<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;

class AboutWPS extends BaseMetabox
{
    protected $key = 'about_wps';
    protected $priority = 'side';

    public function getName()
    {
        return esc_html__('WP Statistics', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('', 'wp-statistics');
    }

    public function getScreen()
    {
        return [Menus::get_action_menu_slug('overview')];
    }

    public function getData()
    {
        return false;
    }

    public function render()
    {
        View::load('metabox/about', []);
    }
}