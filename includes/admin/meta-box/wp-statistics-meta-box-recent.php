<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\DB;
use WP_STATISTICS\Option;
use WP_STATISTICS\Visitor;

class recent
{

    public static function get($args = array())
    {
        /**
         * Filters the args used from metabox for query stats
         *
         * @param array $args The args passed to query stats
         * @since 14.2.1
         *
         */
        $args = apply_filters('wp_statistics_meta_box_recent_args', $args);

        // Prepare Response
        try {

            $visitorTable      = DB::table('visitor');
            $relationshipTable = DB::table('visitor_relationships');

            if (Option::get('visitors_log')) {
                $args['sql'] = "SELECT vsr.*, vs.* FROM ( SELECT visitor_id, page_id, MAX(date) AS latest_visit_date FROM `{$relationshipTable}` GROUP BY visitor_id ) AS latest_visits JOIN `{$visitorTable}` vs ON latest_visits.visitor_id = vs.ID JOIN `{$relationshipTable}` vsr ON vsr.visitor_id = latest_visits.visitor_id AND vsr.date = latest_visits.latest_visit_date ORDER BY vsr.date DESC";
            }

            $response = Visitor::get($args);

        } catch (\Exception $e) {
            \WP_Statistics::log($e->getMessage());
            $response = array();
        }

        // Check For No Data Meta Box
        if (count($response) < 1) {
            $response['no_data'] = 1;
        }

        // Response
        return $response;
    }

}