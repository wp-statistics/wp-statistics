<?php

namespace WP_Statistics\Service\Admin\HelpCenter;

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
        $plusOne                 = '';
        $hasUpdatedNotifications = NotificationFactory::hasUpdatedNotifications();
        $displayNotifications    = Option::get('display_notifications') ? true : false;
        $newNotificationCount    = NotificationFactory::getNewNotificationCount();
        $notificationCount       = $newNotificationCount > 9 ? esc_html('9+') : number_format_i18n($newNotificationCount);

        if ($displayNotifications && $hasUpdatedNotifications && $newNotificationCount > 0) {
            $notificationTitle = esc_attr(sprintf(esc_html__('%s plugin notifications', 'wp-statistics'), $notificationCount));
            $plusOne           = " <span class='update-plugins count-$notificationCount' title='$notificationTitle'><span class='update-count'>" . $notificationCount . "</span></span>";
        }


        $items['help_center'] = [
            'sub'      => 'overview',
            'title'    => esc_html__('Help Center', 'wp-statistics') . $plusOne,
            'page_url' => 'help-center',
            'callback' => HelpCenterPage::class,
            'priority' => 999
        ];

        return $items;
    }
}