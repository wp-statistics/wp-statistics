<?php 

namespace WP_Statistics\Service\Admin\ContentAnalytics\Views;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseView;

class SingleView extends BaseView 
{
    public function isLocked()
    {
        return !Helper::isAddOnActive('data-plus');
    }

    public function render()
    {
        $args       = [];
        $template   = 'single';

        if ($this->isLocked()) {
            $template = 'single-locked';
        }

        Admin_Template::get_template(['layout/header', 'layout/title', "pages/content-analytics/$template", 'layout/footer'], $args);
    }
}