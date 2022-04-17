<?php

namespace App\Models\Shikimori;

use App\Http\Helpers\Cookie;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\Cookie as CookieSetter;

class UserModel {
    private static $_staticGlobalInstance = null;

    protected $_userId = null;
    protected const USER_ID_COOKIE_NAME = 'shiki_this_user_id';

    /**
     * @throws \Exception
     */
    public function __construct() {
        $this->_userId = request()->cookie(self::USER_ID_COOKIE_NAME);
    }

    public function getUserId() {
        return $this->_userId ?? $this->me()['id'] ?? null;
    }

    public function me() {
        try {
            $client = new Client();

            $response = $client->get(Endpoints::API_V1_URL . '/users/whoami', [
                'headers' => AuthModel::instance()->addTokenAccessHeaderParams(),
            ]);

            $authData = json_decode($response->getBody(), true);

            $this->_userId = $authData['id'];

            Cookie::instance()->queue(
                CookieSetter::create(
                    self::USER_ID_COOKIE_NAME,
                    $this->_userId,
                    time() + 2678400,
                    '/',
                    null,
                    true,
                    true,
                    false,
                    CookieSetter::SAMESITE_NONE
                )
            );

            return $authData;
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
            self::$_staticGlobalInstance = new self();
        }

        return self::$_staticGlobalInstance;
    }
}
