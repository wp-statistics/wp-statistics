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
     * The page view.
     *
     * @var string|null
     */
    protected $pageView = null;

    /**
     * Class constructor.
     *
     * Hooks into the admin menu to register the Data Migration page.
     */
    public function __construct()
    {
        add_filter('wp_statistics_admin_menu_list', [$this, 'addMenuItem']);
    }

    /**
     * Adds the "Data Migration" item to the WP Statistics admin submenu.
     *
     * @param array $items Existing menu items.
     * @return array Modified menu items with the Data Migration entry added.
     */
    public function addMenuItem($items)
    {
        if (empty($this->pageView)) {
            return $items;
        }

        $view = new $this->pageView();

        $items[$view->getPageIndex()] = [
            'sub'      => '',
            'title'    => $view->getPageTitle(),
            'page_url' => $view->getPageSlug(),
            'callback' => $this->pageView,
            'priority' => $view->getPriority(),
        ];

        return $items;
    }

    /**
     * Get list of available AJAX actions.
     *
     * Child classes must implement this method to define their available AJAX endpoints.
     * The returned array should contain action WP_Statistics_names that will be automatically registered
     * with WordPress AJAX handling system.
     *
     * @return array List of AJAX action WP_Statistics_names supported by this controller
     */
    abstract public function getActions();
}