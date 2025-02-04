<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;

class SearchEngines extends BaseMetabox
{
    protected $key = 'search';
    protected $context = 'normal';

    public function getName()
    {
        return esc_html__('Referrals from Search Engines', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('A breakdown of views from different search engines over time.', 'wp-statistics');
    }

    public function getOptions()
    {
        return [
            'datepicker'    => true,
            'button'        => View::load('metabox/action-button',[
                'link'  => Menus::admin_url('referrals', ['tab' => 'search-engines']),
                'title' => esc_html__('View Referrals from Search Engines', 'wp-statistics')
            ],true)
        ];
    }

    public function getData()
    {
        $args = $this->getFilters();

        $data   = $this->dataProvider->getSearchEnginesChartData($args);
        $output = View::load('metabox/search_engines', [], true);

        return [
            'output'    => $output,
            'data'      => $data
        ];
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}