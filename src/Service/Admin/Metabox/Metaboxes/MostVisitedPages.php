<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Service\Admin\Metabox\MetaboxHelper;

class MostVisitedPages extends BaseMetabox
{
    protected $key = 'pages';
    protected $context = 'normal';

    public function getName()
    {
        return esc_html__('Top Pages', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('Pages with the most views in the period.', 'wp-statistics');
    }

    public function getOptions()
    {
        return [
            'datepicker' => true,
            'button'     => View::load('metabox/action-button', [
                'link'  => Menus::admin_url('pages', ['tab' => 'top']),
                'title' => MetaboxHelper::isDashboardContext() ? esc_html__('View all pages', 'wp-statistics') : esc_html__('View Top Pages', 'wp-statistics')
            ], true)
        ];
    }

    public function getData()
    {
        $args = $this->getFilters();

        // WP Dashboard: ranked list with share bars, 5 rows, no bounce-rate queries.
        if (MetaboxHelper::isDashboardContext()) {
            return View::load('metabox/dashboard/rank-list', [
                'data' => $this->dataProvider->getTopPagesShare($args, MetaboxHelper::DASHBOARD_ROWS),
                'type' => 'pages',
                'date' => $args['date'] ?? [],
            ], true);
        }

        $data = $this->dataProvider->getTopPagesWithEngagement($args);

        $isTodayOrFutureDate = DateTime::isTodayOrFutureDate($args['date']['to'] ?? null);

        $output = View::load('metabox/most-visited-pages', ['data' => $data, 'args' => $args, 'isTodayOrFutureDate' => $isTodayOrFutureDate], true);

        return $output;
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}