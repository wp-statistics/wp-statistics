<?php

namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;
use WP_Statistics\Components\DateTime;

class BrowserUsage extends BaseMetabox
{
    protected $key = 'browsers';
    protected $context = 'side';

    public function getName()
    {
        return esc_html__('Browser Usage', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('Distribution of visitors based on the browsers they use.', 'wp-statistics');
    }

    public function getOptions()
    {
        return [
            'datepicker' => true,
            'button'     => View::load('metabox/action-button', [
                'link'  => Menus::admin_url('devices', ['tab' => 'browsers']),
                'title' => esc_html__('View Browser Usage', 'wp-statistics')
            ], true)
        ];
    }

    public function getData()
    {
        $args = $this->getFilters();

        $isTodayOrFutureDate = DateTime::isTodayOrFutureDate($args['date']['to'] ?? null);

        $data = $this->dataProvider->getBrowsersChartData($args);

        $data = array_merge($data, [
            'tag_id' => 'wps-browser-usage',
            'url'    => WP_STATISTICS_URL . 'assets/images/no-data/vector-3.svg'
        ]);

        $output = View::load('metabox/horizontal-bar', ['data' => $data, 'filters' => $args, 'isTodayOrFutureDate' => $isTodayOrFutureDate], true);

        return [
            'output' => $output,
            'data'   => $data
        ];
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}