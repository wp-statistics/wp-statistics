<?php 
namespace WP_Statistics\Service\Admin\Visitors\Views;

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseView;
use WP_Statistics\Components\View;

class SingleVisitorView extends BaseView
{
    public function __construct()
    {

    }

    public function render()
    {
        $args = [];
        Admin_Template::get_template(['layout/header', 'layout/title'], $args);
        View::load('pages/visitors/single-visitor');
        Admin_Template::get_template(['layout/footer'], $args);
    }
}