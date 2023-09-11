<?php

namespace App\Http\Controllers\Shikimori;

use App\Models\Shikimori\AuthModel;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Lumen\Application;
use Laravel\Lumen\Http\Redirector;
use Throwable;

class ShikimoriAuthApiController {
    /**
     * Authorization step
     *
     * @param AuthModel $authModel
     * @param Request $request
     *
     * @return View|Application|null
     */
    public function authorization( AuthModel $authModel, Request $request ) {
        $code = $request->get( 'code' );
        if ( null === $code ) {
            return null;
        }

        if ( !$authModel->authorize( $code ) ) {
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
        [$authUrl, $state] = $authModel->getAuthUrl();
        return redirect( $authUrl );
    }

    /**
     * Is authorized
     *
     * @param AuthModel $authModel
     *
     * @return array
     * @throws Throwable
     */
    public function authorized( AuthModel $authModel ) {
        throw_if( !$authModel->isAuthorized() && !$authModel->authorize(), Exception::class, 'Not authorized!', 401 );

        return ['logged' => true];
    }

    /**
     * Logout
     *
     * @param AuthModel $authModel
     *
     * @return void
     */
    public function logout( AuthModel $authModel ): void {
        $authModel->logout();
    }
}
