<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit;

use WP_Statistics\Abstracts\BasePage;
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Menus;

class PrivacyAuditPage extends BasePage
{
    protected $pageSlug = 'privacy-audit';
    protected $dataProvider;

    public function __construct()
    {
        parent::__construct();
        $this->dataProvider = new PrivacyAuditDataProvider();
    }

    public function getData()
    {
        return [
            'compliance_status' => $this->dataProvider->getComplianceStatus()
        ];
    }

    /**
     * Display HTML
     */
    public function render()
    {
        $args = [
            'title'         => esc_html__('Privacy Audit', 'wp-statistics'),
            'tooltip'       => esc_html__('Settings that may collect personal data, and how to change them.', 'wp-statistics'),
            'pageName'      => Menus::get_page_slug('privacy_audit'),
            'data'          => $this->getData()
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/privacy-audit', 'layout/footer'], $args);
    }
}
