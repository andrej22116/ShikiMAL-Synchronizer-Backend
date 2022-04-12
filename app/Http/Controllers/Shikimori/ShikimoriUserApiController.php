<?php

namespace App\Http\Controllers\Shikimori;

use App\Models\Shikimori\UserModel;
use Laravel\Lumen\Http\Request;
use Laravel\Lumen\Routing\Controller;

class ShikimoriUserApiController
{
    /**
     * User info
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me( Request $request ) {
        $userModel = UserModel::instance();

        $userProfileData = $userModel->me();
        if ( isset($userProfileData['status']) ) {
            return response()->json([
                'status' => $userProfileData['status'],
                'message' => $userProfileData['message'],
            ], $userProfileData['code']);
        }

        return response()->json(
            array_merge(
                [ 'status' => 'ok' ],
                $userProfileData
            )
        );
    }
}
