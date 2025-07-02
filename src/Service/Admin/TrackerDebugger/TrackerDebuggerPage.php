<?php

namespace WP_Statistics\Service\Admin\TrackerDebugger;

use WP_Statistics\Abstracts\BasePage;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\RemoteRequest;
use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Models\VisitorsModel;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Debugger\DebuggerFactory;
use WP_STATISTICS\TimeZone;

class TrackerDebuggerPage extends BasePage
{
    protected $pageSlug = 'tracker-debugger';

    public function __construct()
    {
        parent::__construct();
    }

    public function view()
    {
        $providers = (new DebuggerFactory())->getAllProviders();

        $args = [
            'title'     => esc_html__('Tracker Debugger', 'wp-statistics'),
            'pageName'  => Menus::get_page_slug('tracker_debugger'),
        ];

        $args = array_merge($args, $providers);
                
        Admin_Template::get_template(['layout/header', 'layout/title'], $args);
        View::load(['pages/tracker-debugger/tracker-debugger'], $args);
        Admin_Template::get_template(['layout/footer'], $args);
    }
}
