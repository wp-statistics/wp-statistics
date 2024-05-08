<?php

namespace WP_Statistics\Components;

/**
 * Simple singleton that we will extend
 */
class Singleton
{

    /**
     * @var Singleton $instance Instance
     */
    private static $instance;

    /**
     * Construct
     */
    private function __construct()
    {
    }

    /**
     * Get Instance
     *
     * @return Singleton Instance
     */
    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}