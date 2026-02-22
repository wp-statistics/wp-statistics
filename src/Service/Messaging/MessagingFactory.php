<?php

namespace WP_Statistics\Service\Messaging;

use WP_Statistics\Service\Messaging\Provider\MailProvider;
use WP_Statistics\Service\Messaging\Provider\SmsProvider;

/**
 * Static helper that routes outgoing messages to the appropriate provider.
 * All helpers return the boolean result from the provider’s `send()` call.
 *
 * @since 15.0.0
 */
class MessagingFactory
{
    /**
     * Send a plain e‑mail using the default provider.
     *
     * @param string|array $to Recipient address or list.
     * @param string $subject Subject line.
     * @param string $content Message body (HTML allowed).
     * @param bool|string $template `true` for default layout or absolute path.
     * @param array $args Additional template variables.
     *
     * @return bool  True on success, false on failure.
     */
    public static function mail($to, $subject, $content, $template = true, $args = [])
    {
        $service = MessagingService::make(MailProvider::class);

        return $service->provider()
            ->init()
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($content)
            ->setTemplate($template, $args)
            ->send();
    }

    /**
     * Send an SMS message.
     *
     * @param string $to Destination phone number.
     * @param string $text Message text.
     *
     * @return bool  True on success, false on failure.
     */
    public static function sms($to, $text)
    {
        $service = MessagingService::make(SmsProvider::class);

        return $service->provider()
            ->init()
            ->setTo($to)
            ->setText($text)
            ->send();
    }
}