<?php

namespace WP_Statistics\Components;

/**
 * Response headers shared by every tracking endpoint (custom event,
 * hit record, REST /hit). Keeps the URLs out of search-engine indexes
 * and out of reverse-proxy / CDN caches.
 *
 * @link https://wordpress.org/support/topic/request-for-cloudflare-html-caching-compatibility/
 */
class TrackingResponse
{
    /**
     * @return array<string,string>
     */
    public static function getHeaders()
    {
        return [
            'X-Robots-Tag'  => 'noindex, nofollow',
            'Cache-Control' => 'no-cache',
        ];
    }

    /**
     * Emit headers via header(). For REST handlers, pass getHeaders()
     * to $response->set_headers() instead.
     */
    public static function sendHeaders()
    {
        if (headers_sent()) {
            return;
        }

        foreach (self::getHeaders() as $name => $value) {
            header($name . ': ' . $value, true);
        }
    }
}
