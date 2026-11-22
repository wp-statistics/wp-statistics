<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\Metabox\MetaboxHelper;

class LatestVisitor extends BaseMetabox
{
    protected $key = 'recent';
    protected $context = 'normal';

    public function getName()
    {
        return esc_html__('Latest Visitors', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('The latest visitors, newest first.', 'wp-statistics');
    }

    public function getOptions()
    {
        return [
            'datepicker'    => false,
            'button'        => View::load('metabox/action-button', [
                'link'  => Menus::admin_url('visitors', ['tab' => 'visitors']),
                'title' => MetaboxHelper::isDashboardContext() ? esc_html__('View all visitors', 'wp-statistics') : esc_html__('View Latest Visitors', 'wp-statistics')
            ], true)
        ];
    }

    public function getData()
    {
        $args = ['ignore_date' => true];

        // WP Dashboard: compact list, 5 rows fetched (not 10 hidden).
        if (MetaboxHelper::isDashboardContext()) {
            $data = $this->dataProvider->getLatestVisitorsData($args, MetaboxHelper::DASHBOARD_ROWS);

            return View::load('metabox/dashboard/visitors-list', [
                'data'  => $data,
                'mode'  => 'latest',
                'empty' => [
                    'title' => __('No visitors yet', 'wp-statistics'),
                    'hint'  => __('New visits show here as they happen.', 'wp-statistics'),
                ],
            ], true);
        }

        $data = $this->dataProvider->getLatestVisitorsData($args);

        $output = View::load('metabox/latest-visitor', ['data' => $data], true);

        return $output;
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}