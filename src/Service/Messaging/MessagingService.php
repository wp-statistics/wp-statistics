<?php

namespace WP_Statistics\Service\Messaging;

/**
 * Lightweight wrapper that instantiates a messaging provider and exposes it
 * for further configuration.
 *
 * @todo template part should be improved.
 * @since 15.0.0
 */
class MessagingService
{
    /**
     * Concrete provider instance (e.g. MailProvider, SmsProvider).
     *
     * @var object
     */
    private $provider;

    /**
     * MessagingService constructor is private to enforce factory usage.
     *
     * @param object $provider Instantiated provider.
     */
    private function __construct($provider)
    {
        $this->provider = $provider;
    }

    /**
     * Factory helper that creates a service for the requested provider class.
     *
     * @param string $providerClass Fully qualified class name.
     * @return self
     * @throws \InvalidArgumentException When the class does not exist.
     */
    public static function make($providerClass)
    {
        if (!class_exists($providerClass)) {
            throw new \InvalidArgumentException("Provider {$providerClass} not found.");
        }

        return new self(new $providerClass());
    }

    /**
     * Return the underlying provider so callers can chain its API.
     *
     * @return object
     */
    public function provider()
    {
        return $this->provider;
    }
}