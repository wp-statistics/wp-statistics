<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Contracts;

/**
 * Interface for localize data providers.
 *
 * Classes implementing this interface are responsible for providing
 * specific data sets that will be localized and sent to React components.
 *
 * Each provider should handle one specific type of data (e.g., sidebar, user info, etc.)
 * to maintain single responsibility principle.
 *
 * @since 15.0.0
 */
interface LocalizeDataProviderInterface
{
    /**
     * Get the data to be localized.
     *
     * Returns an associative array of data that will be merged into
     * the localized data object sent to React.
     *
     * @return array Associative array of data to be localized
     */
    public function getData();

    /**
     * Get the key under which this data will be stored.
     *
     * This key will be used as the top-level property in the localized
     * data object accessible in React.
     *
     * @return string The data key (e.g., 'sidebar', 'userInfo', 'settings')
     */
    public function getKey();
}

