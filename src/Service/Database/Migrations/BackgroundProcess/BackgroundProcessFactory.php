<?php

namespace WP_Statistics\Service\Database\Migrations\BackgroundProcess;

/**
 * Factory class to get background process instances.
 * 
 * @package WP_Statistics\Service\Database\Migrations\BackgroundProcess
 */
class BackgroundProcessFactory
{
    /**
     * Get a background process instance by its key.
     * 
     * @param string $processKey The key identifying the background process.
     * 
     * @return object|null The background process instance or null if not found.
     */
    public static function getBackgroundProcess($processKey)
    {
        return (new BackgroundProcessManager())->getBackgroundProcess($processKey);
    }

    public static function isProcessDone()
    {
        
    }
}