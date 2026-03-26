<?php
namespace WP_Statistics\Service\CustomEvent;

class CustomEventHelper
{
    /**
     * Retrieves registered custom events from all sources.
     *
     * Events are merged from three sources in priority order:
     * 1. Internal (wp_statistics_internal_custom_events) — system events, bypass reserved name check
     * 2. External (wp_statistics_custom_events) — goals + third-party, validated against reserved names
     *
     * Each event is tagged with a 'source' field: 'internal', 'goal', or 'code'.
     * First-registered wins — if a name is already taken, later registrations are skipped.
     *
     * @return array
     */
    public static function getCustomEvents()
    {
        $result = [];

        // Internal events (system — bypass reserved name check)
        $internalCustomEvents = apply_filters('wp_statistics_internal_custom_events', []);

        foreach ($internalCustomEvents as $event) {
            $event['source'] = $event['source'] ?? 'internal';
            $result[$event['machine_name']] = $event;
        }

        // External events (goals + third-party — validated)
        $customEvents = apply_filters('wp_statistics_custom_events', []);

        foreach ($customEvents as $event) {
            // Skip if name already registered by another source
            if (isset($result[$event['machine_name']])) {
                continue;
            }

            // Validate against reserved names
            if (!self::isEventNameValid($event['machine_name'])) {
                \WP_Statistics()->log(esc_html__("An event with `{$event['machine_name']}` machine name is not allowed.", 'wp-statistics'), 'error');
                continue;
            }

            $event['source'] = $event['source'] ?? 'code';
            $result[$event['machine_name']] = $event;
        }

        return $result;
    }

    /**
     * Retrieves an array of active custom event names.
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
     * @param string $name The event name to check.
     * @return bool
     */
    public static function isEventActive($name)
    {
        return in_array($name, self::getActiveCustomEvents());
    }

    /**
     * Find an event by name across all registered sources.
     *
     * @param string $name The event name to find.
     * @return array|null The event data with source tag, or null if not found.
     */
    public static function findEventByName(string $name): ?array
    {
        $events = self::getCustomEvents();

        return $events[$name] ?? null;
    }

    /**
     * Validate an event name for registration.
     *
     * Checks:
     * - Not empty
     * - Not reserved
     * - Not already registered by another source
     *
     * @param string $name The event name to validate.
     * @param string|null $excludeSource Skip conflict check for this source (e.g., 'goal' when editing a goal).
     * @return array{valid: bool, reason?: string}
     */
    public static function validateEventName(string $name, ?string $excludeSource = null): array
    {
        if (empty($name)) {
            return ['valid' => false, 'reason' => esc_html__('Event name is required.', 'wp-statistics')];
        }

        if (self::isEventNameReserved($name)) {
            return ['valid' => false, 'reason' => esc_html__('This event name is reserved by the system.', 'wp-statistics')];
        }

        $existing = self::findEventByName($name);
        if ($existing && (!$excludeSource || $existing['source'] !== $excludeSource)) {
            return ['valid' => false, 'reason' => esc_html__('This event name is already registered.', 'wp-statistics')];
        }

        return ['valid' => true];
    }

    /**
     * Checks if the given name is a reserved name for custom events.
     *
     * @param string $name The name to check.
     * @return bool
     */
    public static function isEventNameReserved($name)
    {
        return in_array($name, self::getReservedEventNames());
    }

    /**
     * Checks if the given name is allowed to be used as a custom event name.
     *
     * @param string $name The name to check.
     * @return bool
     */
    public static function isEventNameValid($name)
    {
        $isValid = !self::isEventNameReserved($name);

        return apply_filters('wp_statistics_custom_event_name_validation', $isValid, $name);
    }

    /**
     * Gets a list of reserved event names that cannot be used for custom events.
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
