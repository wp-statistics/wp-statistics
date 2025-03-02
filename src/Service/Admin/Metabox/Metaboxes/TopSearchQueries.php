<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;

class TopSearchQueries extends BaseMetabox
{
    protected $key = 'search-queries';
    protected $context = 'normal';

    public function getName()
    {
        return esc_html__('Top Search Queries', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('', 'wp-statistics');
    }

    public function getOptions()
    {
        return [
            'datepicker'    => false,
        ];
    }

    public function getData()
    {
        $args = $this->getFilters();

        $data   = '';
        $output = View::load('metabox/top-search-queries', ['data' => $data, 'filters' => $args], true);

        return $output;
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}