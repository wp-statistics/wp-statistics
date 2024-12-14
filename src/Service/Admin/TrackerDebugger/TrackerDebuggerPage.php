<?php

namespace WP_Statistics\Service\Admin\TrackerDebugger;

use WP_Statistics\Abstracts\BasePage;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;

class TrackerDebuggerPage extends BasePage
{
    protected $pageSlug = 'tracker-debugger';

    public function __construct()
    {
        parent::__construct();
    }

    public function view()
    {
        $args = [
            'title'     => esc_html__('Tracker Debugger', 'wp-statistics'),
            'tooltip'   => esc_html__('tooltip', 'wp-statistics'),
            'pageName'  => Menus::get_page_slug('tracker_debugger'),
        ];

        Admin_Template::get_template(['layout/header', 'layout/title'], $args);
        View::load(['pages/tracker-debugger/tracker-debugger'], $args);
        Admin_Template::get_template(['layout/footer'], $args);
    }
}
