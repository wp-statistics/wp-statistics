<?php

namespace WP_Statistics\Service\Admin\Referrals;

class ReferralsManager
{

    public function __construct()
    {
        add_filter('wp_statistics_admin_menu_list', [$this, 'addMenuItem']);
    }

    /**
     * Add menu item
     *
     * @param array $items
     * @return array
     */
    public function addMenuItem($items)
    {
        $items['referrals'] = [
            'sub'       => 'overview',
            'title'     => esc_html__('Referrals', 'wp-statistics'),
            'page_url'  => 'referrals',
            'callback'  => ReferralsPage::class,
            'priority'  => 27
        ];

        return $items;
    }
}
