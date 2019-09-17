<?php

/**
 * Omnisales © 2018
 *
 */

namespace Omnisales\Http;

/**
 * Interface
 *
 * @package Omnisales
 */
interface RequestBodyInterface {

    /**
     * Get the body of the request to send to Graph.
     *
     * @return string
     */
    public function getBody();
}
