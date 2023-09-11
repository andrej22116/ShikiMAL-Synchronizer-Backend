<?php

namespace App\Http\Controllers\MyAnimeList;

use App\Http\Helpers\Cookie;
use App\Models\MyAnimeList\AuthModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Laravel\Lumen\Application;
use Laravel\Lumen\Http\Redirector;
use Laravel\Lumen\Http\Request;
use Symfony\Component\HttpFoundation\Cookie as CookieSetter;

class MalAuthApiController {
    protected const CHALLENGE_COOKIE_NAME = 'mal_challenge';

    /**
     * Authorization step
     *
     * @param Request $request
     *
     * @return View|Application|null
     */
    public function authorization( AuthModel $authModel ) {
        $code = $_GET['code'] ?? null;
        if ( null === $code ) {
            return null;
        }

        $challenge = request()->cookie( self::CHALLENGE_COOKIE_NAME );
        if ( null === $challenge ) {
            return null;
        }

        if ( !$authModel->authorize( $code, $challenge ) ) {
            return null;
        }

        return view( 'close' );
    }

    /**
     * Start authorization
     *
     * @param AuthModel $authModel
     *
     * @return RedirectResponse|Redirector
     */
    public function authorize( AuthModel $authModel ) {
        [$authUrl, $challenge, $state] = $authModel->getAuthUrl();

        app( Cookie::class )->queue(
            CookieSetter::create(
                self::CHALLENGE_COOKIE_NAME,
                $challenge,
                time() + 1800,
                '/',
                null,
                true,
                true,
                false,
                CookieSetter::SAMESITE_NONE
            )
        );

        return redirect( $authUrl );
    }

    /**
     * Is authorized
     *
     * @param AuthModel $authModel
     *
     * @return array
     * @throws \Throwable
     */
    public function test( AuthModel $authModel ): array {
        throw_if( !$authModel->isAuthorized() && !$authModel->authorize(), \Exception::class, 'Not authorized!', 401 );

        return ['logged' => true];
    }

    /**
     * Logout
     *
     * @param AuthModel $authModel
     *
     * @return array
     */
    public function logout( AuthModel $authModel ): array {
        $authModel->logout();

        return [];
    }
}
