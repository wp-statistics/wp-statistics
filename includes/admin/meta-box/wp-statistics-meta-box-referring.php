<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\Menus;
use WP_Statistics\Models\VisitorsModel;
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
        $args['date']['from']  = self::$fromDate;
        $args['date']['to']    = self::$toDate;
        $args['per_page'] = $number;

        // Get List Top Referring
        try {
            $visitorsModel  = new VisitorsModel();
            $referrers      = $visitorsModel->getReferrers($args);
            $parsedReferrers= [];

            foreach ($referrers as $referrer) {
                $parsedReferrers[]      = [
                    'domain'    => $referrer->referred,
                    'page_link' => Menus::admin_url('referrals', ['referrer' => $referrer->referred]),
                    'number'    => number_format_i18n($referrer->visitors)
                ];
            }

            $response['referring'] = $parsedReferrers;
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
