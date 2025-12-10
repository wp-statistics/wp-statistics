<?php

namespace WP_Statistics\Service\AnalyticsQuery\Contracts;

/**
 * Interface for registries.
 *
 * @since 15.0.0
 */
interface RegistryInterface
{
    /**
     * Register an item.
     *
     * @param string $name The item name.
     * @param mixed  $item The item to register.
     * @return void
     */
    public function register(string $name, $item): void;

    /**
     * Check if an item exists.
     *
     * @param string $name The item name.
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Get an item by name.
     *
     * @param string $name The item name.
     * @return mixed|null
     */
    public function get(string $name);

    /**
     * Get all registered item names.
     *
     * @return array
     */
    public function getAll(): array;
}
