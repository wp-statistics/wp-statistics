<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Controllers;

use WP_Statistics\Abstracts\BaseDashboardController;
use WP_Statistics\Service\Admin\DashboardBootstrap\Views\SettingPage;
use WP_Statistics\Utils\Request;

/**
 * Controller for handling setting page.
 *
 * @since 15.0.0
 */
class SettingsController extends BaseDashboardController
{
    /**
     * The page view.
     *
     * @var string|null
     */
    protected $pageView = SettingPage::class;

    /**
     * Get list of available AJAX actions.
     *
     * Returns an array of action names that this controller can handle.
     * These actions are automatically registered with WordPress AJAX.
     *
     * @return array List of available AJAX actions
     */
    public function getActions()
    {
        return [];
    }
}
