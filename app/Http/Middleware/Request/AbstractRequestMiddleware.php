<?php

namespace App\Http\Middleware\Request;

use Closure;

abstract class AbstractRequestMiddleware {
    protected $requestRawData = null;

    /**
     * AbstractRequestMiddleware constructor.
     */
    public function __construct() {
        $rawData = file_get_contents('php://input');
        if ( false !== $rawData ) {
            $this->requestRawData = $rawData;
        }
    }

    /**
     * Convert like JSON raw request data to array
     * @return array
     */
    public function rawJsonToArray() : array {
        return !is_null($this->requestRawData)
            ? json_decode($this->requestRawData, true) ?? []
            : [];
    }


    /**
     * Convert like QueryString raw request data to array
     * @return array
     */
    public function rawQueryToArray() : array {
        $parsedQuery = null;
        if ( !is_null($this->requestRawData) ) {
            parse_str($this->requestRawData, $parsedQuery);
        }
        else {
            $parsedQuery = [];
        }

        return $parsedQuery;
    }

    /**
     * Return array with request fields by content type
     * @param string $contentType
     * @return array
     */
    public function rawDataToArray( string $contentType ) : array {
        if ( 'application/json' === $contentType ) {
            return $this->rawJsonToArray();
        }
        else {
            return $this->rawQueryToArray();
        }
    }
}
