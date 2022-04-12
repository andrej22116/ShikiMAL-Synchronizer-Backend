<?php

namespace App\Http\Helpers;

class Cookie {
    static $instance = null;

    private $cookieList = [];

    private function __construct() {}

    public static function instance() : self {
        if ( !self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function queue( \Symfony\Component\HttpFoundation\Cookie $cookie ) {
        $this->cookieList[] = $cookie;
    }

    public function getQueuedCookies() : array {
        return $this->cookieList;
    }
}


