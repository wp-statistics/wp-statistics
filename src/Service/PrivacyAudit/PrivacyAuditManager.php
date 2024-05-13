<?php

namespace WP_Statistics\Service\PrivacyAudit;

use WP_STATISTICS\Option;

class PrivacyAuditManager
{

    public function __construct()
    {
        if (Option::get('privacy_audit')) {
            add_filter('wp_statistics_admin_menu_list', [$this, 'addMenuItem']);
            add_filter('wp_statistics_ajax_list', [$this, 'registerAjaxCallbacks']);
            add_filter( 'site_status_tests', [$this, 'registerHealthStatusTests']);
            add_action('admin_init', [$this, 'initPrivacyStatusOption']);
        }
    }

    /**
     * Add privacy audit status options, if not already added.
     *
     * @return void
     */
    public function initPrivacyStatusOption()
    {
        PrivacyStatusOption::init();
    }

    /**
     * Add menu item
     *
     * @param array $items
     * @return array
     */
    public function addMenuItem($items)
    {
        $newItem = [
            'privacy_audit' => [
                'sub'      => 'overview',
                'title'    => esc_html__('Privacy Audit', 'wp-statistics'),
                'page_url' => 'privacy-audit',
                'callback' => PrivacyAuditPage::class,
            ]
        ];

        array_splice($items, 17, 0, $newItem);

        return $items;
    }

    /**
     * Add ajax actions
     *
     * @param array $list
     * @return array
     */
    public function registerAjaxCallbacks($list)
    {
        $privacyAuditController = new PrivacyAuditController();

        $list[] = [
            'class'   => $privacyAuditController,
            'action'  => 'getPrivacyStatus'
        ];

        $list[] = [
            'class'   => $privacyAuditController,
            'action'  => 'updatePrivacyStatus'
        ];

        return $list;
    }


    /**
     * Register privacy compliance test for WordPress site health.
     * 
     * @return array $tests
     */
    public function registerHealthStatusTests($tests) 
    {
        $tests['direct']['wp_statistics_privacy_compliance_status'] = [
			'label' => esc_html__('Are your WP Statistics settings privacy-compliant?', 'wp-statistics' ),
			'test'  => [PrivacyAuditCheck::class, 'privacyComplianceTest'],
		];

        return $tests;
    }

}