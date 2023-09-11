<?php

namespace App\Models\Shikimori;

use App\Http\Helpers\Cookie;
use Exception;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Cookie as CookieSetter;

class UserModel extends Model {
    protected const USER_ID_COOKIE_NAME = 'shiki_this_user_id';
    protected $_userId = null;

    /**
     * @throws Exception
     */
    public function __construct() {
        $this->_userId = request()->cookie( self::USER_ID_COOKIE_NAME );
    }

    public function getUserId() {
        return $this->_userId ?? $this->me()['id'] ?? null;
    }

    public function me() {
        $authModel = app( AuthModel::class );

        $client = new Client();

        $response = $client->get( Endpoint::make( '/users/whoami' ), [
            'headers' => $authModel->addTokenAccessHeaderParams(),
        ] );

        $authData = json_decode( $response->getBody(), true );

        $this->_userId = $authData['id'];

        app( Cookie::class )->queue(
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
}
