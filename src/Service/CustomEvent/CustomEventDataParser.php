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

    /**
     * Whether the event data comes from a trusted server-side caller.
     *
     * Untrusted callers (the public admin-ajax endpoint) may only attribute an
     * event to a publicly-viewable post; a trusted caller (the server-side
     * wp_statistics_event() helper) may attribute it to any existing post.
     *
     * @var bool
     */
    protected $trusted;

    public function __construct($eventName, $eventData = [], $visitorProfile = null, $trusted = false)
    {
        $this->eventName        = $eventName;
        $this->eventData        = is_array($eventData) ? $eventData : [];
        $this->visitorProfile   = $visitorProfile;
        $this->trusted          = (bool) $trusted;

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
                /* translators: %s: string value */
                throw new Exception(sprintf(esc_html__('The value for the key "%s" in the event data is an array. Only strings, numbers and booleans are allowed.', 'wp-statistics'), $key)); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- internal exception, message is not rendered to HTML
            }
        }
    }

    /**
     * Sets the identity fields for the custom event data.
     *
     * The visitor, user and resource an event is attributed to are always
     * derived server-side and never taken from the request payload, so a caller
     * cannot record events on behalf of an arbitrary visitor, WordPress user or
     * post. A client-supplied resource id is only honoured when it references an
     * existing, publicly-viewable post; otherwise the server-detected current
     * resource is used.
     *
     * The following fields are set:
     * - visitor_id: The id of the current visitor.
     * - user_id: The id of the current user.
     * - resource_id: The id of the current resource (e.g. page, post, etc.).
     *
     * @return void
     */
    private function setDefaultFields()
    {
        // Set visitor id (server-derived, overriding any client-supplied value)
        $visitorId = $this->visitorProfile->getVisitorId();
        if ($visitorId) {
            $this->eventData['visitor_id'] = $visitorId;
        } else {
            unset($this->eventData['visitor_id']);
        }

        // Set user id (server-derived, overriding any client-supplied value)
        $userId = $this->visitorProfile->getUserId();
        if ($userId) {
            $this->eventData['user_id'] = $userId;
        } else {
            unset($this->eventData['user_id']);
        }

        // Set resource id (validated client value, or the current resource)
        $resourceId = $this->resolveResourceId();
        if (!empty($resourceId)) {
            $this->eventData['resource_id'] = $resourceId;
        } else {
            unset($this->eventData['resource_id']);
        }
    }

    /**
     * Resolves the resource id for the event.
     *
     * For an untrusted caller a client-supplied resource id is accepted only
     * when it maps to an existing, publicly-viewable post. For a trusted
     * server-side caller it is accepted when it maps to any existing post
     * (including private posts and non-public custom post types). When the
     * supplied id is not acceptable, the server-detected current resource id is
     * used.
     *
     * @return int|null
     */
    private function resolveResourceId()
    {
        if (array_key_exists('resource_id', $this->eventData)) {
            $candidate = absint($this->eventData['resource_id']);

            if ($candidate && $this->isAcceptableResource($candidate)) {
                return $candidate;
            }
        }

        $currentPage = $this->visitorProfile->getCurrentPageType();

        return !empty($currentPage['id']) ? absint($currentPage['id']) : null;
    }

    /**
     * Checks whether the given id is an acceptable resource for this caller.
     *
     * @param int $postId
     * @return bool
     */
    private function isAcceptableResource($postId)
    {
        if ($this->trusted) {
            return (bool) get_post($postId);
        }

        return $this->isPubliclyViewablePost($postId);
    }

    /**
     * Checks whether the given id is an existing, publicly-viewable post.
     *
     * @param int $postId
     * @return bool
     */
    private function isPubliclyViewablePost($postId)
    {
        $post = get_post($postId);

        if (!$post) {
            return false;
        }

        if (function_exists('is_post_publicly_viewable')) {
            return is_post_publicly_viewable($post);
        }

        return get_post_status($post) === 'publish';
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