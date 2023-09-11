<?php

namespace App\Http\Controllers\MyAnimeList;

use App\Models\MyAnimeList\AnimeListModel;
use Exception;
use Throwable;

class MalAnimeListApiController {
    /**
     * Full anime list
     *
     * @param AnimeListModel $animeListModel
     *
     * @return array
     */
    public function library( AnimeListModel $animeListModel ): array {
        return [
            'list' => $animeListModel->getUserAnimeList(),
        ];
    }

    /**
     * Full anime rates
     *
     * @param AnimeListModel $animeListModel
     * @param string|null $anime_id
     *
     * @return array
     * @throws Throwable
     */
    public function rates( AnimeListModel $animeListModel, ?string $anime_id ): array {
        throw_if( empty( $anime_id ), Exception::class, 'Invalid anime ID', 400 );

        return $animeListModel->getAnimeRates( $anime_id );
    }

    /**
     * Update anime rate
     *
     * @param AnimeListModel $animeListModel
     * @param string|null $anime_id
     *
     * @return array
     * @throws Throwable
     */
    public function updateRates( AnimeListModel $animeListModel, ?string $anime_id ): array {
        throw_if( empty( $anime_id ), Exception::class, 'Invalid anime ID', 400 );

        $rateFields = request()->input();

        return $animeListModel->updateAnimeRates( $anime_id, $rateFields );
    }
}
