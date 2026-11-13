<?php

namespace WP_Statistics\Service\Admin\Notification;

use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;

class NotificationFactory
{
    /**
     * The visible notifications of this request, and the stored data they came from.
     * The header asks for the dot, the badge and the count on one page, so the filter runs once.
     * Comparing with the stored data keeps it right after a dismiss in the same request.
     */
    private static $visibleNotifications = [];
    private static $visibleSource        = null;

    /**
     * Stored notifications that are inside their dates and match the site's tags.
     *
     * @return array
     */
    private static function getVisibleNotifications()
    {
        $source = self::getRawNotificationsData()['data'] ?? [];

        if (self::$visibleSource !== $source) {
            self::$visibleNotifications = NotificationProcessor::filterNotificationsByTags($source);
            self::$visibleSource        = $source;
        }

        $license = self::getLicenseNotification();

        return $license ? array_merge([$license], self::$visibleNotifications) : self::$visibleNotifications;
    }

    /**
     * A local notice, not from the server: add-ons that are installed but have no valid license or an expired one.
     * It clears itself once the licenses are fixed, so it has no Dismiss button.
     *
     * @return array|null
     */
    private static function getLicenseNotification()
    {
        if (!LicenseHelper::canManageLicenses()) {
            return null;
        }

        $summary = LicenseHelper::getLicenseSummary();

        if (empty($summary['attention'])) {
            return null;
        }

        if ($summary['expired'] > 0) {
            /* translators: %d: number of add-ons */
            $title = sprintf(_n('%d add-on has an expired license', '%d add-ons have an expired license', $summary['expired'], 'wp-statistics'), $summary['expired']);
            $text  = __('Renew or enter a new license key to keep getting updates and support.', 'wp-statistics');
        } else {
            /* translators: %d: number of add-ons */
            $title = sprintf(_n('%d add-on has no license yet', '%d add-ons have no license yet', $summary['attention'], 'wp-statistics'), $summary['attention']);
            $text  = __('Enter your license key to unlock updates and support.', 'wp-statistics');
        }

        return [
            'id'               => 'license-alert',
            'title'            => $title,
            'icon'             => wp_json_encode('🔑'),
            'description'      => wp_json_encode($text),
            'primary_button'   => ['title' => __('Enter license', 'wp-statistics'), 'url' => admin_url('admin.php?page=wps_plugins_page&tab=add-license')],
            'secondary_button' => ['title' => '', 'url' => ''],
            'background_color' => 'warning',
            'plugin_name'      => 'WP Statistics',
            'activated_at'     => current_time('Y-m-d\TH:i'),
            'dismiss'          => false,
            'local'            => true,
        ];
    }

    /**
     * Retrieves the raw notification data from WordPress options.
     *
     * @return array The raw notification data stored in the database.
     */
    public static function getRawNotificationsData()
    {
        return get_option('wp_statistics_notifications', []);
    }

    /**
     * Retrieves all notifications after processing and filtering.
     *
     * @return array Processed and decorated notifications.
     */
    public static function getAllNotifications()
    {
        return NotificationProcessor::decorateNotifications(self::getVisibleNotifications());
    }

    /**
     * Checks if there are updated notifications.
     *
     * @return bool
     */
    public static function hasUpdatedNotifications()
    {
        $notifications = self::getVisibleNotifications();

        foreach ($notifications as $notification) {
            if (empty($notification['dismiss'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the bell badge of the newest unread notification that has one, or null.
     *
     * @return array|null ['text' => string, 'urgent' => bool]
     */
    public static function getBellBadge()
    {
        $notifications = self::getVisibleNotifications();

        foreach ($notifications as $notification) {
            if (empty($notification['dismiss']) && !empty($notification['extra_data']['badge'])) {
                return [
                    'text'   => (string)$notification['extra_data']['badge'],
                    'urgent' => ($notification['background_color'] ?? '') === 'danger',
                ];
            }
        }

        return null;
    }

    /**
     * Returns the count of new notifications, or false if no new notifications exist.
     *
     * @return int False if no new notifications exist, or the count of new notifications.
     */
    public static function getNewNotificationCount()
    {
        $notifications = self::getVisibleNotifications();

        $count = 0;

        foreach ($notifications as $notification) {
            if (empty($notification['dismiss'])) {
                $count++;
            }
        }

        return $count;
    }
}