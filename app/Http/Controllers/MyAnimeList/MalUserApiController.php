<?php

namespace App\Http\Controllers\MyAnimeList;

use App\Models\MyAnimeList\UserModel;
use Laravel\Lumen\Http\Request;
use Laravel\Lumen\Routing\Controller;

class MalUserApiController
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
