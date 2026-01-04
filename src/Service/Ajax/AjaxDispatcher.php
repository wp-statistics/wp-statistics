<?php

namespace WP_Statistics\Service\Ajax;

use WP_Statistics\Components\Ajax;

/**
 * AJAX Dispatcher
 *
 * Processes the wp_statistics_ajax_list filter and registers all AJAX handlers.
 * This centralizes AJAX registration that was previously scattered across components.
 *
 * @since 15.0.0
 */
class AjaxDispatcher
{
    /**
     * Initialize the AJAX dispatcher.
     *
     * @since 15.0.0
     */
    public function __construct()
    {
        $this->register();
    }

    /**
     * Register all AJAX handlers from the filter.
     *
     * Hooks into 'init' to ensure all components have added their handlers
     * to the wp_statistics_ajax_list filter before we process them.
     *
     * @return void
     * @since 15.0.0
     */
    public function register()
    {
        add_action('init', [$this, 'processAjaxList'], 20);
    }

    /**
     * Process the AJAX list and register all handlers.
     *
     * @return void
     * @since 15.0.0
     */
    public function processAjaxList()
    {
        /**
         * Filter to collect AJAX handler registrations.
         *
         * Components can add their AJAX handlers to this list:
         * $list[] = [
         *     'class'  => $this,           // Object instance with callback method
         *     'action' => 'my_action',     // Action name (without wp_statistics_ prefix)
         *     'public' => true,            // Whether to allow non-logged-in users
         * ];
         *
         * The callback method should be named: {action}_action_callback()
         *
         * @param array $list Array of AJAX handler configurations
         * @return array Modified list of handlers
         * @since 15.0.0
         */
        $ajaxList = apply_filters('wp_statistics_ajax_list', []);

        if (empty($ajaxList) || !is_array($ajaxList)) {
            return;
        }

        foreach ($ajaxList as $ajax) {
            if (!isset($ajax['action'])) {
                continue;
            }

            $action   = $ajax['action'];
            $class    = $ajax['class'] ?? null;
            $public   = $ajax['public'] ?? true;
            $callback = $ajax['callback'] ?? null;

            // Determine the callback
            if ($callback !== null && is_callable($callback)) {
                // Use provided callback directly
                $callbackFunction = $callback;
            } elseif ($class !== null && is_object($class)) {
                // Use standard naming convention: {action}_action_callback
                $method = $action . '_action_callback';
                if (method_exists($class, $method)) {
                    $callbackFunction = [$class, $method];
                } else {
                    continue; // Skip if method doesn't exist
                }
            } else {
                continue; // Skip if no valid callback
            }

            // Register the AJAX handler
            Ajax::register($action, $callbackFunction, $public);
        }
    }
}
