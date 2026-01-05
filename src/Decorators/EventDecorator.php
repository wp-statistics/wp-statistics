<?php

namespace WP_Statistics\Decorators;

use WP_Statistics\Components\DateTime;
use WP_Statistics\Utils\Query;

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
        return new PostDecorator($this->event->page_id);
    }

    public function getUser()
    {
        return new UserDecorator($this->event->user_id);
    }

    /**
     * Get visitor data for this event.
     *
     * @return VisitorDecorator|null Decorated visitor data or null if not found.
     */
    public function getVisitor()
    {
        if (empty($this->event->visitor_id)) {
            return null;
        }

        $fields = [
            'visitor.ID',
            'visitor.platform',
            'visitor.agent',
            'CAST(`visitor`.`version` AS SIGNED) as version',
            'visitor.model',
            'visitor.device',
            'visitor.location',
            'visitor.user_id',
            'visitor.region',
            'visitor.city',
            'visitor.hits',
            'visitor.last_counter',
            'visitor.referred',
            'visitor.source_channel',
            'visitor.source_name',
            'visitor.ip',
            'visitor.first_page',
            'visitor.first_view',
            'visitor.last_page',
            'visitor.last_view',
            'pages.uri as first_uri',
            'users.display_name',
            'users.user_email',
            'users.user_login',
            'users.user_registered'
        ];

        $result = Query::select($fields)
            ->from('visitor')
            ->where('visitor.ID', '=', $this->event->visitor_id)
            ->join('pages', ['first_page', 'pages.page_id'], [], 'LEFT')
            ->join('users', ['visitor.user_id', 'users.ID'], [], 'LEFT')
            ->decorate(VisitorDecorator::class)
            ->getRow();

        return $result;
    }
}