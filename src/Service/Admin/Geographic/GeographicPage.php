<?php

namespace WP_Statistics\Service\Admin\Geographic;

use WP_Statistics\Abstracts\MultiViewPage;
use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\BackgroundProcessFactory;
use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs\IncompleteGeoIpUpdater;
use WP_STATISTICS\Menus;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Admin\Geographic\Views\SingleCountryView;
use WP_Statistics\Service\Admin\Geographic\Views\TabsView;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Utils\Request;

class GeographicPage extends MultiViewPage
{
    protected $pageSlug = 'geographic';
    protected $defaultView = 'tabs';
    protected $views = [
        'tabs'           => TabsView::class,
        'single-country' => SingleCountryView::class
    ];

    /**
     * @var VisitorsModel
     */
    private $visitorModel;

    public function __construct()
    {
        parent::__construct();
    }

    protected function init()
    {
        $this->visitorModel = new VisitorsModel();

        $this->disableScreenOption();
        $this->checkIncompleteGeoIpUpdaterNotice();
        $this->processIncompleteGeoIpUpdaterBackgroundAction();
    }

    /**
     * Check for visitors with incomplete location data
     *
     * @return void
     */
    private function checkIncompleteGeoIpUpdaterNotice()
    {
        /** @var IncompleteGeoIpUpdater $backgroundProcess */
        $backgroundProcess = WP_Statistics()->getBackgroundProcess('update_unknown_visitor_geoip');

        if (!$backgroundProcess->is_initiated()) {
            $actionUrl = add_query_arg(
                [
                    'action' => 'update_unknown_visitor_geoip',
                    'nonce'  => wp_create_nonce('update_unknown_visitor_geoip_nonce')
                ],
                Menus::admin_url('geographic')
            );

            $message = sprintf(
                __('Detected visitors without location data. Please <a href="%s">click here</a> to update the geographic data in the background. This is necessary for accurate analytics.', 'wp-statistics'),
                esc_url($actionUrl),
                '' // compatibility with old translations
            );

            Notice::addNotice($message, 'update_unknown_visitor_geoip');
        }

        if ($backgroundProcess->is_active()) {
            Notice::addNotice(
                __('Geographic data update is already in progress.', 'wp-statistics'),
                'running_geoip_process_notice',
                'info',
                true
            );
        }
    }

    private function processIncompleteGeoIpUpdaterBackgroundAction()
    {
        // Check the action and nonce
        if (!Request::compare('action', 'update_unknown_visitor_geoip')) {
            return;
        }

        check_admin_referer('update_unknown_visitor_geoip_nonce', 'nonce');

        // Update GeoIP data for visitors with incomplete information
        BackgroundProcessFactory::batchUpdateIncompleteGeoIpForVisitors();

        wp_redirect(Menus::admin_url('geographic'));
        exit;
    }
}
