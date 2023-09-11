<?php

namespace App\Http\Helpers;

class Cookie {
    private $cookieList = [];

    public function queue( \Symfony\Component\HttpFoundation\Cookie $cookie ) {
        $this->cookieList[] = $cookie;
    }

    public function getQueuedCookies(): array {
        return $this->cookieList;
    }
}


