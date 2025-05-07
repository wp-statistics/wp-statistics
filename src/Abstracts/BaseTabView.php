<?php

namespace WP_Statistics\Abstracts;

use WP_Statistics\Exception\SystemErrorException;
use WP_Statistics\Utils\Request;

abstract class BaseTabView extends BaseView
{
    protected $defaultTab;
    protected $tabs;

    public function __construct()
    {
        // Throw error when invalid tab provided
        if (!in_array($this->getCurrentTab(), $this->tabs)) {
            throw new SystemErrorException(
                esc_html__('Invalid tab provided.', 'wp-statistics')
            );
        }
    }

    /**
     * Retrieves the current tab.
     *
     * @param string $tab The current tab.
     * @return string The current tab.
     */
    protected function getCurrentTab()
    {
        return Request::get('tab', $this->defaultTab);
    }

    /**
     * Checks whether the current tab matches the given tab.
     *
     * @param string|array $tab The tab to check against the current tab, or tabs.
     * @return bool True if the current tab matches the given tab, false otherwise.
     */
    protected function isTab($tab)
    {
        $activeTab = $this->getCurrentTab();

        // If given tab is an array, check if it contains current tab.
        if (is_array($tab)) {
            return in_array($activeTab, $tab);
        }

        return $activeTab === $tab;
    }

    /**
     * Retrieves data for the current tab. For example, for visitors tab, getVisitorsData() method will be called.
     *
     * @return array Tab data for the current tab.
     */
    protected function getTabData()
    {
        $currentTab     = ucwords($this->getCurrentTab(), '-');
        $tabDataMethod  = 'get' . str_replace('-', '', $currentTab) . 'Data';

        if (!method_exists($this, $tabDataMethod)) {
            // Filter to add data for locked tab
            return apply_filters("wp_statistics_{$this->getCurrentPage()}_{$this->getCurrentTab()}_data", []);
        };

        return $this->$tabDataMethod();
    }
}