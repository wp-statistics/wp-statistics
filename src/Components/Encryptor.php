<?php
namespace WP_Statistics\Components;

use WP_Statistics;

class Encryptor
{
    private const NONCE_BYTES = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;
    private const KEY_BYTES = SODIUM_CRYPTO_SECRETBOX_KEYBYTES;
    private const CIPHER_KEY_OPTION = 'wp_statistics_cipher_key';

    // Cached key to be used for encryption/decryption.
    private static $key = null;

    /**
     * Retrieve the key used for encryption/decryption.
     *
     * If defined, SALTs are used to generate a key.
     * If not, a random key is generated and stored in wp_options.
     *
     * @return string
     */
    private static function key(): string
    {
        if (self::$key !== null) {
            return self::$key;
        }

        // Build material from whichever SALTs are defined.
        $material = (defined('AUTH_KEY')         ? AUTH_KEY         : '') .
                    (defined('SECURE_AUTH_KEY')  ? SECURE_AUTH_KEY  : '') .
                    (defined('LOGGED_IN_KEY')    ? LOGGED_IN_KEY    : '') .
                    (defined('NONCE_KEY')        ? NONCE_KEY        : '') .
                    (defined('AUTH_SALT')        ? AUTH_SALT        : '') .
                    (defined('SECURE_AUTH_SALT') ? SECURE_AUTH_SALT : '') .
                    (defined('LOGGED_IN_SALT')   ? LOGGED_IN_SALT   : '') .
                    (defined('NONCE_SALT')       ? NONCE_SALT       : '');

        if ($material !== '') {
            self::$key = sodium_crypto_generichash($material, '', self::KEY_BYTES);
            return self::$key;
        }

        // If no SALTs are defined, fall back to a persistent random key in wp_options.
        $cipherKey  = get_option(self::CIPHER_KEY_OPTION);
        $rawKey     = is_string($cipherKey) ? base64_decode($cipherKey, true) : false;

        if ($rawKey == false || strlen($rawKey) !== self::KEY_BYTES) {
            $rawKey = random_bytes(self::KEY_BYTES);
            update_option(self::CIPHER_KEY_OPTION, base64_encode($rawKey), true);
        }

        // Cache the key for future use.
        self::$key = $rawKey;

        return self::$key;
    }

    /**
     * Encrypts a given plain text string and returns the encrypted token.
     *
     * @param string $plainText
     * @return string The encrypted token.
     */
    public static function encrypt($plainText)
    {
        $nonce  = random_bytes(self::NONCE_BYTES);
        $cipher = sodium_crypto_secretbox($plainText, $nonce, self::key());
        return rtrim(strtr(base64_encode($nonce . $cipher), '+/', '-_'), '=');
    }

    /**
     * Decrypts a given encrypted token and returns the plain text string.
     *
     * @param string $token The encrypted token to decrypt.
     * @return string|false The decrypted plain text string, or false if decryption failed.
     */
    public static function decrypt($token)
    {
        $token = base64_decode(strtr($token, '-_', '+/'));

        if ($token === false || strlen($token) < self::NONCE_BYTES) {
            WP_Statistics::log(esc_html__('Malformed token', 'wp-statistics'), 'error');
            return false;
        }

        $nonce  = substr($token, 0, self::NONCE_BYTES);
        $cipher = substr($token, self::NONCE_BYTES);
        $plain  = sodium_crypto_secretbox_open($cipher, $nonce, self::key());

        if ($plain === false) {
            WP_Statistics::log(esc_html__('Failed to decrypt token', 'wp-statistics'), 'error');
            return false;
        }

        return $plain;
    }
}