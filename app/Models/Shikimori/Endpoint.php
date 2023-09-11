<?php

namespace App\Models\Shikimori;

class Endpoint {
    public const API_V1_URL = 'https://shikimori.me/api';
    public const API_V2_URL = 'https://shikimori.me/api/v2';

    /**
     * @param string $path
     *
     * @return string
     */
    public static function makeV2( string $path ): string {
        return self::make( $path, 'v2' );
    }

    /**
     * @param string $path
     * @param string $version empty or 'v2'
     *
     * @return string
     */
    public static function make( string $path, string $version = '' ): string {
        $apiUrl = $version === 'v2' ? self::API_V2_URL : self::API_V1_URL;
        $path = trim( $path );
        $path = trim( $path, '/' );

        return "$apiUrl/$path";
    }
}
