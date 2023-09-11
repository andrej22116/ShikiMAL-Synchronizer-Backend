<?php

namespace App\Models\MyAnimeList;

class Endpoint {
    public const API_V2_URL = 'https://api.myanimelist.net/v2';

    /**
     * @param string $path
     * @param string $version Currently 'v2' only
     *
     * @return string
     */
    public static function make( string $path, string $version = 'v2' ): string {
        $apiUrl = self::API_V2_URL;
        $path = trim( $path );
        $path = trim( $path, '/' );

        return "$apiUrl/$path";
    }
}
