<?php

namespace App\Http\Controllers\MyAnimeList;

use App\Models\MyAnimeList\AnimeListModel;
use Laravel\Lumen\Http\Request;
use Laravel\Lumen\Routing\Controller;

class MalAnimeListApiController
{
    /**
     * Full anime list
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function library( Request $request ) {
        $animeList = AnimeListModel::instance()->getUserAnimeList();

        if ( isset($animeList['status']) ) {
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
    public function rates( Request $request, $anime_id ) {
        if ( null === $anime_id ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid anime ID'
            ], 400);
        }

        $animeRates = AnimeListModel::instance()->getAnimeRates( $anime_id );

        if ( isset($animeRates['status']) && 'error' === $animeRates['status'] ) {
            return response()->json([
                'status' => $animeRates['status'],
                'message' => $animeRates['message'],
            ], $animeRates['code']);
        }
        else if ( isset($animeRates['error']) ) {
            return response()->json([
                'status' => 'error',
                'message' => $animeRates['error'],
            ], 500);
        }

        return response()->json(
            array_merge(
                [ 'status' => 'ok' ],
                $animeRates
            )
        );
    }

    /**
     * Update anime rate
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRates( Request $request, $anime_id ) {
        if ( null === $anime_id ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid anime ID'
            ], 400);
        }

        $rateFields = request()->input();

        $updateResult = AnimeListModel::instance()->updateAnimeRates( $anime_id, $rateFields );

        if ( isset($updateResult['status']) && 'error' === $updateResult['status'] ) {
            return response()->json([
                'status' => $updateResult['status'],
                'message' => $updateResult['message'],
            ], $updateResult['code']);
        }
        else if ( isset($animeRates['error']) ) {
            return response()->json([
                'status' => 'error',
                'message' => $animeRates['error'],
            ], 500);
        }

        return response()->json(
            array_merge(
                [ 'status' => 'ok' ],
                $updateResult
            )
        );
    }
}
