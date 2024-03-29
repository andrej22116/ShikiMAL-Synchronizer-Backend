<?php

namespace App\Models\MyAnimeList;

use App\Http\Helpers\Cookie;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\Cookie as CookieSetter;

class AuthModel {
    protected const ACCESS_TOKEN_COOKIE_NAME = 'mal_access_token';
    protected const REFRESH_TOKEN_COOKIE_NAME = 'mal_refresh_token';
    protected $_appName = null;
    protected $_appId = null;
    protected $_appSecret = null;
    protected $_accessToken = null;
    protected $_refreshToken = null;
    protected $_cookie = null;

    /**
     * @throws Exception
     */
    public function __construct() {
        $this->_cookie = app( Cookie::class );

        $this->_appName = env( 'APP_NAME' );
        $this->_appId = env( 'MAL_API_CLIENT_ID' );
        $this->_appSecret = env( 'MAL_API_CLIENT_SECRET' );

        if ( !$this->_appId || !$this->_appSecret ) {
            throw new Exception( 'MyAnimeList application access keys not found!' );
        }

        $this->_accessToken = request()->cookie( self::ACCESS_TOKEN_COOKIE_NAME );
        $this->_refreshToken = request()->cookie( self::REFRESH_TOKEN_COOKIE_NAME );
    }

    public function getAuthUrl(): array {
        $authBaseUrl = 'https://myanimelist.net/v1/oauth2/authorize';
        $redirectUrl = self::getRedirectUri( true );

        $time = time();
        $state = md5( $time );
        $challenge = md5( $time ) . sha1( $time ) . md5( rand() );

        $queryArgs = [
            'response_type=code',
            "client_id={$this->_appId}",
            "state={$state}",
            "code_challenge={$challenge}",
            "redirect_uri={$redirectUrl}&scope=user_rates",
        ];

        $queryString = implode( '&', $queryArgs );

        return ["{$authBaseUrl}?{$queryString}", $challenge, $state];
    }

    public static function getRedirectUri( $encodeUrl = false ): string {
        return $encodeUrl
            ? urlencode( route( 'mal.auth.authorization' ) )
            : route( 'mal.auth.authorization' );
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
    public function authorize( $authCode = null, $authVerifier = null, $authState = null ): bool {
        if ( $this->_accessToken ) {
            return true;
        }

        if ( empty( $authCode ) && null !== $this->_refreshToken ) {
            $refreshTokenSuccess = $this->refreshAccessToken();
            if ( $refreshTokenSuccess ) {
                return true;
            }
        }

        if ( empty( $authCode ) || empty( $authVerifier ) ) {
            return false;
        }

        try {
            $httpClient = new Client();

            $response = $httpClient->post( 'https://myanimelist.net/v1/oauth2/token', [
                'headers' => $this->getDefaultAccessHeaderParams(),
                'body' => http_build_query( [
                    'grant_type' => 'authorization_code',
                    'client_id' => $this->_appId,
                    'client_secret' => $this->_appSecret,
                    'code' => $authCode,
                    'redirect_uri' => self::getRedirectUri(),
                    'code_verifier' => $authVerifier
                ] )
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

            $response = $httpClient->post( 'https://myanimelist.net/v1/oauth2/token', [
                'headers' => $this->getDefaultAccessHeaderParams(),
                'json' => [
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

    /**
     * Return default access header params
     *
     * @return array
     */
    public function getDefaultAccessHeaderParams(): array {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
    }

    protected function updateAccessKeys( array $accessData ) {
        $this->_accessToken = $accessData['access_token'] ?? null;
        $this->_refreshToken = $accessData['refresh_token'] ?? null;

        $accessTokenLifeTime = time() + (int)($accessData['expires_in'] ?? 0);
        $refreshTokenLifeTime = time() + (isset( $accessData['expires_in'] ) ? 2678400 : 0); // 31 day

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

    public function logout() {
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
            'Authorization' => "Bearer {$this->_accessToken}"
        ];
    }

    /**
     * Return new array with added header params
     *
     * @param array $params
     *
     * @return array
     */
    public function addTokenAccessHeaderParams( array $params ): array {
        return $this->injectTokenAccessHeaderParams( $params );
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
    public function addDefaultAccessHeaderParams( array $params ): array {
        return $this->injectDefaultAccessHeaderParams( $params );
    }

    /**
     * Inject header params in array
     *
     * @param array $params
     *
     * @return array
     */
    public function injectDefaultAccessHeaderParams( array &$params ): array {
        $params['Content-Type'] = 'application/x-www-form-urlencoded';
        return $params;
    }
}
