<?php

namespace WP_Statistics\Service\Logger;

use WP_Statistics\Service\Logger\Provider\FileProvider;
use WP_Statistics\Service\Logger\Provider\TrackerProvider;

/**
 * Factory for creating logger services with different providers.
 */
class LoggerFactory
{
    /**
     * @var string|null Logger type.
     */
    private static $loggerType = null;

    /**
     * Creates a logger service based on the provided type.
     * 
     * @param string|null $type Logger type ('tracker' or 'file').
     * @return LoggerService The logger service instance.
     */
    public static function logger($type = null)
    {
        self::$loggerType = $type;

        $provider      = self::getProviderInstance();
        $loggerService = new LoggerService($provider);

        return $loggerService->getProvider();
    }

    /**
     * Returns the appropriate provider instance.
     * 
     * @return object The provider instance (TrackerProvider or FileProvider).
     */
    public static function getProviderInstance()
    {
        $providerName = is_null(self::$loggerType) || 'tracker' === self::$loggerType
            ? TrackerProvider::class
            : FileProvider::class;

        if (class_exists($providerName)) {
            return new $providerName();
        }

        return new TrackerProvider();
    }
}
