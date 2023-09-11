<?php

namespace App\Http\Controllers\Shikimori;

use App\Models\Shikimori\AnimeListModel;
use App\Models\Shikimori\UserModel;
use Exception;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Http\Request;
use Throwable;

class ShikimoriAnimeListApiController {
    /**
     * Full anime list
     *
     * @param UserModel $userModel
     * @param AnimeListModel $animeListModel
     * @param null|string $userId
     *
     * @return array
     * @throws Throwable
     */
    public function library( UserModel $userModel, AnimeListModel $animeListModel, ?string $userId = null ): array {
        $userId = $userId ?? $userModel->getUserId();
        throw_if( empty( $userId ), Exception::class, 'Invalid user ID', 400 );

        return [
            'list' => $animeListModel->getUserAnimeList( $userId ),
        ];
    }

    /**
     * Full anime rates
     *
     * @param AnimeListModel $animeListModel
     * @param Request $request
     *
     * @return array
     * @throws Throwable
     */
    public function rates( AnimeListModel $animeListModel, Request $request ): array {
        $ratesId = $request['rate_id'];
        throw_if( null === $ratesId, Exception::class, 'Invalid rate ID', 400 );

        return $animeListModel->getAnimeRates( $ratesId );
    }

    /**
     * Create new anime rate
     *
     * @param AnimeListModel $animeListModel
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Throwable
     */
    public function newRates( AnimeListModel $animeListModel, Request $request ) {
        $userId = $request['user_id'];
        $targetId = $request['target_id'];

        throw_if( null === $userId, Exception::class, 'Invalid user ID', 400 );
        throw_if( null === $targetId, Exception::class, 'Invalid target ID', 400 );

        $rateFields = request()->input();

        $createResult = $animeListModel->createAnimeRates( $userId, $targetId, $rateFields );

        return response()->json(
            array_merge(
                ['status' => 'ok'],
                $createResult
            )
        );
    }

    /**
     * Update anime rate
     *
     * @param AnimeListModel $animeListModel
     * @param string|null $rate_id
     *
     * @return JsonResponse
     * @throws Throwable
     */
    public function updateRates( AnimeListModel $animeListModel, ?string $rate_id ) {
        throw_if( null === $rate_id, Exception::class, 'Invalid rate ID', 400 );

        $rateFields = request()->input();

        $updateResult = $animeListModel->updateAnimeRates( $rate_id, $rateFields );

        return response()->json(
            array_merge(
                ['status' => 'ok'],
                $updateResult
            )
        );
    }
}
