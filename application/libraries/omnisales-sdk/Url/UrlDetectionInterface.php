<?php
/**
 * Omnisales © 2018
 *
 */

namespace Omnisales\Url;

/**
 * Interface UrlDetectionInterface
 *
 * @package Omnisales
 */
interface UrlDetectionInterface
{
    /**
     * Get the currently active URL.
     *
     * @return string
     */
    public function getCurrentUrl();
}
