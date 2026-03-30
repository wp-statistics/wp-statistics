<?php

namespace WP_Statistics\Service\CustomEvent;

/**
 * @deprecated Use WP_Statistics\Pro\Modules\EventTracker\EventRecorder instead.
 *
 * Data parsing is now handled internally by EventRecorder::parseData().
 * This class is kept for backward compatibility.
 */
class CustomEventDataParser
{
    protected $eventName;
    protected $eventData;

    public function __construct($eventName, $eventData = [], $visitorProfile = null)
    {
        $this->eventName = $eventName;
        $this->eventData = is_array($eventData) ? $eventData : [];
    }

    /**
     * @deprecated
     * @return array
     */
    public function getParsedData()
    {
        return [
            'event_name' => $this->eventName,
            'visitor_id' => null,
            'page_id'    => null,
            'user_id'    => null,
            'event_data' => $this->eventData,
        ];
    }

    /**
     * @deprecated
     * @return array
     */
    public function getDefaultEventFields()
    {
        return ['visitor_id', 'user_id', 'resource_id'];
    }
}
