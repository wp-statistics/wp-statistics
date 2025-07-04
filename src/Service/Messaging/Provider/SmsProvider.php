<?php

namespace WP_Statistics\Service\Messaging\Provider;

/**
 * Handles SMS dispatch via the `wp_sms_send()` function provided by the
 * WP‑SMS plugin or any compatible gateway wrapper.
 *
 * The class exposes a minimal fluent interface—`init()`, `setTo()`,
 * `setText()`, `send()`—sufficient for the messaging factory to configure
 * and trigger a message.
 *
 * @package WP_Statistics\Service\Admin\Messaging\Provider
 * @since 15.0.0
 */
class SmsProvider
{
    /**
     * Destination phone number.
     *
     * @var string
     */
    private $to = '';

    /**
     * Message text sent to the recipient.
     *
     * @var string
     */
    private $text = '';

    /**
     * Return the current instance for fluent chaining.
     *
     * @return self
     */
    public function init()
    {
        return $this;
    }

    /**
     * Define the recipient phone number.
     *
     * @param string $to E.164 formatted number or gateway‑specific format.
     * @return $this
     */
    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * Set the SMS body text.
     *
     * @param string $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Dispatch the SMS using `wp_sms_send()`.
     *
     * @return bool True if sending succeeded, false otherwise.
     */
    public function send()
    {
        if (!function_exists('wp_sms_send')) {
            return false;
        }

        if ($this->to === '' || $this->text === '') {
            return false;
        }

        $result = wp_sms_send($this->to, $this->text);

        return !is_wp_error($result);
    }
}