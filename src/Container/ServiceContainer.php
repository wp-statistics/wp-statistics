<?php

namespace WP_Statistics\Container;

/**
 * Service Container for WP Statistics v15.
 *
 * Provides lazy loading of services for optimal performance.
 * Services are only instantiated when first accessed.
 *
 * @since 15.0.0
 */
class ServiceContainer
{
    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static $instance = null;

    /**
     * Registered service factories.
     *
     * @var array<string, callable>
     */
    private $factories = [];

    /**
     * Instantiated service instances.
     *
     * @var array<string, object>
     */
    private $instances = [];

    /**
     * Service aliases.
     *
     * @var array<string, string>
     */
    private $aliases = [];

    /**
     * Private constructor for singleton.
     */
    private function __construct()
    {
    }

    /**
     * Get the singleton instance.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Register a service factory.
     *
     * @param string   $id      Service identifier.
     * @param callable $factory Factory function that returns the service.
     * @return self
     */
    public function register(string $id, callable $factory): self
    {
        $this->factories[$id] = $factory;
        return $this;
    }

    /**
     * Register a singleton service.
     *
     * @param string $id       Service identifier.
     * @param object $instance Pre-instantiated service.
     * @return self
     */
    public function singleton(string $id, object $instance): self
    {
        $this->instances[$id] = $instance;
        return $this;
    }

    /**
     * Register an alias for a service.
     *
     * @param string $alias  Alias name.
     * @param string $target Target service ID.
     * @return self
     */
    public function alias(string $alias, string $target): self
    {
        $this->aliases[$alias] = $target;
        return $this;
    }

    /**
     * Get a service by ID (lazy loading).
     *
     * @param string $id Service identifier.
     * @return object|null
     */
    public function get(string $id)
    {
        // Resolve alias
        if (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        }

        // Return cached instance
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        // Create from factory
        if (isset($this->factories[$id])) {
            $this->instances[$id] = ($this->factories[$id])($this);
            return $this->instances[$id];
        }

        return null;
    }

    /**
     * Check if a service is registered.
     *
     * @param string $id Service identifier.
     * @return bool
     */
    public function has(string $id): bool
    {
        if (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        }

        return isset($this->instances[$id]) || isset($this->factories[$id]);
    }

    /**
     * Magic method for accessing services as properties.
     *
     * @param string $name Service name.
     * @return object|null
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * Reset the container (useful for testing).
     *
     * @return void
     */
    public function reset(): void
    {
        $this->instances = [];
    }
}
