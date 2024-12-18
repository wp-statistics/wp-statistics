<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Helper;

class PostVisitorsLocked extends BaseMetabox
{
    protected $key = 'post_latest_visitors_locked';
    protected $context = 'normal';
    protected $priority = 'high';
    protected $static = true;

    public function getName()
    {
        return esc_html__('Statistics - Latest Visitors', 'wp-statistics');
    }

    public function getDescription()
    {
        return '';
    }

    public function isActive()
    {
        return !Helper::isAddOnActive('data-plus');
    }

    public function getData()
    {
        return false;
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}