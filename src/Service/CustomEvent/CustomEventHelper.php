<?php
namespace WP_Statistics\Service\CustomEvent;

class CustomEventHelper
{
    /**
     * Retrieves registered custom events via code, if any.
     * This is a filterable array to allow other plugins to add their own custom events.
     *
     * @return array
     */
    public static function getCustomEvents()
    {
        $result = [];

        // This is a filter to register internal WP Statistics events
        $internalCustomEvents = apply_filters('wp_statistics_internal_custom_events', []);

        // This is a filter to register custom events by other plugins, themes, etc
        $customEvents = apply_filters('wp_statistics_custom_events', []);

        foreach ($customEvents as $event) {
            // Check if the event name is valid (not already defined or is reserved)
            if (!self::isEventNameValid($event['machine_name'])) {
                \WP_Statistics::log(esc_html__("An event with `{$event['machine_name']}` machine name is not allowed.", 'wp-statistics'), 'error');
                continue;
            }

            $result[$event['machine_name']] = $event;
        }

        foreach ($internalCustomEvents as $event) {
            $result[$event['machine_name']] = $event;
        }

        return $result;
    }

    /**
     * Retrieves an array of active custom events.
     *
     * @return string[]
     */
    public static function getActiveCustomEvents()
    {
        $activeCustomEvents = [];

        foreach (self::getCustomEvents() as $event) {
            if (!empty($event['status'])) {
                $activeCustomEvents[] = $event['machine_name'];
            }
        }

        return apply_filters('wp_statistics_active_custom_events', $activeCustomEvents);
    }

    /**
     * Checks if a custom event is active.
     *
     * @param string $name The name of the custom event to check.
     * @return bool True if the custom event is active, false otherwise.
     */
    public static function isEventActive($name)
    {
        return in_array($name, self::getActiveCustomEvents());
    }

    /**
     * Checks if the given name is a reserved name for custom events.
     *
     * @param string $name The name to check.
     * @return bool True if the name is reserved, false otherwise.
     */
    public static function isEventNameReserved($name)
    {
        return in_array($name, self::getReservedEventNames());
    }

    /**
     * Checks if the given name is allowed to be used as a custom event name.
     *
     * The name is allowed if it is not reserved and is not already in use as a custom event name.
     *
     * @param string $name The name to check.
     * @return bool True if the name is allowed, false otherwise.
     */
    public static function isEventNameValid($name)
    {
        $isValid = ! self::isEventNameReserved($name) ? true : false;

        return apply_filters('wp_statistics_custom_event_name_validation', $isValid, $name);
    }

    /**
     * Gets a list of reserved event names that are not allowed to be used for custom events.
     *
     * @return string[]
     */
    public static function getReservedEventNames()
    {
        return [
            'page_view',
            'video_start',
            'custom_event',
            'user_login',
            'session_start',
            'video_progress',
            'analytics',
            'view_search_results',
            'user_engagement',
            'video_complete',
            'reserved_event',
            'user_logout',
            'scroll',
            'add_to_cart',
            'tracking_event',
            'form_submit',
            'click',
            'purchase',
            'system_event',
            'file_download',
            'notification_open',
            'error_event'
        ];
    }
}