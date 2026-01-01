<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Managers;

use WP_Statistics\Service\Admin\DashboardBootstrap\Contracts\LocalizeDataProviderInterface;

/**
 * Manager for handling localized data sent to React components.
 *
 * This manager coordinates multiple data providers, collecting their data
 * and preparing it for localization to React. It follows the composite pattern,
 * allowing different providers to contribute their specific data independently.
 *
 * Usage:
 * ```
 * $manager = new LocalizeDataManager();
 * $manager->registerProvider(new LayoutDataProvider());
 * $manager->registerProvider(new UserInfoProvider());
 * $manager->init();
 * ```
 *
 * @since 15.0.0
 */
class LocalizeDataManager
{
    /**
     * Array of registered data providers.
     *
     * @var LocalizeDataProviderInterface[]
     */
    private $providers = [];

    /**
     * Hook priority for the localize filter.
     *
     * @var int
     */
    private $filterPriority = 10;

    /**
     * Register a data provider.
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
     *
     * @param array $data Existing localized data
     * @return array Modified data with provider data merged in
     */
    public function addLocalizedData(array $data)
    {
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
     * @return LocalizeDataProviderInterface[] Array of registered providers
     */
    public function getProviders()
    {
        return $this->providers;
    }
}

