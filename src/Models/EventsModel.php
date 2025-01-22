<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Utils\Query;

class EventsModel extends BaseModel
{
    public function countEvents($args = [])
    {
        $args = $this->parseArgs($args, [
            'event_name'=> '',
            'author_id' => '',
            'post_type' => '',
            'post_id'   => '',
            'date'      => '',
            'group_by'  => '',
        ]);

        $query = Query::select('COUNT(*)')
            ->from('events')
            ->where('event_name', 'IN', $args['event_name'])
            ->where('events.page_id', '=', $args['post_id'])
            ->whereDate('events.date', $args['date'])
            ->groupBy($args['group_by']);

        if (!empty($args['author_id']) || !empty($args['post_type']) || !empty($args['post_id'])) {
            $query
                ->join('posts', ['events.page_id', 'posts.ID'])
                ->where('posts.post_type', '=', $args['post_type'])
                ->where('posts.post_author', '=', $args['author_id']);
        }

        $result = $query->getVar();

        return $result;
    }

    public function getEvents($args = [])
    {
        $args = $this->parseArgs($args, [
            'page'          => 1,
            'per_page'      => Admin_Template::$item_per_page,
            'event_name'    => '',
            'author_id'     => '',
            'post_type'     => '',
            'post_id'       => '',
            'date'          => '',
            'decorator'     => '',
            'order'         => 'date',
            'order_by'      => 'DESC',
        ]);

        $query = Query::select('*')
            ->from('events')
            ->where('event_name', 'IN', $args['event_name'])
            ->where('events.page_id', '=', $args['post_id'])
            ->whereDate('events.date', $args['date'])
            ->orderBy($args['order'], $args['order_by'])
            ->perPage($args['page'], $args['per_page'])
            ->decorate($args['decorator']);

        if (!empty($args['author_id']) || !empty($args['post_type']) || !empty($args['post_id'])) {
            $query
                ->join('posts', ['events.page_id', 'posts.ID'])
                ->where('posts.post_type', '=', $args['post_type'])
                ->where('posts.post_author', '=', $args['author_id']);
        }

        return $query->getAll();
    }

    public function getTopEvents($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_id'   => '',
            'post_type' => '',
            'author_id' => '',
            'page'      => 1,
            'per_page'  => Admin_Template::$item_per_page,
            'event_name'=> '',
            'decorator' => ''
        ]);

        $query = Query::select([
                "JSON_UNQUOTE(JSON_EXTRACT(`event_data`, '$.target_url')) AS url",
                "COUNT(*) AS count"
            ])
            ->from('events')
            ->where('event_name', 'IN', $args['event_name'])
            ->where('events.page_id', '=', $args['post_id'])
            ->whereDate('events.date', $args['date'])
            ->orderBy('count', 'DESC')
            ->perPage($args['page'], $args['per_page'])
            ->groupBy('url')
            ->decorate($args['decorator']);

        if (!empty($args['author_id']) || !empty($args['post_type']) || !empty($args['post_id'])) {
            $query
                ->join('posts', ['events.page_id', 'posts.ID'])
                ->where('posts.post_type', '=', $args['post_type'])
                ->where('posts.post_author', '=', $args['author_id']);
        }

        return $query->getAll();
    }

    public function countTopEvents($args = [])
    {
        $args = $this->parseArgs($args, [
            'event_name'    => '',
            'author_id'     => '',
            'post_type'     => '',
            'post_id'       => '',
            'date'          => '',
        ]);

        $subQuery = Query::select("JSON_UNQUOTE(JSON_EXTRACT(`event_data`, '$.target_url')) AS url")
            ->from('events')
            ->where('event_name', 'IN', $args['event_name'])
            ->where('events.page_id', '=', $args['post_id'])
            ->whereDate('events.date', $args['date'])
            ->groupBy('url');

        if (!empty($args['author_id']) || !empty($args['post_type']) || !empty($args['post_id'])) {
            $subQuery
                ->join('posts', ['events.page_id', 'posts.ID'])
                ->where('posts.post_type', '=', $args['post_type'])
                ->where('posts.post_author', '=', $args['author_id']);
        }

        $result = Query::select('COUNT(url)')
            ->fromQuery($subQuery->getQuery())
            ->getVar();

        return $result;
    }

    public function insertEvent($args)
    {
        $data = [
            'date'       => DateTime::get('now', 'Y-m-d H:i:s'),
            'page_id'    => $args['page_id'],
            'visitor_id' => $args['visitor_id'],
            'event_name' => $args['event_name'],
            'event_data' => json_encode($args['event_data'])
        ];

        $result = Query::insert('events')
            ->set($data)
            ->execute();

        return $result;
    }
}