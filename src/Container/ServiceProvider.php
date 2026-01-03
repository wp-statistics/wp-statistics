<?php

namespace WP_Statistics\Container;

/**
 * Service Provider Interface.
 *
 * Defines the contract for registering services with the container.
 *
 * @since 15.0.0
 */
interface ServiceProvider
{
    /**
     * Register services with the container.
     *
     * @param ServiceContainer $container The service container.
     * @return void
     */
    public function register(ServiceContainer $container): void;

    /**
     * Bootstrap any services.
     *
     * Called after all providers are registered.
     *
     * @param ServiceContainer $container The service container.
     * @return void
     */
    public function boot(ServiceContainer $container): void;
}
