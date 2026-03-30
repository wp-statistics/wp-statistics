<?php

namespace WP_Statistics\Service\Logger;

use WP_Statistics\Service\Logger\Provider\FileProvider;

/**
 * Factory for creating logger services with different providers.
 */
class LoggerFactory
{
    /**
     * Creates a logger service.
     *
     * @param string|null $type Logger type (reserved for future use).
     * @return FileProvider The logger provider instance.
     */
    public static function logger($type = null)
    {
        $provider      = new FileProvider();
        $loggerService = new LoggerService($provider);

        return $loggerService->getProvider();
    }
}
