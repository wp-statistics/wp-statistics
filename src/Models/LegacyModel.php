<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Utils\Query;

class LegacyModel extends BaseModel
{
    public static function get($method, $args, $version)
    {
        $instance = new self();

        $version = str_replace('.', '_', $version);
        $method  = ucfirst($method);

        return $instance->{"get{$method}_{$version}"}($args);
    }

    protected function getReferredVisitors_14_12_4($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'              => '',
            'source_channel'    => '',
            'source_name'       => '',
            'referrer'          => '',
            'order_by'          => 'visitor.ID',
            'order'             => 'desc',
            'page'              => '',
            'per_page'          => '',
        ]);

        $firstHit = Query::select([
            'MIN(ID) as ID',
            'visitor_id'
        ])
            ->from('visitor_relationships')
            ->groupBy('visitor_id')
            ->getQuery();

        $firstHitQuery = Query::select([
            'visitor_relationships.visitor_id',
            'page_id',
            'date'
        ])
            ->from('visitor_relationships')
            ->whereRaw("(ID, visitor_id) IN ($firstHit)")
            ->groupBy('visitor_id')
            ->getQuery();

        $lastHit = Query::select([
            'visitor_id',
            'MAX(date) as date'
        ])
            ->from('visitor_relationships')
            ->groupBy('visitor_id')
            ->getQuery();

        $lastHitQuery = Query::select([
            'visitor_relationships.visitor_id',
            'page_id',
            'date'
        ])
            ->from('visitor_relationships')
            ->whereRaw("(visitor_id, date) IN ($lastHit)")
            ->groupBy('visitor_id')
            ->getQuery();

        $query = Query::select([
            'visitor.ID',
            'visitor.ip',
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
            'visitor.referred',
            'visitor.last_counter',
            'visitor.source_channel',
            'visitor.source_name',
            'users.display_name',
            'users.user_email',
            'first_hit.page_id as first_page',
            'first_hit.date as first_view',
            'last_hit.page_id as last_page',
            'last_hit.date as last_view'
        ])
            ->from('visitor')
            ->join('users', ['visitor.user_id', 'users.ID'], [], 'LEFT')
            ->joinQuery($firstHitQuery, ['visitor.ID', 'first_hit.visitor_id'], 'first_hit', 'LEFT')
            ->joinQuery($lastHitQuery, ['visitor.ID', 'last_hit.visitor_id'], 'last_hit', 'LEFT')
            ->where('source_name', '=', $args['source_name'])
            ->where('referred', '=', $args['referrer'])
            ->whereNotNull('visitor.referred')
            ->whereDate('visitor.last_counter', $args['date'])
            ->perPage($args['page'], $args['per_page'])
            ->orderBy($args['order_by'], $args['order'])
            ->decorate(VisitorDecorator::class);

        // When source_channel is `unassigned`, only get visitors without source_channel
        if ($args['source_channel'] === 'unassigned') {
            $query
                ->whereNull('visitor.source_channel');
        } else {
            $query
                ->where('source_channel', '=', $args['source_channel']);
        }

        $result = $query->getAll();

        return $result ?? [];
    }


    protected function getVisitorData_14_12_4($args = [])
    {
        $args = $this->parseArgs($args, [
            'fields'     => [],
            'visitor_id' => '',
            'ip'         => '', // not recommended to get visitor data by ip, it's less efficient
            'decorate'   => true,
            'page_info'  => true,
            'user_info'  => true
        ]);

        $fields = !empty($args['fields']) && is_array($args['fields']) ? $args['fields'] : [
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
            'visitor.ip'
        ];

        // If visitor_id is empty, get visitor_id by IP
        if (empty($args['visitor_id']) || !empty($args['ip'])) {
            $visitorId = Query::select(['ID'])
                ->from('visitor')
                ->where('ip', '=', $args['ip'])
                ->getVar();

            $args['visitor_id'] = $visitorId ?? '';
        }

        if ($args['page_info'])  {
            $firstPage = Query::select(['MIN(ID)', 'page_id', 'visitor_id'])
                ->from('visitor_relationships')
                ->where('visitor_id', '=', $args['visitor_id'])
                ->getQuery();

            $firstView = Query::select(['MIN(date) as date', 'visitor_id'])
                ->from('visitor_relationships')
                ->where('visitor_id', '=', $args['visitor_id'])
                ->getQuery();

            $fields[] = 'first_view.date as first_view';
            $fields[] = 'first_page.page_id as first_page';
            $fields[] = 'pages.uri as first_uri';
        }

        if ($args['user_info']) {
            $fields[] = 'users.display_name';
            $fields[] = 'users.user_email';
            $fields[] = 'users.user_login';
            $fields[] = 'users.user_registered';
        }

        $query = Query::select($fields)
            ->from('visitor')
            ->where('visitor.ID', '=', $args['visitor_id']);

        if ($args['page_info']) {
            $query
                ->joinQuery($firstPage, ['visitor.ID', 'first_page.visitor_id'], 'first_page', 'LEFT')
                ->joinQuery($firstView, ['visitor.ID', 'first_view.visitor_id'], 'first_view', 'LEFT')
                ->join('pages', ['first_page.page_id', 'pages.page_id'], [], 'LEFT');
        }

        if ($args['user_info']) {
            $query
                ->join('users', ['visitor.user_id', 'users.ID'], [], 'LEFT');
        }

        if ($args['decorate']) {
            $query
                ->decorate(VisitorDecorator::class);
        }

        return $query->getRow();
    }

    protected function getVisitorsData_14_12_4($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'resource_type' => '',
            'resource_id'   => '',
            'post_type'     => '',
            'author_id'     => '',
            'post_id'       => '',
            'country'       => '',
            'agent'         => '',
            'platform'      => '',
            'user_id'       => '',
            'ip'            => '',
            'query_param'   => '',
            'taxonomy'      => '',
            'term'          => '',
            'order_by'      => 'visitor.ID',
            'order'         => 'DESC',
            'page'          => '',
            'per_page'      => '',
            'page_info'     => true,
            'user_info'     => false,
            'date_field'    => 'visitor.last_counter',
            'logged_in'     => false,
            'user_role'     => '',
            'fields'        => []
        ]);

        // Set default fields
        if (empty($args['fields'])) {
            $args['fields'] = [
                'visitor.ID',
                'visitor.ip',
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
                'visitor.referred',
                'visitor.last_counter',
                'visitor.source_channel',
                'visitor.source_name',
            ];
        }

        // If page info is true, get last page the visitor has visited
        if ($args['page_info'] === true) {

            $lastHit = Query::select([
                'visitor_id',
                'MAX(date) as date'
            ])
                ->from('visitor_relationships')
                ->groupBy('visitor_id')
                ->getQuery();

            $subQuery = Query::select([
                'visitor_relationships.visitor_id',
                'page_id',
                'date'
            ])
                ->from('visitor_relationships')
                ->whereRaw("(visitor_id, date) IN ($lastHit)")
                ->groupBy('visitor_id')
                ->getQuery();

            $args['fields'][] = 'last_hit.page_id as last_page';
            $args['fields'][] = 'last_hit.date as last_view';
        }

        if ($args['user_info'] === true) {
            $args['fields'][] = 'users.display_name';
            $args['fields'][] = 'users.user_email';
        }

        // When retrieving data for a single resource, get the page view date
        if (!empty($args['resource_id']) && ($args['resource_type'])) {
            $args['fields'][] = 'visitor_relationships.date as page_view';
        }

        $query = Query::select($args['fields'])
            ->from('visitor')
            ->where('agent', '=', $args['agent'])
            ->where('platform', '=', $args['platform'])
            ->where('user_id', '=', $args['user_id'])
            ->where('ip', 'LIKE', "%{$args['ip']}%")
            ->where('visitor.location', '=', $args['country'])
            ->whereDate($args['date_field'], $args['date'])
            ->perPage($args['page'], $args['per_page'])
            ->orderBy($args['order_by'], $args['order'])
            ->decorate(VisitorDecorator::class)
            ->groupBy('visitor.ID');

        if ($args['logged_in'] === true) {
            $query->where('visitor.user_id', '!=', 0);
            $query->whereNotNull('visitor.user_id');

            if (!empty($args['user_role'])) {
                $query->join('usermeta', ['visitor.user_id', 'usermeta.user_id']);
                $query->where('usermeta.meta_key', '=', "wp_capabilities");
                $query->where('usermeta.meta_value', 'LIKE', "%{$args['user_role']}%");
            }
        }

        // If last page is true, get last page the visitor has visited
        if ($args['page_info'] === true) {
            $query->joinQuery($subQuery, ['visitor.ID', 'last_hit.visitor_id'], 'last_hit', 'LEFT');
        }

        if ($args['user_info']) {
            $query->join('users', ['visitor.user_id', 'users.ID'], [], 'LEFT');
        }

        $filteredArgs = array_filter($args);

        if (array_intersect(['resource_type', 'resource_id', 'query_param'], array_keys($filteredArgs))) {
            $query
                ->join('visitor_relationships', ['visitor_relationships.visitor_id', 'visitor.ID'])
                ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
                ->where('pages.type', 'IN', $args['resource_type'])
                ->where('pages.id', '=', $args['resource_id'])
                ->where('pages.uri', '=', $args['query_param']);
        }

        if (array_intersect(['post_type', 'post_id', 'query_param', 'taxonomy', 'term'], array_keys($filteredArgs))) {
            $query
                ->join('visitor_relationships', ['visitor_relationships.visitor_id', 'visitor.ID'])
                ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
                ->join('posts', ['posts.ID', 'pages.id'], [], 'LEFT')
                ->where('post_type', 'IN', $args['post_type'])
                ->where('post_author', '=', $args['author_id'])
                ->where('posts.ID', '=', $args['post_id']);

            if (array_intersect(['taxonomy', 'term'], array_keys($filteredArgs))) {
                $taxQuery = Query::select(['DISTINCT object_id'])
                    ->from('term_relationships')
                    ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
                    ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
                    ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
                    ->where('terms.term_id', '=', $args['term'])
                    ->getQuery();

                $query
                    ->joinQuery($taxQuery, ['posts.ID', 'tax.object_id'], 'tax');
            }
        }

        $result = $query->getAll();

        return $result ? $result : [];
    }
}
