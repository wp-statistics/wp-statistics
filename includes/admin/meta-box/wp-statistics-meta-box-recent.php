<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\Menus;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Models\VisitorsModel;

class recent
{

    public static function get($args = array())
    {
        $args = wp_parse_args($args, [
            'page'          => 1,
            'per_page'      => 10,
            'ignore_date'   => true,
            'user_info'     => true,
            'page_info'     => true
        ]);

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

            $visitorsModel  = new VisitorsModel();
            $response       = $visitorsModel->getVisitorsData($args);

        } catch (\Exception $e) {
            \WP_Statistics::log($e->getMessage());
            $response = array();
        }

        // Check For No Data Meta Box
        if (count($response) < 1) {
            $response['no_data'] = 1;
        } else {
            $response = self::prepareResponse($response);
        }

        // Response
        return $response;
    }

    private static function prepareResponse($data)
    {
        $result = [];

        foreach ($data as $visitor) {
            /** @var VisitorDecorator $visitor */

            $result[] = [
                'ID'        => $visitor->getId(),
                'IP'        => $visitor->getIP(),
                'last_view' => $visitor->getLastView(),
                'last_page' => $visitor->getLastPage(),
                'hits'      => $visitor->getHits(),
                'single_url'=> Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()]),
                'referrer'  => [
                    'name' => $visitor->getReferral()->getRawReferrer(),
                    'link' => $visitor->getReferral()->getReferrer()
                ],
                'location'  => [
                    'country'       => $visitor->getLocation()->getCountryName(),
                    'flag'          => $visitor->getLocation()->getCountryFlag(),
                    'location'      => Admin_Template::locationColumn($visitor->getLocation()->getCountryCode(), $visitor->getLocation()->getRegion(), $visitor->getLocation()->getCity()),
                ],
                'browser'   => [
                    'name'      => $visitor->getBrowser()->getName(),
                    'version'   => $visitor->getBrowser()->getVersion(),
                    'logo'      => $visitor->getBrowser()->getLogo()
                ],
                'os'        => [
                    'name'      => $visitor->getOs()->getName(),
                    'logo'      => $visitor->getOs()->getLogo()
                ],
                'user'      => $visitor->isLoggedInUser() ? [
                    'name'  => $visitor->getUser()->getDisplayName(),
                    'email' => $visitor->getUser()->getEmail(),
                    'role'  => $visitor->getUser()->getRole(),
                ] : [],
            ];
        }

        return $result;
    }

}