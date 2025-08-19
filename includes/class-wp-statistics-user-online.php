<?php

namespace WP_STATISTICS;

use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Models\OnlineModel;

class UserOnline
{
    /**
     * Check Active User Online System
     *
     * @return mixed
     */
    public static function active()
    {
        /**
         * Disable/Enable User Online for Custom request
         *
         * @example add_filter('wp_statistics_active_user_online', function(){ if( is_page() ) { return false; } });
         */
        return (has_filter('wp_statistics_active_user_online')) ? apply_filters('wp_statistics_active_user_online', true) : Option::get('useronline', true);
    }

    /**
     * Check IP is online
     *
     * @deprecated This method is deprecated. Use OnlineModel::getOnlineVisitor instead.
     * @param bool $user_ip
     * @return bool
     */
    public static function is_ip_online($user_ip = false)
    {
        $onlineModel = new OnlineModel();
        $user_online = $onlineModel->getOnlineVisitors(['ip' => $user_ip]);

        return (!$user_online ? false : $user_online);
    }

    /**
     * Get User Online List By Custom Query
     *
     * @deprecated This method is deprecated. Use OnlineModel::getOnlineVisitorsData instead.
     * @param array $arg
     * @return array
     * @throws \Exception
     */
    public static function get($arg = array())
    {
        $onlineModel = new OnlineModel();

        $result = $onlineModel->getOnlineVisitors($arg);

        // Get List
        $list = array();
        foreach ($result as $items) {
            /** @var VisitorDecorator $items */

            $ip       = esc_html($items->getRawIP());
            $agent    = esc_html($items->getBrowser()->getName());
            $platform = esc_html($items->getOs()->getName());

            $item = array(
                'referred' => $items->getReferral()->getReferrer(),
                'agent'    => $agent,
                'platform' => $platform,
                'version'  => $items->getBrowser()->getVersion(),
            );

            // Add User information
            if ($items->getUser()) {
                $item['user'] = array(
                    'ID'         => $items->getUser()->getId(),
                    'user_email' => $items->getUser()->getEmail(),
                    'user_login' => $items->getUser()->getUsername(),
                    'name'       => $items->getUser()->getDisplayName(),
                );
            }

            // Page info
            $item['page'] = $items->getLastPage();

            // Push Browser
            $item['browser'] = array(
                'name' => $agent,
                'logo' => $items->getBrowser()->getLogo(),
                'link' => Menus::admin_url('visitors', array('agent' => $agent))
            );

            // Push IP
            if (IP::IsHashIP($ip)) {
                $item['ip'] = array('value' => substr($ip, 6, 10), 'link' => Menus::admin_url('visitors', array('ip' => urlencode($ip))));
            } else {
                $item['ip']  = array('value' => $ip, 'link' => Menus::admin_url('visitors', array('ip' => $ip)));
                $item['map'] = Helper::geoIPTools($ip);
            }

            $item['country'] = array('location' => $items->getLocation()->getCountryCode(), 'flag' => $items->getLocation()->getCountryFlag(), 'name' => $items->getLocation()->getCountryName());
            $item['city']    = $items->getLocation()->getCity();
            $item['region']  = $items->getLocation()->getRegion();

            $item['single_url'] = Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $items->getId()]);

            $item['online_for'] = $items->getOnlineTime();

            $list[] = $item;
        }

        return $list;
    }


}