<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;

class GoPremium extends BaseMetabox
{
    protected $key = 'go-premium';
    protected $context = 'side';
    protected $dismissible = true;
    protected $static = true;

    public function getName()
    {
        return esc_html__('Go Premium', 'wp-statistics');
    }

    public function getDescription()
    {
        return '';
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
        View::load('metabox/go-premium', ['widget_id' => $this->getKey()]);
    }
}