<?php

namespace App\Http\Controllers\MyAnimeList;

use App\Http\Helpers\Cookie;
use App\Models\MyAnimeList\AuthModel;
use Illuminate\Support\Facades\Redirect;
use Laravel\Lumen\Http\Request;
use Symfony\Component\HttpFoundation\Cookie as CookieSetter;

class MalAuthApiController
{
    protected const CHALLENGE_COOKIE_NAME = 'mal_challenge';

    /**
     * Start authorization
     * @param Request $request
     */
    public function authorize( Request $request ) {
        [$authUrl, $challenge, $state] = AuthModel::instance()->getAuthUrl();

        Cookie::instance()->queue(
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

        return redirect($authUrl);
    }

    /**
     * Authorization step
     * @param Request $request
     * @return \Illuminate\View\View|\Laravel\Lumen\Application|null
     */
    public function authorization( Request $request ) {
        $code = $_GET['code'] ?? null;
        if ( null === $code ) {
            return null;
        }

        $challenge = request()->cookie(self::CHALLENGE_COOKIE_NAME);
        if ( null === $challenge ) {
            var_dump($challenge);
            echo 'No challenge';
            return null;
        }

        if ( !AuthModel::instance()->authorize($code, $challenge) ) {
            return null;
        }

        return view('close');
    }

    /**
     * Is authorized
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function test( Request $request ) {
        $model = AuthModel::instance();

        if ( !$model->isAuthorized() && !$model->authorize() ) {
            return response()->json([
                'state' => 'error',
                'message' => 'Not authorized!'
            ], 401);
        }

        return response()->json([
            'state' => 'ok',
            'logged' => true
        ]);
    }

    /**
     * Logout
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout( Request $request ) {
        AuthModel::instance()->logout();

        return response()->json([
            'state' => 'ok'
        ]);
    }
}
