<?php

namespace WP_Statistics\Service\Admin\ReactApp\Managers;

use WP_Statistics\Service\Admin\ReactApp\Contracts\LocalizeDataProviderInterface;

/**
 * Manager for handling localized data sent to React components.
 *
 * This manager coordinates multiple data providers, collecting their data
 * and preparing it for localization to React. It follows the composite pattern,
 * allowing different providers to contribute their specific data independently.
 *
 * Supports lazy loading - providers are only instantiated when data is actually needed.
 *
 * Usage:
 * ```
 * $manager = new LocalizeDataManager();
 * // Lazy loading (recommended) - class names only, no instantiation
 * $manager->registerProviderClass(LayoutDataProvider::class);
 * $manager->registerProviderClass(UserInfoProvider::class);
 * // Or immediate registration
 * $manager->registerProvider(new CustomProvider());
 * $manager->init();
 * ```
 *
 * @since 15.0.0
 */
class LocalizeDataManager
{
    /**
     * Array of registered data provider instances.
     *
     * @var LocalizeDataProviderInterface[]
     */
    private $providers = [];

    /**
     * Array of provider class names for lazy loading.
     *
     * @var string[]
     */
    private $providerClasses = [];

    /**
     * Whether providers have been resolved.
     *
     * @var bool
     */
    private $resolved = false;

    /**
     * Hook priority for the localize filter.
     *
     * @var int
     */
    private $filterPriority = 10;

    /**
     * Register a data provider instance.
     *
     * Adds a new data provider to the collection. Providers will be
     * processed in the order they are registered.
     *
     * @param LocalizeDataProviderInterface $provider The data provider to register
     * @return self For method chaining
     */
    public function registerProvider(LocalizeDataProviderInterface $provider)
    {
        $this->providers[] = $provider;
        return $this;
    }

    /**
     * Register a provider class for lazy loading.
     *
     * The provider will only be instantiated when data is actually needed.
     *
     * @param string $className Fully qualified class name
     * @return self For method chaining
     */
    public function registerProviderClass(string $className)
    {
        $this->providerClasses[] = $className;
        return $this;
    }

    /**
     * Register multiple providers at once.
     *
     * @param LocalizeDataProviderInterface[] $providers Array of data providers
     * @return self For method chaining
     */
    public function registerProviders(array $providers)
    {
        foreach ($providers as $provider) {
            if ($provider instanceof LocalizeDataProviderInterface) {
                $this->registerProvider($provider);
            }
        }
        return $this;
    }

    /**
     * Register multiple provider classes for lazy loading.
     *
     * @param string[] $classNames Array of fully qualified class names
     * @return self For method chaining
     */
    public function registerProviderClasses(array $classNames)
    {
        foreach ($classNames as $className) {
            $this->registerProviderClass($className);
        }
        return $this;
    }

    /**
     * Resolve all lazy-loaded provider classes.
     *
     * Instantiates providers only when needed.
     *
     * @return void
     */
    private function resolveProviders()
    {
        if ($this->resolved) {
            return;
        }

        foreach ($this->providerClasses as $className) {
            $this->providers[] = new $className();
        }

        $this->resolved = true;
    }

    /**
     * Initialize the manager and hook into WordPress.
     *
     * Sets up the filter that will provide localized data to React.
     * Must be called after all providers are registered.
     *
     * @return void
     */
    public function init()
    {
        add_filter('wp_statistics_react_localized_data', [$this, 'addLocalizedData'], $this->filterPriority);
    }

    /**
     * Add localized data from all registered providers.
     *
     * This method is called by WordPress filter and collects data
     * from all registered providers that should be loaded.
     * Lazy-loaded providers are instantiated here when data is actually needed.
     *
     * @param array $data Existing localized data
     * @return array Modified data with provider data merged in
     */
    public function addLocalizedData(array $data)
    {
        // Resolve lazy-loaded providers now that we need the data
        $this->resolveProviders();

        foreach ($this->providers as $provider) {
            $key          = $provider->getKey();
            $providerData = $provider->getData();

            // Merge if key exists, otherwise set directly
            if (isset($data[$key]) && is_array($data[$key]) && is_array($providerData)) {
                $data[$key] = array_merge($data[$key], $providerData);
            } else {
                $data[$key] = $providerData;
            }
        }

        return $data;
    }

    /**
     * Set the filter priority.
     *
     * @param int $priority WordPress filter priority
     * @return self For method chaining
     */
    public function setFilterPriority(int $priority)
    {
        $this->filterPriority = $priority;
        return $this;
    }

    /**
     * Get all registered providers.
     *
     * Note: This resolves any lazy-loaded providers.
     *
     * @return LocalizeDataProviderInterface[] Array of registered providers
     */
    public function getProviders()
    {
        $this->resolveProviders();
        return $this->providers;
    }
}

