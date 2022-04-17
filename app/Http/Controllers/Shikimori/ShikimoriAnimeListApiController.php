<?php

namespace App\Http\Controllers\Shikimori;

use App\Models\Shikimori\AnimeListModel;
use App\Models\Shikimori\UserModel;
use Laravel\Lumen\Http\Request;
use Laravel\Lumen\Routing\Controller;

class ShikimoriAnimeListApiController
{
    /**
     * Full anime list
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function library( Request $request, $userId = null ) {
        $userId = $userId ?? UserModel::instance()->getUserId();
        if ( empty($userId) ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid user ID'
            ], 400);
        }

        $animeList = AnimeListModel::instance()->getUserAnimeList( $userId );

        if ( isset($animeList['status']) && 'error' === $animeList['status']) {
            return response()->json([
                'status' => $animeList['status'],
                'message' => $animeList['message'],
            ], $animeList['code']);
        }

        return response()->json([
            'status' => 'ok',
            'list' => $animeList
        ]);
    }

    /**
     * Full anime rates
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function rates( Request $request ) {
        $ratesId = $request['rate_id'];
        if ( null === $ratesId ) {
            response()->json([
                'status' => 'error',
                'message' => 'Invalid rates ID'
            ], 400);
        }

        $animeRates = AnimeListModel::instance()->getAnimeRates( $ratesId );

        if ( isset($animeRates['status']) ) {
            return response()->json([
                'status' => $animeRates['status'],
                'message' => $animeRates['message'],
            ], $animeRates['code']);
        }

        return response()->json(
            array_merge(
                [ 'status' => 'ok' ],
                $animeRates
            )
        );
    }

    /**
     * Create new anime rate
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function newRates( Request $request ) {
        $userId = $request['user_id'];
        if ( null === $userId ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid user ID'
            ], 400);
        }

        $targetId = $request['target_id'];
        if ( null === $targetId ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid target ID'
            ], 400);
        }

        $rateFields = request()->input();

        $createResult = AnimeListModel::instance()->createAnimeRates( $userId, $targetId, $rateFields );

        if ( isset($createResult['status']) && 'error' === $createResult['status'] ) {
            return response()->json([
                'status' => $createResult['status'],
                'message' => $createResult['message'],
            ], $createResult['code']);
        }

        return response()->json(
            array_merge(
                [ 'status' => 'ok' ],
                $createResult
            )
        );
    }

    /**
     * Update anime rate
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRates( Request $request, $rate_id ) {
        if ( null === $rate_id ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid rate ID'
            ], 400);
        }

        $rateFields = request()->input();

        $updateResult = AnimeListModel::instance()->updateAnimeRates( $rate_id, $rateFields );

        if ( isset($updateResult['status']) && 'error' === $updateResult['status'] ) {
            return response()->json([
                'status' => $updateResult['status'],
                'message' => $updateResult['message'],
            ], $updateResult['code']);
        }

        return response()->json(
            array_merge(
                [ 'status' => 'ok' ],
                $updateResult
            )
        );
    }
}
