<?php

namespace WP_Statistics\Service\Admin\HelpCenter;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\Notification\NotificationFactory;

class HelpCenterManager
{
    public function __construct()
    {
        add_filter('wp_statistics_admin_menu_list', [$this, 'addMenuItem']);
    }

    /**
     * Add menu item
     *
     * @param array $items
     * @return array
     */
    public function addMenuItem($items)
    {
        $notificationBadge    = '';
        $displayNotifications = Option::get('display_notifications') ? true : false;

        if ($displayNotifications) {
            $newNotificationCount = NotificationFactory::getNewNotificationCount();

            if ($newNotificationCount > 0) {
                $notificationCount = $newNotificationCount > 9 ? esc_html('9+') : number_format_i18n($newNotificationCount);
                /* translators: %s: string value */
                $notificationTitle = esc_attr(sprintf(esc_html__('%s plugin notifications', 'wp-statistics'), $notificationCount));
                $notificationBadge = " <span class='update-plugins count-$notificationCount' title='$notificationTitle'><span class='update-count'>" . $notificationCount . "</span></span>";
            }
        }


        $items['help_center'] = [
            'sub'      => 'overview',
            'title'    => esc_html__('Help Center', 'wp-statistics') . $notificationBadge,
            'page_url' => 'help-center',
            'callback' => HelpCenterPage::class,
            'priority' => 999
        ];

        return $items;
    }
}