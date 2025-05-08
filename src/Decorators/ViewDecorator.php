<?php

namespace WP_Statistics\Decorators;

use WP_Statistics\Records\RecordFactory;

/**
 * Decorator for a record from the 'views' table.
 *
 * Provides accessor methods for view-related data and linked entities.
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
        return $this->view->viewed_at ?? null;
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
     * Get the resource associated with this view.
     *
     * @return ResourceDecorator|null
     */
    public function getResource()
    {
        if (empty($this->view->resource_id)) {
            return new ResourceDecorator(null);
        }

        $record = RecordFactory::resource()->get(['ID' => $this->view->resource_id]);
        return new ResourceDecorator($record);
    }
}
