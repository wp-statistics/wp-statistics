<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\Menus;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Models\OnlineModel;
use WP_Statistics\Decorators\VisitorDecorator;

class useronline
{

    public static function get($args = array())
    {
        $args = wp_parse_args($args, [
            'order_by'  => 'date',
            'order'     => 'DESC'
        ]);

        /**
         * Filters the args used from metabox for query stats
         *
         * @param array $args The args passed to query stats
         * @since 14.2.1
         *
         */
        $args = apply_filters('wp_statistics_meta_box_useronline_args', $args);

        // Prepare Response
        try {

            $onlineModel = new OnlineModel();
            $response    = $onlineModel->getOnlineVisitorsData($args);

        } catch (\Exception $e) {
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
                'ID'            => $visitor->getId(),
                'IP'            => $visitor->getIP(),
                'last_view'     => $visitor->getLastView(),
                'last_page'     => $visitor->getLastPage(),
                'hits'          => $visitor->getHits(),
                'online_time'   => $visitor->getOnlineTime(),
                'single_url'    => Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()]),
                'referrer'      => [
                    'name' => $visitor->getReferral()->getRawReferrer(),
                    'link' => $visitor->getReferral()->getReferrer()
                ],
                'location'      => [
                    'country'       => $visitor->getLocation()->getCountryName(),
                    'flag'          => $visitor->getLocation()->getCountryFlag(),
                    'location'      => Admin_Template::locationColumn($visitor->getLocation()->getCountryCode(), $visitor->getLocation()->getRegion(), $visitor->getLocation()->getCity()),
                ],
                'browser'       => [
                    'name'      => $visitor->getBrowser()->getName(),
                    'version'   => $visitor->getBrowser()->getVersion(),
                    'logo'      => $visitor->getBrowser()->getLogo()
                ],
                'os'            => [
                    'name'      => $visitor->getOs()->getName(),
                    'logo'      => $visitor->getOs()->getLogo()
                ],
                'user'          => $visitor->isLoggedInUser() ? [
                    'name'  => $visitor->getUser()->getDisplayName(),
                    'email' => $visitor->getUser()->getEmail(),
                    'role'  => $visitor->getUser()->getRole(),
                ] : [],
            ];
        }

        return $result;
    }

}