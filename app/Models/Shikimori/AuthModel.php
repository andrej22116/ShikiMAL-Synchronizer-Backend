<?php

namespace App\Models\Shikimori;

use App\Http\Helpers\Cookie;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\Cookie as CookieSetter;

class AuthModel extends Model {
    protected $_appName = null;
    protected $_appId = null;
    protected $_appSecret = null;
    protected $_accessToken = null;
    protected $_refreshToken = null;
    protected $_cookie = null;

    protected const ACCESS_TOKEN_COOKIE_NAME = 'shiki_access_token';
    protected const REFRESH_TOKEN_COOKIE_NAME = 'shiki_refresh_token';

    /**
     * @throws \Exception
     */
    public function __construct() {
        $this->_cookie = app( Cookie::class );

        $this->_appName = env( 'APP_NAME' );
        $this->_appId = env( 'SHIKIMORI_API_CLIENT_ID' );
        $this->_appSecret = env( 'SHIKIMORI_API_CLIENT_SECRET' );

        if ( !$this->_appId || !$this->_appSecret ) {
            throw new \Exception( 'Shikimori application access keys not found!' );
        }

        $this->_accessToken = request()->cookie( self::ACCESS_TOKEN_COOKIE_NAME );
        $this->_refreshToken = request()->cookie( self::REFRESH_TOKEN_COOKIE_NAME );
    }

    public static function getRedirectUri( $encodeUrl = false ): string {
        return $encodeUrl
            ? urlencode( route( 'shikimori.auth.authorization' ) )
            : route( 'shikimori.auth.authorization' );
    }

    public function getAuthUrl(): array {
        $authBaseUrl = 'https://shikimori.me/oauth/authorize';
        $redirectUrl = self::getRedirectUri( true );

        $state = md5( time() );

        $queryArgs = [
            'response_type=code',
            "client_id={$this->_appId}",
            "state={$state}",
            "redirect_uri={$redirectUrl}&scope=user_rates",
        ];

        $queryString = implode( '&', $queryArgs );

        return ["{$authBaseUrl}?{$queryString}", $state];
    }

    public function isAuthorized(): bool {
        return null !== $this->_accessToken;
    }

    /**
     * Try authorize user use refresh token or auth code
     *
     * @param null $authCode
     * @param null $authState
     *
     * @return bool
     */
    public function authorize( $authCode = null, $authState = null ): bool {
        if ( $this->_accessToken ) {
            return true;
        }

        if ( empty( $authCode ) && null !== $this->_refreshToken ) {
            $refreshTokenSuccess = $this->refreshAccessToken();
            if ( $refreshTokenSuccess ) {
                return true;
            }
        }

        if ( empty( $authCode ) ) {
            return false;
        }

        try {
            $httpClient = new Client();

            $response = $httpClient->post( 'https://shikimori.me/oauth/token', [
                'headers' => $this->getDefaultAccessHeaderParams(),
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => $this->_appId,
                    'client_secret' => $this->_appSecret,
                    'code' => $authCode,
                    'redirect_uri' => self::getRedirectUri()
                ]
            ] );

            $authData = json_decode( $response->getBody(), true );

            $this->updateAccessKeys( $authData );

            return true;
        } catch ( GuzzleException $e ) {
            return false;
        }
    }

    public function refreshAccessToken(): bool {
        if ( null === $this->_refreshToken ) {
            return false;
        }

        try {
            $httpClient = new Client();

            $response = $httpClient->post( 'https://shikimori.me/oauth/token', [
                'headers' => $this->getDefaultAccessHeaderParams(),
                'form_params' => [
                    'client_id' => $this->_appId,
                    'client_secret' => $this->_appSecret,
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $this->_refreshToken
                ]
            ] );

            $authData = json_decode( $response->getBody(), true );

            $this->updateAccessKeys( $authData );

            return true;
        } catch ( GuzzleException $e ) {
            return false;
        }
    }

    public function logout(): void {
        $this->_cookie->queue(
            CookieSetter::create(
                self::ACCESS_TOKEN_COOKIE_NAME,
                '',
                1,
                '/',
                null,
                true,
                true,
                false,
                CookieSetter::SAMESITE_NONE
            )
        );

        $this->_cookie->queue(
            CookieSetter::create(
                self::REFRESH_TOKEN_COOKIE_NAME,
                '',
                1,
                '/',
                null,
                true,
                true,
                false,
                CookieSetter::SAMESITE_NONE
            )
        );
    }

    public function getAccessToken(): ?string {
        return $this->_accessToken;
    }

    /**
     * Return token access header params
     *
     * @return array
     */
    public function getTokenAccessHeaderParams(): array {
        return null === $this->_accessToken ? [] : [
            'User-Agent' => $this->_appName,
            'Authorization' => "Bearer {$this->_accessToken}"
        ];
    }

    /**
     * Inject header params in array
     *
     * @param array $params
     *
     * @return array
     */
    public function injectTokenAccessHeaderParams( array &$params ) {
        if ( null !== $this->_accessToken ) {
            $params['User-Agent'] = $this->_appName;
            $params['Authorization'] = "Bearer {$this->_accessToken}";
        }

        return $params;
    }

    /**
     * Return new array with added header params
     *
     * @param array $params
     *
     * @return array
     */
    public function addTokenAccessHeaderParams( array $params = [] ): array {
        return $this->injectTokenAccessHeaderParams( $params );
    }

    /**
     * Return default access header params
     *
     * @return array
     */
    public function getDefaultAccessHeaderParams(): array {
        return [
            'User-Agent' => $this->_appName,
            'Content-Type' => 'multipart/form-data'
        ];
    }

    /**
     * Inject header params in array
     *
     * @param array $params
     *
     * @return array
     */
    public function injectDefaultAccessHeaderParams( array &$params ): array {
        $params['User-Agent'] = $this->_appName;
        $params['Content-Type'] = 'multipart/form-data';
        return $params;
    }

    /**
     * Return new array with added header params
     *
     * @param array $params
     *
     * @return array
     */
    public function addDefaultAccessHeaderParams( array $params = [] ): array {
        return $this->injectDefaultAccessHeaderParams( $params );
    }

    protected function updateAccessKeys( array $accessData ) {
        $this->_accessToken = $accessData['access_token'] ?? null;
        $this->_refreshToken = $accessData['refresh_token'] ?? null;

        $accessTokenLifeTime = time() + (int)($accessData['expires_in'] ?? 0);
        $refreshTokenLifeTime = time() + (int)($accessData['expires_in'] ?? 0) * 7;

        $this->_cookie->queue(
            CookieSetter::create(
                self::ACCESS_TOKEN_COOKIE_NAME,
                $this->_accessToken,
                $accessTokenLifeTime,
                '/',
                null,
                true,
                true,
                false,
                CookieSetter::SAMESITE_NONE
            )
        );

        $this->_cookie->queue(
            CookieSetter::create(
                self::REFRESH_TOKEN_COOKIE_NAME,
                $this->_refreshToken,
                $refreshTokenLifeTime,
                '/',
                null,
                true,
                true,
                false,
                CookieSetter::SAMESITE_NONE
            )
        );
    }
}
