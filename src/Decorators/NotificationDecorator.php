<?php

namespace WP_Statistics\Decorators;

class NotificationDecorator
{
    /**
     * @var mixed The notification object being decorated.
     */
    private $notification;

    /**
     * NotificationDecorator constructor.
     *
     * @param mixed $notification The notification object.
     */
    public function __construct($notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get the notification ID.
     *
     * @return int|null The ID of the notification or null if not set.
     */
    public function getID()
    {
        return $this->notification->id ?? null;
    }

    /**
     * Get the notification title.
     *
     * @return string|null The title of the notification or null if not set.
     */
    public function getTitle()
    {
        return $this->notification->title ?? null;
    }

    /**
     * Get the notification icon.
     *
     * @return string|null The icon URL or null if not set.
     */
    public function getIcon()
    {
        return json_decode($this->notification->icon) ?? null;
    }

    /**
     * Get the notification description.
     *
     * @return string|null The description of the notification or null if not set.
     */
    public function getDescription()
    {
        return json_decode($this->notification->description) ?? null;
    }

    /**
     * Get the primary button title.
     *
     * @return string|null The title of the primary button or null if not set.
     */
    public function primaryButtonTitle()
    {
        return $this->notification->primary_button['title'] ?? null;
    }

    /**
     * Get the primary button URL.
     *
     * @return string|null The URL of the primary button or null if not set.
     */
    public function primaryButtonUrl()
    {
        $homeUrl = home_url();
        $url     = isset($this->notification->primary_button['url'])
            ? str_replace('{baseUrl}', $homeUrl, $this->notification->primary_button['url'])
            : null;

        return $url;
    }

    /**
     * Get the secondary button title.
     *
     * @return string|null The title of the secondary button or null if not set.
     */
    public function secondaryButtonTitle()
    {
        return $this->notification->secondary_button['title'] ?? null;
    }

    /**
     * Get the secondary button URL.
     *
     * @return string|null The URL of the secondary button or null if not set.
     */
    public function secondaryButtonUrl()
    {
        $homeUrl = home_url();
        $url     = isset($this->notification->secondary_button['url'])
            ? str_replace('{baseUrl}', $homeUrl, $this->notification->secondary_button['url'])
            : null;
        return $url;
    }

    /**
     * Get the background color of the notification.
     *
     * @return string|null The background color in hex format or null if not set.
     */
    public function backgroundColor()
    {
        $backgroundColors = [
            'inherit' => '',
            'danger'  => 'wps-notification-sidebar__danger',
            'info'    => 'wps-notification-sidebar__info',
            'promo'   => 'wps-notification-sidebar__promo',
            'warning' => 'wps-notification-sidebar__warning',
            'success' => 'wps-notification-sidebar__success'
        ];

        return $backgroundColors[$this->notification->background_color] ?? null;
    }

    /**
     * Get the activation timestamp of the notification.
     *
     * @return string|null The activation timestamp or null if not set.
     */
    public function activatedAt()
    {
        if ($this->notification->activated_at) {
            $timezoneOffset = get_option('gmt_offset') * 3600;

            $timestamp = strtotime($this->notification->activated_at);

            $timestamp += $timezoneOffset;

            $timeDiff = human_time_diff($timestamp, current_time('timestamp'));

            return $timeDiff . ' ' . __('ago', 'wp-statistics');
        }

        return null;
    }

    /**
     * Get the dismiss status or value of the notification.
     *
     * @return mixed|null The dismiss value if set, otherwise null.
     */
    public function getDismiss()
    {
        return $this->notification->dismiss ?? null;
    }

    /**
     * Short text shown on the bell while this notification is unread (optional, from extra_data).
     *
     * @return string|null
     */
    public function getBadge()
    {
        return $this->notification->extra_data['badge'] ?? null;
    }

    /**
     * Coupon code shown with a copy button (optional, from extra_data).
     *
     * @return string|null
     */
    public function getCode()
    {
        return $this->notification->extra_data['code'] ?? null;
    }

    /**
     * End time in UTC (ISO 8601), used for the countdown.
     *
     * @return string|null
     */
    public function getEndsAt()
    {
        return $this->notification->ends_at ?? null;
    }

    /**
     * Whether this notification uses the promo (hero card) style.
     *
     * @return bool
     */
    public function isPromo()
    {
        return ($this->notification->background_color ?? '') === 'promo';
    }
}
