<?php

namespace WP_Statistics\Service\Admin\Notification;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Admin\ReactApp\Contracts\LocalizeDataProviderInterface;

/**
 * Notification Data Provider.
 *
 * Provides remote notification data for React pages via localization.
 *
 * @since 15.0.0
 */
class NotificationDataProvider implements LocalizeDataProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getKey()
    {
        return 'notifications';
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        $enabled = (bool) Option::getValue('display_notifications', true);

        if (!$enabled) {
            return [
                'enabled'     => false,
                'items'       => [],
                'unreadCount' => 0,
                'nonce'       => '',
            ];
        }

        $allItems    = array_map([self::class, 'sanitizeItem'], NotificationFactory::getAll());
        $dismissedIds = NotificationFactory::getDismissedIds();

        return [
            'enabled'      => true,
            'items'        => $allItems,
            'dismissedIds' => $dismissedIds,
            'unreadCount'  => NotificationFactory::getUnreadCount(),
            'nonce'        => wp_create_nonce('wp_statistics_notification_nonce'),
        ];
    }

    /**
     * Sanitize a notification item for frontend consumption.
     *
     * - Decodes JSON-encoded description field
     * - Strips HTML tags from description (plain text for React)
     * - Replaces {baseUrl} placeholder in button URLs
     *
     * @param array $item Raw notification item.
     * @return array Sanitized item.
     */
    private static function sanitizeItem($item)
    {
        $baseUrl = home_url();

        if (!empty($item['description'])) {
            $decoded = json_decode($item['description'], true);
            if (is_string($decoded)) {
                $item['description'] = $decoded;
            }
            $item['description'] = trim(wp_strip_all_tags($item['description']));
        }

        foreach (['primary_button', 'secondary_button'] as $key) {
            if (!empty($item[$key]['url'])) {
                $item[$key]['url'] = str_replace('{baseUrl}', $baseUrl, $item[$key]['url']);
            }
        }

        return $item;
    }
}
