<?php

namespace WP_Statistics\Abstracts;

/**
 * Base controller for handling dashboard functionality.
 *
 * This abstract class provides the foundation for dashboard controllers in WP Statistics.
 * It handles common functionality such as:
 * - AJAX nonce management
 * - Data localization for React components
 * - Filter initialization
 *
 * Each controller that extends this class should implement the getActions() method
 * to define its available AJAX endpoints.
 *
 * @since 15.0.0
 */
abstract class BaseDashboardController
{
    /**
     * The nonce key for dashboard actions.
     *
     * This key is used to generate and verify nonces for AJAX requests
     * to ensure security of dashboard operations.
     *
     * @since 15.0.0
     * @var string
     */
    protected $nonceKey = 'wp_statistics_dashboard_nonce';

    /**
     * Add dashboard localized data.
     *
     * Adds necessary data to be localized for React components, including:
     * - Security nonce for AJAX requests
     * - Any additional data needed by the dashboard
     *
     * @param array $data The data to be localized
     * @return array Modified data with nonce and additional dashboard data
     * @since 15.0.0
     */
    public function addDashboardLocalizedData($data)
    {
        if (function_exists('wp_create_nonce')) {
            $data['dashboardNonce'] = wp_create_nonce($this->nonceKey);
            $data['ajaxUrl']        = admin_url( 'admin-ajax.php' );
        }

        return $data;
    }

    /**
     * Initialize filters for the controller.
     *
     * Sets up WordPress filters needed for the dashboard functionality.
     * Currently adds the dashboard localized data to the wp_statistics_react_localized_data filter.
     *
     * @return void
     * @since 15.0.0
     */
    public function initFilters()
    {
        if (function_exists('add_filter')) {
            add_filter('wp_statistics_react_localized_data', [$this, 'addDashboardLocalizedData']);
        }
    }

    /**
     * Get list of available AJAX actions.
     *
     * Child classes must implement this method to define their available AJAX endpoints.
     * The returned array should contain action names that will be automatically registered
     * with WordPress AJAX handling system.
     *
     * @return array List of AJAX action names supported by this controller
     * @since 15.0.0
     */
    abstract public function getActions();
}