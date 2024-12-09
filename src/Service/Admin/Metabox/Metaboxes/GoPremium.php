<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;

class GoPremium extends BaseMetabox
{
    protected $key = 'go_premium';
    protected $priority = 'side';

    public function getName()
    {
        return esc_html__('Go Premium', 'wp-statistics');
    }

    public function getDescription()
    {
        return '';
    }

    /**
     * Returns the screens the metabox is active on
     * @return array
     */
    public function getScreen()
    {
        return [Menus::get_action_menu_slug('overview')];
    }

    public function getData()
    {
        $output = View::load('metabox/go-premium',[],true);

        return $output;
    }

    public function render()
    {
        View::load('metabox/go-premium', []);
    }
}