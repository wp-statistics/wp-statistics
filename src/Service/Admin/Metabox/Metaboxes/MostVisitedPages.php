<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;

class MostVisitedPages extends BaseMetabox
{
    protected $key = 'pages';
    protected $context = 'normal';

    public function getName()
    {
        return esc_html__('Most Visited Pages', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('Pages on your website with the highest number of views in the selected time frame.', 'wp-statistics');
    }

    public function getOptions()
    {
        return [
            'datepicker'    => true,
            'button'        => View::load('metabox/action-button',[
                'link'  => Menus::admin_url('pages'),
                'title' => esc_html__('View Most Visited Pages', 'wp-statistics')
            ],true)
        ];
    }

    public function getData()
    {
        $args = $this->getFilters();
        $data = $this->dataProvider->getTopPages($args);

        $output = View::load('metabox/most-visited-pages', ['data' => $data, 'args' => $args], true);

        return $output;
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}