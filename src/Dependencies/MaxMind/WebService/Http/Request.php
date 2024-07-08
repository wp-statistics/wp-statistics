<?php

namespace WP_Statistics\Dependencies\MaxMind\WebService\Http;

/**
 * Interface Request.
 *
 * @internal
 */
interface Request
{
    /**
     * @param string $url
     * @param array  $options
     */
    public function __construct($url, $options);

    /**
     * @param string $body
     *
     * @return mixed
     */
    public function post($body);

    /**
     * @return mixed
     */
    public function get();
}
