<?php
namespace WP_Statistics\Service\Admin\Optimization;

use WP_Statistics\Abstracts\BasePage;
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\DB;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_STATISTICS\User;
use WP_Statistics\Utils\Request;

class OptimizationPage extends BasePage
{
    protected $pageSlug = 'optimization';

    public function __construct()
    {
        parent::__construct();
    }

    public function view()
    {
        $args = [
            'class'  => 'wp-statistics-settings',
            'title'  => esc_html__('Optimization', 'wp-statistics'),
            'tables' => DB::getTableRows(),
        ];

        Admin_Template::get_template(['layout/header', 'optimization', 'layout/footer'], $args);
    }
}