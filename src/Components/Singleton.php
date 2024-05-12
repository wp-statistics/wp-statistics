<?php

namespace WP_Statistics\Components;

/**
 * Simple singleton that we will extend
 */
class Singleton
{

    /**
     * @var Singleton[] $instance Instance
     */
    private static $instances = [];

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
    public static function instance()
    {
        $class = static::class;

        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static();
        }

        return self::$instances[$class];
    }
}