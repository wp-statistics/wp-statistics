<?php

namespace WP_Statistics\Service\Admin\Geographic;

use WP_STATISTICS\GeoIP;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Abstracts\MultiViewPage;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Admin\Geographic\Views\SingleCountryView;
use WP_Statistics\Service\Admin\Geographic\Views\TabsView;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Analytics\GeoIpService;
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

        if (Option::get('geoip') && GeoIP::IsSupport()) {
            $this->checkIncompleteGeoIpUpdaterNotice();
            $this->processIncompleteGeoIpUpdaterBackground();
        }
    }

    /**
     * Check for visitors with incomplete location data
     *
     * @return void
     */
    private function checkIncompleteGeoIpUpdaterNotice()
    {
        $visitorCount = $this->visitorModel->getVisitorsWithIncompleteLocation(true);

        if ($visitorCount > 0 && !Option::getOptionGroup('jobs', 'update_unknown_visitor_geoip_started')) {
            $actionUrl = add_query_arg(
                [
                    'action' => 'update_unknown_visitor_geoip',
                    'nonce'  => wp_create_nonce('update_unknown_visitor_geoip_nonce')
                ],
                Menus::admin_url('geographic')
            );

            $message = sprintf(
                __('Detected <b>%d</b> visitors without location data. Please <a href="%s">click here</a> to update the geographic data in the background. This is necessary for accurate analytics.', 'wp-statistics'),
                $visitorCount,
                esc_url($actionUrl)
            );

            Notice::addNotice($message, 'update_unknown_visitor_geoip', 'info', false);
        }
    }

    private function processIncompleteGeoIpUpdaterBackground()
    {
        // Check the action and nonce
        if (!Request::compare('action', 'update_unknown_visitor_geoip')) {
            return;
        }

        check_admin_referer('update_unknown_visitor_geoip_nonce', 'nonce');

        // Check if already processed
        if (Option::getOptionGroup('jobs', 'update_unknown_visitor_geoip_started')) {
            Notice::addFlashNotice(__('Geographic data update is already in progress.', 'wp-statistics'));

            wp_redirect(Menus::admin_url('geographic'));
            exit;
        }

        // Mark the process as completed
        Option::saveOptionGroup('jobs', 'update_unknown_visitor_geoip_started', false);

        // Before the process, let's make sure the Maxmind Databases are up to date
        $result = GeoIP::download('country', 'update');
        GeoIP::download('city', 'update');

        if (isset($result['status']) and $result['status'] === false) {
            $errorMessage        = isset($result['notice']) ? $result['notice'] : 'an unknown error occurred';
            $userFriendlyMessage = sprintf(
                __('GeoIP functionality could not be activated due to an error: %s.', 'wp-statistics'),
                $errorMessage
            );

            Notice::addFlashNotice($userFriendlyMessage, 'error');

        } else {
            $geoIpService = new GeoIpService();
            $geoIpService->batchUpdateIncompleteGeoIpForVisitors();
        }

        wp_redirect(Menus::admin_url('geographic'));
        exit;
    }
}
