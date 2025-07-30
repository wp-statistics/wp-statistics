<?php

namespace WP_Statistics\Decorators;

use WP_Statistics\Components\DateTime;
use WP_Statistics\Decorators\ResourceDecorator;
use WP_Statistics\Decorators\UserDecorator;
use WP_Statistics\Models\VisitorsModel;

class EventDecorator
{
    protected $event;
    protected $eventData;

    public function __construct($event)
    {
        $this->event     = $event;
        $this->eventData = json_decode($this->event->event_data, true);
    }

    public function getDate()
    {
        return DateTime::format($this->event->date, ['include_time' => true]);
    }

    public function getName()
    {
        return $this->event->event_name;
    }

    public function getData()
    {
        return $this->eventData;
    }

    public function getPage()
    {
        return new ResourceDecorator($this->event->page_id);
    }

    public function getUser()
    {
        return new UserDecorator($this->event->user_id);
    }

    public function getVisitor()
    {
        $visitorsModel = new VisitorsModel();
        return $visitorsModel->getVisitorData(['visitor_id' => $this->event->visitor_id]);
    }
}