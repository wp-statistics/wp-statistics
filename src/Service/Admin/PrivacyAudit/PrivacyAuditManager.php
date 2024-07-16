<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit;

use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\Abstracts\ResolvableAudit;

class PrivacyAuditManager
{

    public function __construct()
    {
        if (Option::get('privacy_audit')) {
            add_filter('wp_statistics_admin_menu_list', [$this, 'addMenuItem']);
            add_filter('wp_statistics_ajax_list', [$this, 'registerAjaxCallbacks']);
            add_filter('site_status_tests', [$this, 'registerHealthStatusTests']);
            add_action('admin_init', [$this, 'initOptions']);
        }
    }

    /**
     * Add privacy audit status options, if not already added.
     *
     * @return void
     */
    public function initOptions()
    {
        $dataProvider   = new PrivacyAuditDataProvider();
        $audits         = $dataProvider->getAudits();
        $defaultOptions = [];

        /** @var ResolvableAudit $audit */
        foreach ($audits as $audit) {
            // Only resolvable audits state needs to be stored in options
            if (!is_subclass_of($audit, ResolvableAudit::class)) continue;

            // By default, all audits should be action_required
            $defaultOptions[$audit::$optionKey] = 'action_required';
        }

        foreach ($defaultOptions as $key => $value) {
            Option::addOptionGroup($key, $value, 'privacy_status');
        }
    }

    /**
     * Add menu item
     *
     * @param array $items
     * @return array
     */
    public function addMenuItem($items)
    {
        $items['privacy_audit'] = [
            'sub'      => 'overview',
            'title'    => esc_html__('Privacy Audit', 'wp-statistics'),
            'page_url' => 'privacy-audit',
            'callback' => PrivacyAuditPage::class,
            'priority'  => 91,
        ];

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
            'class'  => $privacyAuditController,
            'action' => 'getPrivacyStatus'
        ];

        $list[] = [
            'class'  => $privacyAuditController,
            'action' => 'updatePrivacyStatus'
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
            'label' => esc_html__('Are your WP Statistics settings privacy-compliant?', 'wp-statistics'),
            'test'  => [new PrivacyAuditController, 'privacyComplianceTest'],
        ];

        return $tests;
    }

}