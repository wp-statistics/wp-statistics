<?php
namespace WP_Statistics\Service\CustomEvent;

use Exception;
use WP_Statistics\Service\Analytics\VisitorProfile;

class CustomEventDataParser
{
    protected $eventName;
    protected $eventData;
    protected $eventFields;
    protected $visitorProfile;

    public function __construct($eventName, $eventData = [], $visitorProfile = null)
    {
        $this->eventName        = $eventName;
        $this->eventData        = is_array($eventData) ? $eventData : [];
        $this->visitorProfile   = $visitorProfile;

        if (!$visitorProfile) {
            $this->visitorProfile = new VisitorProfile();
        }
    }

    /**
     * Parses the given event data and returns the data in the format expected by the
     * `WP_Statistics\Models\EventsModel::insertEvent()` method.
     *
     * @return array The parsed event data.
     */
    public function getParsedData()
    {
        $this->setDefaultFields();
        $this->validateData();

        return [
            'event_name'    => $this->eventName,
            'visitor_id'    => $this->eventData['visitor_id'] ?? null,
            'page_id'       => $this->eventData['resource_id'] ?? null,
            'user_id'       => $this->eventData['user_id'] ?? null,
            'event_data'    => array_diff_key($this->eventData, array_flip($this->getDefaultEventFields()))
        ];
    }

    /**
     * Validates the custom event data.
     *
     * @throws \Exception if the event name is empty, reserved, or the event data contains an array.
     *
     * @return void
     */
    private function validateData()
    {
        // Get excluded events that should not be allowed
        $excludedEvents = apply_filters('wp_statistics_excluded_custom_events', []);

        // Check if the event name is not empty, or is excluded
        if (empty($this->eventName) || in_array($this->eventName, $excludedEvents)) {
            throw new Exception(esc_html__('The event name you entered is not valid.', 'wp-statistics'));
        }

        // Check if the event name is active and exists
        if (!CustomEventHelper::isEventActive($this->eventName)) {
            throw new Exception(esc_html__('The event does not exist, or is not active.', 'wp-statistics'));
        }

        // Check if the event data is not empty
        if (empty($this->eventData)) {
            throw new Exception(esc_html__('The event data is not valid.', 'wp-statistics'));
        }

        // Validate event data types
        foreach ($this->eventData as $key => $value) {
            if (is_array($value)) {
                throw new Exception(sprintf(__('The value for the key "%s" in the event data is an array. Only strings, numbers and booleans are allowed.', 'wp-statistics'), $key));
            }
        }
    }

    /**
     * Sets default fields for the custom event data if they are not already present.
     *
     * The following fields are set if they are not already present:
     * - visitor_id: The id of the current visitor.
     * - user_id: The id of the current user.
     * - resource_id: The id of the current resource (e.g. page, post, etc.).
     *
     * @return void
     */
    private function setDefaultFields()
    {
        // Set visitor id
        if (!array_key_exists('visitor_id', $this->eventData)) {
            $visitorId = $this->visitorProfile->getVisitorId();

            if ($visitorId) {
                $this->eventData['visitor_id'] = $visitorId;
            }
        }

        // Set user id
        if (!array_key_exists('user_id', $this->eventData)) {
            $userId = $this->visitorProfile->getUserId();

            if ($userId) {
                $this->eventData['user_id'] = $userId;
            }
        }

        // Set resource id
        if (!array_key_exists('resource_id', $this->eventData)) {
            $resourceId = $this->visitorProfile->getCurrentPageType();

            if (!empty($resourceId['id'])) {
                $this->eventData['resource_id'] = $resourceId['id'];
            }
        }
    }

    /**
     * Returns an associative array of default event fields.
     *
     * @return array An associative array of default event fields.
     */
    public function getDefaultEventFields()
    {
        return ['visitor_id', 'user_id', 'resource_id'];
    }
}