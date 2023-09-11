<?php

namespace App\Models\MyAnimeList;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class UserModel extends Model {
    /**
     * @throws Exception
     */
    public function __construct() {
    }

    public function me() {
        $client = new Client();

        $response = $client->get( Endpoint::make('/users/@me?fields=id,name,picture,gender,birthday,location,joined_at,anime_statistics,time_zone,is_supporter'), [
            'headers' => app(AuthModel::class)->getTokenAccessHeaderParams(),
        ] );

        return $this->getResponseBody( $response );
    }
}
