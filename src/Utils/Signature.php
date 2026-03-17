<?php

namespace WP_Statistics\Utils;

class Signature
{
    /**
     * Generate a signature for the request payload using a WordPress salt.
     *
     * @param array $payload The request payload.
     * @return string The generated signature.
     */
    public static function generate($payload)
    {
        return md5(self::getSalt() . json_encode($payload));
    }

    /**
     * Get the signing salt directly from wp-config.php constants.
     *
     * Uses the same constants as wp_salt('auth') but without requiring
     * pluggable.php, making it SHORTINIT-compatible.
     *
     * @return string The salt string.
     */
    private static function getSalt()
    {
        return (defined('AUTH_KEY') ? AUTH_KEY : '') . (defined('AUTH_SALT') ? AUTH_SALT : '');
    }

    /**
     * Check if the provided signature matches the generated signature for the given payload.
     *
     * @param array $payload The request payload.
     * @param string $signature The provided signature.
     * @return bool True if the signatures match, false otherwise.
     */
    public static function check($payload, $signature)
    {
        return self::generate($payload) === $signature;
    }
}
