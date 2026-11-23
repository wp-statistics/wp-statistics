<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Service\Admin\Metabox\MetaboxHelper;

class TopReferring extends BaseMetabox
{
    protected $key = 'referring';
    protected $context = 'side';

    public function getName()
    {
        return esc_html__('Top Referring', 'wp-statistics');
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
                'link'  => Menus::admin_url('referrals', ['tab' => 'referrers']),
                'title' => MetaboxHelper::isDashboardContext() ? esc_html__('View all referrers', 'wp-statistics') : esc_html__('View Top Referring', 'wp-statistics')
            ],true)
        ];
    }

    public function getData()
    {
        $args = $this->getFilters();

        $isTodayOrFutureDate = DateTime::isTodayOrFutureDate($args['date']['to'] ?? null);

        // WP Dashboard: share rows like Top Countries, 5 rows.
        if (MetaboxHelper::isDashboardContext()) {
            return View::load('metabox/dashboard/rank-list', [
                'data' => $this->dataProvider->getReferrersShare($args, MetaboxHelper::DASHBOARD_ROWS),
                'type' => 'referrers',
                'date' => $args['date'] ?? [],
            ], true);
        }

        // Overview: same share rows as Top Countries (rows + range total for the share).
        $data   = $this->dataProvider->getReferrersShare($args, 5);
        $output = View::load('metabox/top-referring', ['data' => $data, 'filters' => $args, 'isTodayOrFutureDate' => $isTodayOrFutureDate], true);

        return $output;
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}