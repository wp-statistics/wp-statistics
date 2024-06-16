<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit;

use WP_Statistics\Abstracts\BasePage;
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;

class PrivacyAuditPage extends BasePage
{
    protected $pageSlug = 'privacy-audit';

    public function __construct()
    {
        parent::__construct();
    }

    protected function init()
    {

    }

    /**
     * Display HTML
     */
    public function view()
    {
        // Page title
        $args['title']   = esc_html__('Privacy Audit', 'wp-statistics');
        $args['tooltip'] = esc_html__('Check your privacy settings here to make sure WP Statistics is set up safely. This page helps you see if any settings might be collecting personal information and guides you on how to adjust them for better privacy. It\'s an easy way to keep your site\'s data use clear and safe.', 'wp-statistics');

        // Get Current Page Url
        $args['pageName']   = Menus::get_page_slug('privacy_audit');
        $args['pagination'] = Admin_Template::getCurrentPaged();

        // Show Template Page
        Admin_Template::get_template(array('layout/header', 'layout/title', 'pages/privacy-audit', 'layout/footer'), $args);
    }
}
