<?php

namespace App\Models\MyAnimeList;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AnimeListModel {
    private static $_staticGlobalInstance = null;

    public function getUserAnimeList() {
        try {
            $client = new Client();

            $list = [];
            $nextPageUrl = Endpoints::API_V2_URL . '/users/@me/animelist?nsfw=1&limit=1000&fields=my_list_status,num_episodes';

            do {
                $response = $client->get($nextPageUrl, [
                    'headers' => AuthModel::instance()->getTokenAccessHeaderParams(),
                ]);

                $responseJson = json_decode($response->getBody(), true);

                $list = array_merge($list, $responseJson['data']);

                $nextPageUrl = $responseJson['paging']['next'] ?? null;
            } while ( null !== $nextPageUrl );

            return $list;
        }
        catch ( GuzzleException $e ) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ];
        }
    }

    public function getAnimeRates( $animeId ) {
        try {
            $client = new Client();

            $response = $client->get(Endpoints::API_V2_URL . "/anime/{$animeId}?fields=my_list_status", [
                'headers' => AuthModel::instance()->getTokenAccessHeaderParams(),
            ]);

            return json_decode($response->getBody(), true);
        }
        catch ( GuzzleException $e ) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ];
        }
    }

    /**
     * Update anime rate by rate ID
     * @param $rateId
     * @param $rateData
     * @return array|mixed
     */
    public function updateAnimeRates( $animeId, $rateData ) {
        if ( empty($animeId) ) {
            return [
                'status' => 'error',
                'message' => 'Anime ID is empty',
                'code' => 400
            ];
        }

        $preparedRatesData = self::filterRateFields($rateData);

        try {
            $client = new Client();

            $response = $client->patch(Endpoints::API_V2_URL . "/anime/{$animeId}/my_list_status", [
                'headers' => array_merge(
                    AuthModel::instance()->getTokenAccessHeaderParams(),
                    ['Content-Type' => 'application/x-www-form-urlencoded']
                ),
                'body' => http_build_query($preparedRatesData)
            ]);

            return json_decode($response->getBody(), true);
        }
        catch ( GuzzleException $e ) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ];
        }
    }

    public static function instance() : self {
        if ( null === self::$_staticGlobalInstance ) {
            self::$_staticGlobalInstance = new self(AuthModel::instance());
        }

        return self::$_staticGlobalInstance;
    }

    /**
     * Return array with correct fields
     * @param $fields
     * @return array
     */
    protected static function filterRateFields( $fields ) : array {
        $filteredFields = [];

        if ( isset($fields['num_watched_episodes']) ) $filteredFields['num_watched_episodes'] = $fields['num_watched_episodes'];
        //if ( isset($fields['rewatches']) ) $filteredFields['rewatches'] = $fields['rewatches'];
        if ( isset($fields['score']) ) $filteredFields['score'] = $fields['score'];
        if ( isset($fields['status']) ) $filteredFields['status'] = $fields['status'];

        return $filteredFields;
    }
}
