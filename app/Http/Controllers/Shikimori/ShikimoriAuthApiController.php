<?php

namespace App\Http\Controllers\Shikimori;

use App\Models\Shikimori\AuthModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Laravel\Lumen\Routing\Controller;

class ShikimoriAuthApiController
{
    /**
     * Start authorization
     */
    public function authorize( $request = null, $kek = null ) {
        [$authUrl, $state] = AuthModel::instance()->getAuthUrl();
        return redirect($authUrl);
    }

    /**
     * Authorization step
     * @param Request $request
     * @return \Illuminate\View\View|\Laravel\Lumen\Application|null
     */
    public function authorization( Request $request ) {
        $code = $request->get('code');
        if ( null === $code ) {
            return null;
        }

        if ( !AuthModel::instance()->authorize($code) ) {
            return null;
        }

        return view('close');
    }

    /**
     * Is authorized
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authorized( Request $request ) {
        $model = AuthModel::instance();

        if ( !$model->isAuthorized() && !$model->authorize() ) {
            return response()->json([
                'state' => 'error',
                'message' => 'Not authorized!',
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
