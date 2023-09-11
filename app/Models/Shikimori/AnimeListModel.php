<?php

namespace App\Models\Shikimori;

use Exception;
use GuzzleHttp\Client;

class AnimeListModel extends Model {
    protected $authModel;

    public function __construct() {
        $this->authModel = app( AuthModel::class );
    }

    public function getUserAnimeList( string $userId ) {
        throw_if( empty( $userId ), Exception::class, 'User ID is empty', 400 );

        $client = new Client();

        $response = $client->get( Endpoint::make( "/users/{$userId}/anime_rates?limit=5000&censored=false" ), [
            'headers' => $this->authModel->getTokenAccessHeaderParams(),
        ] );

        return $this->getResponseBody( $response );
    }

    public function getAnimeRates( string $ratesId ) {
        throw_if( empty( $ratesId ), Exception::class, 'Rate ID is empty', 400 );

        $client = new Client();

        $response = $client->get( Endpoint::makeV2( "/user_rates/{$ratesId}" ), [
            'headers' => $this->authModel->getTokenAccessHeaderParams(),
        ] );

        return $this->getResponseBody( $response );
    }

    /**
     * Create new user rate for target (anime)
     *
     * @param $userId
     * @param $targetId
     * @param $rateData
     *
     * @return array|mixed
     */
    public function createAnimeRates( string $userId, string $targetId, array $rateData ) {
        throw_if( empty( $userId ) || empty( $targetId ), Exception::class, 'User ID or Target ID is empty', 400 );

        $preparedRatesData = array_merge(
            [
                'target_type' => 'Anime',
                'user_id' => $userId,
                'target_id' => $targetId,
            ],
            $this->filterRateFields( $rateData )
        );

        $client = new Client();

        $response = $client->post( Endpoint::makeV2( "/user_rates/{$userId}" ), [
            'headers' => array_merge(
                $this->authModel->getTokenAccessHeaderParams(),
                ['Content-Type' => 'application/json']
            ),
            'json' => $preparedRatesData
        ] );

        return $this->getResponseBody( $response );
    }

    /**
     * Return array with correct fields
     *
     * @param array $fields
     *
     * @return array
     */
    protected function filterRateFields( array $fields ): array {
        return collect( $fields )
            ->intersectByKeys( [
                'episodes' => true,
                //'rewatches' => true,
                'score' => true,
                'status' => true,
            ] )
            ->all();
    }

    /**
     * Update anime rate by rate ID
     *
     * @param $rateId
     * @param $rateData
     *
     * @return array|mixed
     */
    public function updateAnimeRates( string $rateId, array $rateData ) {
        throw_if( empty( $rateId ), Exception::class, 'Rate ID is empty', 400 );

        $preparedRatesData = $this->filterRateFields( $rateData );
        $preparedRatesData['target_type'] = 'Anime';
        $rate = [
            'user_rate' => $preparedRatesData
        ];

        $client = new Client();

        $response = $client->patch( Endpoint::makeV2( "/user_rates/{$rateId}" ), [
            'headers' => array_merge(
                $this->authModel->getTokenAccessHeaderParams(),
                ['Content-Type' => 'application/json']
            ),
            'json' => $rate
        ] );

        return $this->getResponseBody( $response );
    }
}
