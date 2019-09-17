<?php
/**
 * Omnisales © 2018
 *
 */

namespace Omnisales\HttpClients;

use Omnisales\HttpClients\OmnisalesCurlHttpClient;

/**
 * Class HttpClientsFactory
 *
 * @package Omnisales
 */
class HttpClientsFactory {

    private function __construct() {
        // a factory constructor should never be invoked
    }

    /**
     * HTTP client generation.
     *
     * @param OmnisalesHttpClientInterface|Client|string|null $handler
     *
     * @throws Exception               
     * @throws InvalidArgumentException If the http client handler isn't "curl", "stream", or an instance of Omnisales\HttpClients\OmnisalesHttpClientInterface.
     *
     * @return OmnisalesHttpClientInterface
     */
    public static function createHttpClient($handler) {
        if (!$handler) {
            return self::detectDefaultClient();
        }

        if ($handler instanceof OmnisalesHttpClientInterface) {
            return $handler;
        }

        if ('curl' === $handler) {
            if (!extension_loaded('curl')) {
                throw new Exception('The cURL extension must be loaded in order to use the "curl" handler.');
            }

            return new OmnisalesCurlHttpClient();
        }

        throw new InvalidArgumentException('The http client handler must be set to "curl" be an instance of Omnisales\HttpClients\OmnisalesHttpClientInterface');
    }

    /**
     * Detect default HTTP client.
     *
     * @return OmnisalesHttpClientInterface
     */
    private static function detectDefaultClient() {
        return new OmnisalesCurlHttpClient();
    }

}
