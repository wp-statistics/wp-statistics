<?php

namespace WP_Statistics\Service\Logger;

/**
 * Handles interaction with a logger provider.
 *
 * @package   Database
 * @version   1.0.0
 * @since     14.11.2
 * @author    Hooman
 */
class LoggerService
{
    /**
     * @var LoggerServiceProviderInterface Logger provider instance.
     */
    protected $provider;

    /**
     * LoggerService constructor.
     * 
     * @param LoggerServiceProviderInterface $provider The provider instance to be used by the logger.
     */
    public function __construct(LoggerServiceProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Returns the logger provider instance.
     * 
     * @return LoggerServiceProviderInterface The logger provider.
     */
    public function getProvider()
    {
        return $this->provider;
    }
}
