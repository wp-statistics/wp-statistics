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
        $salt = wp_salt();
        return md5($salt . json_encode($payload));
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
