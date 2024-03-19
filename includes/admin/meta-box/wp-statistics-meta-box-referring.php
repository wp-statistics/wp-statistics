<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\Referred;

class referring extends MetaBoxAbstract
{

    private static $transient_key = 'meta_box_referring_';

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

        // get cached data if exists
        $keyHash = self::$transient_key . md5(json_encode($args));
        if ($result = get_transient($keyHash)) {
            return self::response($result);
        }

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

        // set cache
        set_transient($keyHash, $response, 60 * 60 * 12);

        // Response
        return self::response($response);
    }

    public static function lang()
    {
        return array(
            'server_ip'  => __('Server IP', 'wp-statistics'),
            'references' => __('Referral Sources', 'wp-statistics')
        );
    }
}
