<?php

namespace App\Models\Shikimori;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AnimeListModel {
    private static $_staticGlobalInstance = null;

    public function getUserAnimeList( $userId ) {
        try {
            $client = new Client();

            $response = $client->get(Endpoints::API_V1_URL . "/users/{$userId}/anime_rates?limit=5000&censored=false", [
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

    public function getAnimeRates( $ratesId ) {
        try {
            $client = new Client();

            $response = $client->get(Endpoints::API_V2_URL . "/user_rates/{$ratesId}", [
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
     * Create new user rate for target (anime)
     * @param $userId
     * @param $targetId
     * @param $rateData
     * @return array|mixed
     */
    public function createAnimeRates( $userId, $targetId, $rateData ) {
        if ( empty($userId) || empty($targetId) ) {
            return [
                'status' => 'error',
                'message' => 'User ID or Target ID is empty',
                'code' => 400
            ];
        }

        $preparedRatesData = array_merge(
            [
                'target_type' => 'Anime',
                'user_id' => $userId,
                'target_id' => $targetId,
            ],
            self::filterRateFields($rateData)
        );

        try {
            $client = new Client();

            $response = $client->post(Endpoints::API_V2_URL . "/user_rates/{$userId}", [
                'headers' => array_merge(
                    AuthModel::instance()->getTokenAccessHeaderParams(),
                    ['Content-Type' => 'application/json']
                ),
                'json' => $preparedRatesData
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
    public function updateAnimeRates( $rateId, $rateData ) {
        if ( empty($rateId) ) {
            return [
                'status' => 'error',
                'message' => 'Rate ID is empty',
                'code' => 400
            ];
        }

        $preparedRatesData = self::filterRateFields($rateData);
        $preparedRatesData['target_type'] = 'Anime';
        $rate = [
            'user_rate' => $preparedRatesData
        ];

        try {
            $client = new Client();

            $response = $client->patch(Endpoints::API_V2_URL . "/user_rates/{$rateId}", [
                'headers' => array_merge(
                    AuthModel::instance()->getTokenAccessHeaderParams(),
                    ['Content-Type' => 'application/json']
                ),
                'json' => $rate
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

        if ( isset($fields['episodes']) ) $filteredFields['episodes'] = $fields['episodes'];
        //if ( isset($fields['rewatches']) ) $filteredFields['rewatches'] = $fields['rewatches'];
        if ( isset($fields['score']) ) $filteredFields['score'] = $fields['score'];
        if ( isset($fields['status']) ) $filteredFields['status'] = $fields['status'];

        return $filteredFields;
    }
}
