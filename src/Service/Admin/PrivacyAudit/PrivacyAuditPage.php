<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit;

use WP_Statistics\Abstracts\MultiViewPage;

/**
 * Privacy Audit Page.
 *
 * Renders the legacy Privacy Audit page using PHP templates.
 *
 * @since 15.0.0
 */
class PrivacyAuditPage extends MultiViewPage
{
    protected $pageSlug = 'privacy-audit';
    protected $defaultView = 'privacy-audit';
    protected $views = [];

    public function __construct()
    {
        parent::__construct();
    }

    protected function init()
    {
        $this->disableScreenOption();
    }

    /**
     * Render the page.
     *
     * @return void
     */
    public function view()
    {
        $dataProvider = new PrivacyAuditDataProvider();

        $data = [
            'compliance_status'  => $dataProvider->getComplianceStatus(),
            'recommended_audits' => $dataProvider->getAuditsByStatus('recommended'),
            'passed_audits'      => $dataProvider->getAuditsByStatus('success'),
            'unpassed_audits'    => $dataProvider->getAuditsByStatus('warning'),
            'faq_list'           => $dataProvider->getFaqsStatus(),
        ];

        \WP_STATISTICS\Admin_Template::get_template('pages/privacy-audit', ['data' => $data]);
    }
}
