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
        // An unknown tab (an old link, or a tab that is off right now, such as Logged-in Users while user
        // tracking is off or a tab of a turned-off add-on) shows the default tab instead: no error page, no log entry.
        // Only when there is no valid tab at all is it a real error.
        if (!in_array($this->getCurrentTab(), (array)$this->tabs)) {
            throw new SystemErrorException(
                esc_html__('Invalid tab provided.', 'wp-statistics')
            );
        }
    }

    /**
     * Retrieves the current tab: the `tab` query value when it is one of this page's tabs, else the default tab.
     *
     * @return string The current tab.
     */
    public function getCurrentTab()
    {
        $tab = Request::get('tab', $this->defaultTab);

        if (!empty($this->tabs) && !in_array($tab, (array)$this->tabs)) {
            return in_array($this->defaultTab, (array)$this->tabs) ? $this->defaultTab : reset($this->tabs);
        }

        return $tab;
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