<?php

namespace WP_Statistics\Decorators;

use WP_Statistics\Components\DateTime;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Service\Resources\ResourcesFactory;

/**
 * Decorator for a record from the 'views' table.
 *
 * Provides accessor methods for view-related data and linked entities.
 *
 * @since 15.0.0
 */
class ViewDecorator
{
    /**
     * The view record.
     *
     * @var object|null
     */
    private $view;

    /**
     * ViewDecorator constructor.
     *
     * @param object|null $view A stdClass object from 'views' table, or null.
     */
    public function __construct($view)
    {
        $this->view = $view;
    }

    /**
     * Get the view ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->view->ID ?? null;
    }

    /**
     * Get the viewed timestamp.
     *
     * @return string|null
     */
    public function getViewedAt()
    {
        return empty($this->view->viewed_at) ? null : DateTime::convertUtc($this->view->viewed_at);
    }

    /**
     * Get the next view ID.
     *
     * @return int|null
     */
    public function getNextViewId()
    {
        return $this->view->next_view_id ?? null;
    }

    /**
     * Get the duration of this view.
     *
     * @return int|null
     */
    public function getDuration()
    {
        return isset($this->view->duration) ? (int)$this->view->duration : null;
    }

    /**
     * Get the session id of this view.
     *
     * @return int|null
     */
    public function getSessionId()
    {
        return isset($this->view->session_id) ? (int)$this->view->session_id : null;
    }

    /**
     * Get the session of this view.
     *
     * @return SessionDecorator|null
     */
    public function getSession()
    {
        if (empty($this->view->session_id)) {
            return new SessionDecorator(null);
        }

        $record = RecordFactory::session()->get(['ID' => $this->view->session_id]);
        return new SessionDecorator($record);
    }

    /**
     * Get the logical resource ID linked to this view (value of `views.resource_id`).
     *
     * @return int|null The resource ID or null if not available.
     * @since 15.0.0
     */
    public function getResourceId()
    {
       return isset($this->view->resource_id) ? (int)$this->view->resource_id : null;
    }

    /**
     * Resolve and return the resource object for this view.
     *
     * @return ResourceDecorator|null The decorated resource, or null if unavailable.
     * @since 15.0.0
     */
    public function getResource()
    {
        $resourceId = $this->getResourceId();

        if (empty($resourceId)) {
            return null;
        }

        return ResourcesFactory::getResourceById($resourceId);
    }
}
