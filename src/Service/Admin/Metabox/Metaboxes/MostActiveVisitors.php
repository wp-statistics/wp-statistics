<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;
use WP_Statistics\Components\DateTime;

class MostActiveVisitors extends BaseMetabox
{
    protected $key = 'top-visitors';
    protected $context = 'normal';

    public function getName()
    {
        return esc_html__('Most Active Visitors', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('Visitors with the highest number of views, including their country, city, IP address, and browser.', 'wp-statistics');
    }

    public function getOptions()
    {
        return [
            'datepicker'    => true,
            'button'        => View::load('metabox/action-button',[
                'link'  => Menus::admin_url('visitors', ['tab' => 'top-visitors']) ,
                'title' => esc_html__('View Most Active Visitors', 'wp-statistics')
            ],true)
        ];
    }

    public function getData()
    {
        $args = $this->getFilters();

        $isTodayOrFutureDate = DateTime::isTodayOrFutureDate($args['date']['to'] ?? null);


        $data   = $this->dataProvider->getTopVisitorsData($args);
        $output = View::load('metabox/most-active-visitors', ['data' => $data, 'filters' => $args, 'isTodayOrFutureDate' => $isTodayOrFutureDate], true);

        return $output;
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}