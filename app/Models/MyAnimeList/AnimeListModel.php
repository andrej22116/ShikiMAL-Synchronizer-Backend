<?php

namespace App\Models\MyAnimeList;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AnimeListModel extends Model {
    protected $authModel;

    public function __construct() {
        $this->authModel = app(AuthModel::class);
    }

    public function getUserAnimeList() {
        $client = new Client();

        $list = [];
        $nextPageUrl = Endpoint::make('/users/@me/animelist?nsfw=1&limit=1000&fields=my_list_status,num_episodes,alternative_titles');

        do {
            $response = $client->get( $nextPageUrl, [
                'headers' => $this->authModel->getTokenAccessHeaderParams(),
            ] );

            $responseJson = json_decode( $response->getBody(), true );

            $list = array_merge( $list, $responseJson['data'] );

            $nextPageUrl = $responseJson['paging']['next'] ?? null;
        } while ( null !== $nextPageUrl );

        return $list;
    }

    public function getAnimeRates( $animeId ) {
        $client = new Client();

        $response = $client->get( Endpoint::make("/anime/{$animeId}?fields=my_list_status"), [
            'headers' => $this->authModel->getTokenAccessHeaderParams(),
        ] );

        return $this->getResponseBody($response);
    }

    /**
     * Update anime rate by rate ID
     *
     * @param string $animeId
     * @param array $rateData
     *
     * @return array|mixed
     * @throws GuzzleException
     * @throws \Throwable
     */
    public function updateAnimeRates( string $animeId, array $rateData ) {
        throw_if(empty( $animeId ), \Exception::class, 'Anime ID is empty', 400);

        $preparedRatesData = self::filterRateFields( $rateData );

        $client = new Client();

        $response = $client->patch( Endpoint::make("/anime/{$animeId}/my_list_status"), [
            'headers' => array_merge(
                $this->authModel->getTokenAccessHeaderParams(),
                ['Content-Type' => 'application/x-www-form-urlencoded']
            ),
            'body' => http_build_query( $preparedRatesData )
        ] );

        return $this->getResponseBody($response);
    }

    /**
     * Return array with correct fields
     *
     * @param $fields
     *
     * @return array
     */
    protected static function filterRateFields( $fields ): array {
        return collect( $fields )
            ->intersectByKeys( [
                'num_watched_episodes' => true,
                //'rewatches' => true,
                'score' => true,
                'status' => true,
            ] )
            ->all();
    }
}
