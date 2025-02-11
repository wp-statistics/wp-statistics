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
        return $this->notification->icon ?? null;
    }

    /**
     * Get the notification description.
     *
     * @return string|null The description of the notification or null if not set.
     */
    public function getDescription()
    {
        return $this->notification->description ?? null;
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
        return $this->notification->primary_button['url'] ?? null;
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
        return $this->notification->secondary_button['url'] ?? null;
    }

    /**
     * Get the background color of the notification.
     *
     * @return string|null The background color in hex format or null if not set.
     */
    public function backgroundColor()
    {
        return $this->notification->background_color ?? null;
    }

    /**
     * Get the activation timestamp of the notification.
     *
     * @return string|null The activation timestamp or null if not set.
     */
    public function activatedAt()
    {
        if ($this->notification->activated_at) {
            $timestamp = strtotime($this->notification->activated_at);
            $timeDiff  = human_time_diff($timestamp, current_time('timestamp'));

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
}