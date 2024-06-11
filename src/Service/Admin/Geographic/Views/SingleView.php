<?php 
namespace WP_Statistics\Service\Admin\Geographic\Views;

use WP_Statistics\Abstracts\BaseView;
use WP_STATISTICS\Admin_Template;

class SingleView extends BaseView
{
    public function render()
    {
        $args = [];
        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/geographic/single-locked', 'layout/footer'], $args);
    }
}