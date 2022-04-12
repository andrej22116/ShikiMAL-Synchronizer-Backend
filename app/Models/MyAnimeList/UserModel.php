<?php

namespace App\Models\MyAnimeList;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cookie as CookieGetter;
use Symfony\Component\HttpFoundation\Cookie as CookieSetter;

class UserModel {
    private static $_staticGlobalInstance = null;

    /**
     * @throws \Exception
     */
    public function __construct() {
    }

    public function me() {
        try {
            $client = new Client();

            $response = $client->get(Endpoints::API_V2_URL . '/users/@me?fields=id,name,picture,gender,birthday,location,joined_at,anime_statistics,time_zone,is_supporter', [
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

    public static function instance() : self {
        if ( null === self::$_staticGlobalInstance ) {
            self::$_staticGlobalInstance = new self(AuthModel::instance());
        }

        return self::$_staticGlobalInstance;
    }
}
