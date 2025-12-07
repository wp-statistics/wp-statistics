<?php
namespace WP_Statistics\Globals;

/**
 * The Context class is a utility for managing a global context using key-value pairs.
 * It allows you to add, update, retrieve, and remove data from a centralized context array.
 * This can be useful for maintaining state or configuration data that needs to be accessed globally.
 */
class Context
{
    /**
     * Store all context data
     *
     * @var array
     */
    protected static $data = [];

    /**
     * Add a value to context.
     * If key exists, it will not overwrite.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function add($key, $value)
    {
        if (!isset(static::$data[$key])) {
            static::$data[$key] = $value;
        }
    }

    /**
     * Update an existing value in context or add if not exists
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function update($key, $value)
    {
        static::$data[$key] = $value;
    }

    /**
     * Get a value from context
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return static::$data[$key] ?? $default;
    }

    /**
     * Remove a value from context
     *
     * @param string $key
     * @return void
     */
    public static function unset($key)
    {
        unset(static::$data[$key]);
    }

    /**
     * Check if a key exists in context
     *
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        return array_key_exists($key, static::$data);
    }

    /**
     * Get all context data (optional helper)
     *
     * @return array
     */
    public static function all()
    {
        return static::$data;
    }
}