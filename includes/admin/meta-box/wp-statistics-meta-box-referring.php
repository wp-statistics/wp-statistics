<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\Referred;

class referring extends MetaBoxAbstract
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
        $args = apply_filters('wp_statistics_meta_box_referring_args', $args);


        // Check Number of Country
        $number = (!empty($args['number']) ? $args['number'] : 10);

        // Filter By Date
        self::filterByDate($args);
        $args['from']  = self::$fromDate;
        $args['to']    = self::$toDate;
        $args['limit'] = $number;

        // Get List Top Referring
        try {
            $result   = Referred::getList($args);
            $get_urls = [];
            foreach ($result as $items) {
                $get_urls[$items->domain] = $items->number;
            }
            $response['referring'] = Referred::PrepareReferData($get_urls);
        } catch (\Exception $e) {
            $response = [
                'referring' => []
            ];
        }

        // Check For No Data Meta Box
        if (count($response['referring']) < 1) {
            $response['no_data'] = 1;
        }

        // Response
        return self::response($response);
    }

    public static function lang()
    {
        return array(
            'server_ip'  => __('Server IP', 'wp-statistics'),
            'references' => __('Number of Referrals', 'wp-statistics')
        );
    }
}
