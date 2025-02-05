<?php

namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;

class GlobalVisitorDistribution extends BaseMetabox
{
    protected $key = 'hitsmap';
    protected $context = 'normal';

    public function getName()
    {
        return esc_html__('Global Visitor Distribution', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('Geographical representation of where your site\'s visitors come from.', 'wp-statistics');
    }

    public function getOptions()
    {
        return [
            'datepicker' => true,
        ];
    }

    public function getData()
    {
        $args = $this->getFilters();

        $data = $this->dataProvider->getMapChartData($args);

        $output = View::load('metabox/global-visitor-distribution',  ['data' => $data], true);

        return [
            'data'      => $data,
            'output'    => $output
        ];
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}