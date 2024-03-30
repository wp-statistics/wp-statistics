<?php

namespace WP_Statistics\Service\PrivacyAudit;

class PrivacyAuditManager
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
    public function addMenuItem( $items )
    {
        $newItem = [
            'privacy_audit' => [
                'sub'       => 'overview',
                'title'     => esc_html__('Privacy Audit', 'wp-statistics'),
                'page_url'  => 'privacy-audit',
                'class'     => new PrivacyAuditPage()
            ]
        ];

        array_splice($items, 14, 0, $newItem);

        return $items;
    }

}