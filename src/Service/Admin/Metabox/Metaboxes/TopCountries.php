<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;

class TopCountries extends BaseMetabox
{
    protected $key = 'countries';
    protected $context = 'side';

    public function getName()
    {
        return esc_html__('Top Countries', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('', 'wp-statistics');
    }

    public function getOptions()
    {
        return [
            'datepicker'    => true,
            'button'        => View::load('metabox/action-button',[
                'link'  => Menus::admin_url('geographic'),
                'title' => esc_html__('View Top Countries', 'wp-statistics')
            ],true)
        ];
    }

    public function getData()
    {
        $args = $this->getFilters();

        $data = $this->dataProvider->getTopCountiesData($args);

        $output = View::load('metabox/top-countries', ['data' => $data, 'filters' => $args], true);

        return $output;
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}