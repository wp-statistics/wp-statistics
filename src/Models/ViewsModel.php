<?php

namespace WP_Statistics\Models;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Query;
use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Decorators\VisitorDecorator;

class ViewsModel extends BaseModel
{
    public function countViews($args = [])
    {
        $args = $this->parseArgs($args, [
            'resource_id'       => '',
            'resource_type'     => '',
            'date'              => '',
            'author_id'         => '',
            'query_param'       => '',
            'taxonomy'          => '',
            'term'              => '',

            // Keep for backward-compatibility
            'post_type'         => Helper::get_list_post_type(),
            'post_id'           => '',
        ]);

        // Map post_id to resource_id for backward compatibility
        if (!empty($args['post_id'])) {
            $args['resource_id'] = $args['post_id'];
        }

        // Map post_type to resource_type for backward compatibility (only if query_param is not set, since it's unique)
        if (empty($args['resource_type']) && empty($args['query_param'])) {
            $args['resource_type'] = $args['post_type'];
        }

        $query = Query::select('SUM(pages.count) as total_views')
            ->from('pages')
            ->where('pages.id', '=', $args['resource_id'])
            ->where('pages.type', 'IN', $args['resource_type'])
            ->where('pages.uri', '=', $args['query_param'])
            ->whereDate('date', $args['date']);

        if (!empty($args['author_id']) || !empty($args['taxonomy']) || !empty($args['term'])) {
            $query
                ->join('posts', ['pages.id', 'posts.ID'])
                ->where('post_author', '=', $args['author_id']);

            $this->joinTermFilter($query, $args);
        }

        $total = $query->getVar();
        $total = $total ? intval($total) : 0;

        $total += $this->historicalModel->getViews($args);

        return $total;
    }

    /**
     * Returns views from `pages` table without joining with other tables.
     *
     * Used for calculating taxonomies views (Unlike `countViews()` which is suited for calculating posts/pages/cpt views).
     *
     * @param   array   $args           Arguments to include in query (e.g. `post_id`, `resource_type`, `query_param`, `date`, etc.).
     *
     * @return  int
     */
    public function countViewsFromPagesOnly($args = [])
    {
        $args = $this->parseArgs($args, [
            'post_id'       => '',
            'resource_type' => '',
            'query_param'   => '',
            'date'          => '',
        ]);

        $query = Query::select(['SUM(`count`) AS `count`'])
            ->from('pages')
            ->where('pages.id', '=', $args['post_id'])
            ->where('pages.type', 'IN', $args['resource_type'])
            ->where('pages.uri', '=', $args['query_param'])
            ->whereDate('date', $args['date']);

        if (is_numeric($args['post_id'])) {
            $query->groupBy('id');
        }

        $total = $query->getVar();
        $total = $total ? intval($total) : 0;

        $total += $this->historicalModel->getViews($args);

        return $total;
    }

    public function countDailyViews($args = [])
    {
        $args = $this->parseArgs($args, [
            'resource_type'     => '',
            'resource_id'       => '',
            'date'              => '',
            'author_id'         => '',
            'query_param'       => '',
            'taxonomy'          => '',
            'term'              => '',

            // Keep for backward-compatibility
            'post_id'           => '',
            'post_type'         => Helper::get_list_post_type(),
        ]);

        // Map post_id to resource_id for backward compatibility
        if (!empty($args['post_id'])) {
            $args['resource_id'] = $args['post_id'];
        }

        // Map post_type to resource_type for backward compatibility (only if query_param is not set, since it's unique)
        if (empty($args['resource_type']) && empty($args['query_param'])) {
            $args['resource_type'] = $args['post_type'];
        }

        $query = Query::select([
            'SUM(pages.count) as views',
            'pages.date as date',
        ])
            ->from('pages')
            ->where('pages.type', 'IN', $args['resource_type'])
            ->where('pages.id', '=', $args['resource_id'])
            ->where('pages.uri', '=', $args['query_param'])
            ->whereDate('pages.date', $args['date'])
            ->groupBy('pages.date');

        if (!empty($args['author_id']) || !empty($args['taxonomy']) || !empty($args['term'])) {
            $query
                ->join('posts', ['pages.id', 'posts.ID'])
                ->where('post_author', '=', $args['author_id'])
                ->where('posts.ID', '=', $args['post_id'])
                ->where('post_type', 'IN', $args['post_type']);

            $this->joinTermFilter($query, $args);
        }

        $result = $query->getAll();

        return $result ?? [];
    }

    public function getViewsData($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'page'      => 1,
            'per_page'  => 20
        ]);

        $result = Query::select([
                'visitor.ID',
                'visitor.ip',
                'visitor.platform',
                'visitor.agent',
                'version',
                'visitor.model',
                'visitor.device',
                'visitor.region',
                'visitor.city',
                'visitor.location',
                'visitor.hits',
                'visitor.user_id',
                'visitor.referred',
                'visitor.source_channel',
                'visitor.source_name',
                'visitor_relationships.page_id as last_page',
                'visitor_relationships.date as last_view',
            ])
            ->from('visitor_relationships')
            ->join('visitor', ['visitor_relationships.visitor_id', 'visitor.ID'])
            ->whereDate('visitor_relationships.date', $args['date'])
            ->decorate(VisitorDecorator::class)
            ->orderBy('visitor_relationships.date')
            ->perPage($args['page'], $args['per_page'])
            ->getAll();

        return $result;
    }

    public function countViewRecords($args = [])
    {
        $args = $this->parseArgs($args, [
            'date' => '',
        ]);

        $result = Query::select([
                'COUNT(*)',
            ])
            ->from('visitor_relationships')
            ->whereDate('visitor_relationships.date', $args['date'])
            ->getVar();

        return $result;
    }

    public function getHourlyViews($args = [])
    {
        $args = $this->parseArgs($args, [
            'date' => ''
        ]);

        $result = Query::select([
            'HOUR(date) as hour',
            'COUNT(DISTINCT visitor_id) as visitors',
            'COUNT(*) as views'
            ])
            ->from('visitor_relationships')
            ->whereDate('visitor_relationships.date', $args['date'])
            ->groupBy('hour')
            ->getAll();

        return $result;
    }

    /**
     * @deprecated Use SummaryChartDataProvider instead.
     */
    public function getViewsSummary($args = [])
    {
        $summary = [
            'today'      => [
                'label'     => esc_html__('Today', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['date' => DateRange::get('today')]))
            ],
            'yesterday'  => [
                'label'     => esc_html__('Yesterday', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['date' => DateRange::get('yesterday')]))
            ],
            'this_week'  => [
                'label'     => esc_html__('This week', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['date' => DateRange::get('this_week')]))
            ],
            'last_week'  => [
                'label'     => esc_html__('Last week', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['date' => DateRange::get('last_week')]))
            ],
            'this_month' => [
                'label'     => esc_html__('This month', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['date' => DateRange::get('this_month')]))
            ],
            'last_month' => [
                'label'     => esc_html__('Last month', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['date' => DateRange::get('last_month')]))
            ],
            '7days'      => [
                'label'     => esc_html__('Last 7 days', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['date' => DateRange::get('7days')]))
            ],
            '30days'     => [
                'label'     => esc_html__('Last 30 days', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['date' => DateRange::get('30days')]))
            ],
            '90days'     => [
                'label'     => esc_html__('Last 90 days', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['date' => DateRange::get('90days')]))
            ],
            '6months'    => [
                'label'     => esc_html__('Last 6 months', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['date' => DateRange::get('6months')]))
            ],
            'this_year'  => [
                'label'     => esc_html__('This year (Jan-Today)', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['date' => DateRange::get('this_year')]))
            ]
        ];

        if (!empty($args['include_total'])) {
            $summary['total'] = [
                'label'     => esc_html__('Total', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['ignore_date' => true, 'historical' => true]))
            ];
        }

        return $summary;
    }

    public function getViewedPageUri($args = [])
    {
        $args = $this->parseArgs($args, [
            'id' => '',
        ]);

        $results = Query::select([
            'uri',
            'page_id',
            'SUM(count) AS total',
        ])
            ->from('pages')
            ->where('id', '=', $args['id'])
            ->groupBy('uri')
            ->orderBy('total')
            ->getAll();

        return $results;
    }

    public function getResourcesViews($args = [])
    {
        $args = $this->parseArgs($args, [
            'fields'        => ['id', 'uri', 'type', 'SUM(count) as views'],
            'resource_id'   => '',
            'resource_type' => '',
            'date'          => '',
            'group_by'      => 'id',
            'not_null'      => '',
            'order_by'      => 'views',
            'page'          => 1,
            'per_page'      => 10
        ]);

        // If resource_id and resource_type are empty, get all views including 404, categories, home, etc...
        if (empty($args['resource_id']) && empty($args['resource_type'])) {
            $queries = [];

            $queries[] = Query::select($args['fields'])
                ->from('pages')
                ->where('id', '!=', '0')
                ->whereDate('date', $args['date'])
                ->groupBy('id');

            $queries[] = Query::select($args['fields'])
                ->from('pages')
                ->where('id', '=', '0')
                ->whereDate('date', $args['date'])
                ->groupBy(['uri', 'type']);

            $results = Query::union($queries)
                ->perPage($args['page'], $args['per_page'])
                ->orderBy('views', 'DESC')
                ->getAll();
        } else {
            $results = Query::select($args['fields'])
                ->from('pages')
                ->where('id', '=', $args['resource_id'])
                ->where('type', 'IN', $args['resource_type'])
                ->whereDate('date', $args['date'])
                ->whereNotNull($args['not_null'])
                ->orderBy($args['order_by'])
                ->perPage($args['page'], $args['per_page'])
                ->groupBy($args['group_by'])
                ->getAll();
        }

        return $results;
    }

    public function getPageRecord($args = [])
    {
        $args = $this->parseArgs($args, [
            'fields'        => '*',
            'page_id'       => '',
            'ignore_date'   => true
        ]);

        $result = Query::select('*')
            ->from('pages')
            ->where('page_id', '=', $args['page_id'])
            ->allowCaching()
            ->getRow();

        return $result;
    }

    public function countPagesRecords($args = [])
    {
        $args = $this->parseArgs($args, [
            'resource_id'   => '',
            'resource_type' => '',
            'date'          => '',
            'not_null'      => '',
        ]);

        $result = Query::select('COUNT(*)')
            ->from('pages')
            ->where('id', '=', $args['resource_id'])
            ->where('type', 'IN', $args['resource_type'])
            ->whereDate('date', $args['date'])
            ->whereNotNull($args['not_null'])
            ->getVar();

        return $result;
    }

    /**
     * Where readers of one post went next: the page each visitor viewed right after it.
     *
     * Two cheap steps instead of a self-join:
     *  1. This post's views in the date range (visitor_id + date), newest first, capped.
     *  2. All views of those visitors in the same range, in one query.
     * The next view of a post view is the following row of the same visitor by date (then ID).
     *
     * @param array $args {
     *     @type int          $resource_id   Post ID.
     *     @type string|array $resource_type Post types (pages.type values are resolved from them).
     *     @type array|string $date          Date range, defaults to the page's range.
     *     @type int          $per_page      How many next pages to return. Default 3.
     *     @type int          $max_views     Cap on this post's views read in step 1. Default 5000.
     * }
     * @return array{readers:int, left:int, left_rate:float, pages:array} `pages` holds objects with
     *               page_id (a pages row of that page, for its title and links) and visitors, most visitors first.
     */
    public function getNextPages($args = [])
    {
        global $wpdb;

        $args = $this->parseArgs($args, [
            'resource_id'   => '',
            'resource_type' => '',
            'date'          => '',
            'per_page'      => 3,
            'max_views'     => 5000,
        ]);

        $result = ['readers' => 0, 'left' => 0, 'left_rate' => 0.0, 'pages' => []];
        $postId = intval($args['resource_id']);

        if ($postId <= 0) {
            return $result;
        }

        $date = is_string($args['date']) ? DateRange::get($args['date']) : $args['date'];
        $from = !empty($date['from']) ? $date['from'] : '';
        $to   = !empty($date['to']) ? $date['to'] : '';
        if (strlen($from) === 10) $from .= ' 00:00:00';
        if (strlen($to) === 10) $to .= ' 23:59:59';

        if ($from === '' || $to === '') {
            return $result;
        }

        $relationships = \WP_STATISTICS\DB::table('visitor_relationships');
        $pages         = \WP_STATISTICS\DB::table('pages');

        $typeSql = '';
        $values  = [$postId];
        if (!empty($args['resource_type'])) {
            $types   = (array)$args['resource_type'];
            $typeSql = ' AND p.type IN (' . implode(',', array_fill(0, count($types), '%s')) . ')';
            $values  = array_merge($values, $types);
        }
        $values[] = $from;
        $values[] = $to;
        $values[] = max(1, intval($args['max_views']));

        // Step 1: this post's views in the range (uses pages.id, then visitor_relationships.page_id)
        $postViews = $wpdb->get_results($wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            "SELECT vr.ID, vr.visitor_id FROM {$relationships} vr INNER JOIN {$pages} p ON p.page_id = vr.page_id WHERE p.id = %d{$typeSql} AND vr.date BETWEEN %s AND %s ORDER BY vr.date DESC LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $values
        ));

        if (empty($postViews)) {
            return $result;
        }

        $visitorIds = array_values(array_unique(array_map(function ($row) {
            return intval($row->visitor_id);
        }, $postViews)));

        // Step 2: every view of those visitors in the range (uses visitor_relationships.visitor_id)
        $views = $wpdb->get_results($wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            "SELECT vr.ID, vr.visitor_id, vr.page_id, vr.date, p.id AS resource_id, p.type, p.uri FROM {$relationships} vr INNER JOIN {$pages} p ON p.page_id = vr.page_id WHERE vr.visitor_id IN (" . implode(',', array_fill(0, count($visitorIds), '%d')) . ") AND vr.date BETWEEN %s AND %s ORDER BY vr.visitor_id ASC, vr.date ASC, vr.ID ASC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            array_merge($visitorIds, [$from, $to])
        ));

        $postViewIds = array_flip(array_map(function ($row) {
            return intval($row->ID);
        }, $postViews));

        $byVisitor = [];
        foreach ($views as $view) {
            $byVisitor[intval($view->visitor_id)][] = $view;
        }

        $next = [];
        $left = 0;

        foreach ($byVisitor as $visitorId => $visitorViews) {
            $lastPostView = null;

            foreach ($visitorViews as $index => $view) {
                if (!isset($postViewIds[intval($view->ID)])) {
                    continue;
                }

                $lastPostView = $index;
                $nextView     = isset($visitorViews[$index + 1]) ? $visitorViews[$index + 1] : null;

                // The post itself (a reload) is not a next page
                if (!$nextView || (intval($nextView->resource_id) === $postId && (empty($args['resource_type']) || in_array($nextView->type, (array)$args['resource_type'], true)))) {
                    continue;
                }

                $key = intval($nextView->resource_id) > 0 ? $nextView->type . ':' . intval($nextView->resource_id) : $nextView->type . ':' . $nextView->uri;

                if (!isset($next[$key])) {
                    $next[$key] = ['page_id' => intval($nextView->page_id), 'visitors' => []];
                }
                $next[$key]['visitors'][$visitorId] = true;
            }

            // The visitor's last view of this post was their last view
            if ($lastPostView !== null && !isset($visitorViews[$lastPostView + 1])) {
                $left++;
            }
        }

        $list = array_map(function ($item) {
            return (object)['page_id' => $item['page_id'], 'visitors' => count($item['visitors'])];
        }, array_values($next));

        usort($list, function ($a, $b) {
            return $b->visitors <=> $a->visitors ?: $a->page_id <=> $b->page_id;
        });

        $readers = count($byVisitor);

        return [
            'readers'   => $readers,
            'left'      => $left,
            'left_rate' => $readers ? round($left / $readers * 100, 1) : 0.0,
            'pages'     => array_slice($list, 0, max(1, intval($args['per_page']))),
        ];
    }
}
