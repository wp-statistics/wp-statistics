<?php

namespace WP_Statistics\Service\Admin\ReactApp\Providers;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Admin\ReactApp\Contracts\LocalizeDataProviderInterface;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\Notification\NotificationFactory;
use WP_Statistics\Utils\User;

/**
 * Provider for dashboard header data.
 *
 * This provider is responsible for delivering data that is consumed by
 * the dashboard header area in the React application (e.g. title,
 * user-related info, actions, or badges). The actual payload can be
 * injected or modified by other components through a WordPress filter.
 *
 * @since 15.0.0
 */
class HeaderDataProvider implements LocalizeDataProviderInterface
{
    /**
     * Get header-specific data for the dashboard.
     *
     * By default this returns an empty payload that is meant to be populated
     * via the `wp_statistics_dashboard_header_data` filter, so that other
     * services can inject the actual header data at runtime.
     *
     * @return array Array of header data
     */
    public function getData()
    {
        $manageCap    = User::getExistingCapability(Option::getValue('manage_capability', 'manage_options'));
        $hasManageCap = $manageCap && current_user_can($manageCap);

        $data = [
            'notifications' => [
                'isActive' => Option::getValue('display_notifications', true),
                'items'    => NotificationFactory::getAllNotifications(),
                'icon'     => 'Bell',
                'label'    => esc_html__('Notifications', 'wp-statistics'),
            ],
            'privacyAudit' => [
                'isActive' => apply_filters('wp_statistics_enable_help_icon', true) && $hasManageCap,
                'url'      => '#',
                'icon'     => 'ShieldCheck',
                'label'    => esc_html__('Privacy Audit', 'wp-statistics'),
            ],
            'premiumBadge' => [
                'isActive' => LicenseHelper::isValidLicenseAvailable(),
                'url'      => esc_url(WP_STATISTICS_SITE_URL . '/pricing/?utm_source=wp-statistics&utm_medium=link&utm_campaign=header'),
                'icon'     => 'Crown',
                'label'    => esc_html__('Upgrade To Premium', 'wp-statistics'),
            ],
        ];

        /**
         * Allow other components to enrich the header payload before it is
         * localized for the React dashboard.
         *
         * The same filter is shared with other providers so a single hook can
         * aggregate all dashboard bootstrap data in one place.
         *
         * @param array $data Array of header data
         * @since 15.0.0
         */
        return apply_filters('wp_statistics_dashboard_header_data', $data);
    }

    /**
     * Get the key under which the header data will be localized.
     *
     * @return string The key 'header'
     */
    public function getKey()
    {
        return 'header';
    }
}
