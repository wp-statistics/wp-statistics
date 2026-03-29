<?php

namespace WP_Statistics\Service\CustomEvent;

/**
 * @deprecated Use WP_Statistics\Pro\Modules\EventTracker\EventRecorder instead.
 *
 * This class is kept for backward compatibility. It no longer registers
 * any hooks or records events. The premium EventRecorder handles all
 * event recording when premium is active.
 */
class CustomEventHandler
{
    public function __construct()
    {
        // No-op: hooks are now registered by premium EventTracker module
    }

    /**
     * @deprecated Use EventRecorder::onBatchEvents()
     * @param array $events
     */
    public function onBatchEvents(array $events): void
    {
        // No-op
    }

    /**
     * @deprecated Use EventRecorder::record()
     * @param string $eventName
     * @param array $eventData
     */
    public function recordEvent($eventName, $eventData = [])
    {
        // No-op
    }
}
