<?php

namespace App\Models\Shikimori;

use Psr\Http\Message\ResponseInterface;

class Model {
    /**
     * @param mixed $responseBody
     * @param bool $throwException
     *
     * @return bool
     * @throws \Exception
     */
    public function checkResponse( $responseBody, bool $throwException = true ): bool {
        if ( !is_array( $responseBody ) ) {
            return true;
        }

        $isInvalid = isset( $responseBody['status'] ) && 'error' === $responseBody['status'];
        if ( !$isInvalid ) {
            return true;
        }

        if ( $throwException ) {
            throw new \Exception( $responseBody['message'], $responseBody['code'] );
        }

        return false;
    }

    /**
     * Get body of response, transform to array, check it for errors and return
     *
     * @param ResponseInterface $response
     *
     * @return mixed
     * @throws \Exception
     */
    public function getResponseBody( ResponseInterface $response ) {
        $body = json_decode( $response->getBody(), true );

        $this->checkResponse( $body );

        return $body;
    }
}
