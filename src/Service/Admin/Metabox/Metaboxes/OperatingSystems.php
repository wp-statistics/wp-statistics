<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;

class OperatingSystems extends BaseMetabox
{
    protected $key = 'platforms';
    protected $context = 'side';

    public function getName()
    {
        return esc_html__('Most Used Operating Systems', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('Identify the operating systems most commonly used by your website visitors.', 'wp-statistics');
    }

    public function getOptions()
    {
        return [
            'datepicker'    => true,
            'button'        => View::load('metabox/action-button',[
                'link'  => Menus::admin_url('devices', ['tab' => 'platforms']),
                'title' => esc_html__('View Most Used OS', 'wp-statistics')
            ],true)
        ];
    }

    public function getData()
    {
        $args = $this->getFilters();

        $data = array_merge($this->dataProvider->getOsChartData($args), [
            'tag_id' => 'wps-most-used-operating-systems',
            'url'    => WP_STATISTICS_URL . 'assets/images/no-data/vector-2.svg'
        ]);

        $output = View::load('metabox/horizontal-bar', ['data' => $data], true);

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