<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;
use WP_STATISTICS\UserOnline;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Admin\Metabox\MetaboxHelper;

class CurrentlyOnline extends BaseMetabox
{
    protected $key = 'useronline';
    protected $context = 'normal';

    public function getName()
    {
        return esc_html__('Currently Online', 'wp-statistics');
    }

    public function getDescription()
    {
        return '';
    }

    public function isActive()
    {
        return UserOnline::active();
    }

    public function getOptions()
    {
        $link  = Menus::admin_url('visitors', ['tab' => 'online']);
        $title = esc_html__('View Online Visitors', 'wp-statistics');

        // WP Dashboard: the live page when Real-Time is active, else Online Visitors.
        if (MetaboxHelper::isDashboardContext()) {
            if (Helper::isAddOnActive('realtime-stats')) {
                $link  = admin_url('admin.php?page=wp_statistics_realtime_stats');
                $title = esc_html__('Open Real-Time', 'wp-statistics');
            } else {
                $title = esc_html__('View all online visitors', 'wp-statistics');
            }
        }

        return [
            'datepicker'    => false,
            'button'        => View::load('metabox/action-button',[
                'link'  => $link,
                'title' => $title
            ],true)
        ];
    }

    public function getData()
    {
        if (MetaboxHelper::isDashboardContext()) {
            $data      = $this->dataProvider->getOnlineVisitorsData([], MetaboxHelper::DASHBOARD_ROWS);
            $timeframe = intval(apply_filters('wp_statistics_online_visitors_timeframe', 5));

            return View::load('metabox/dashboard/visitors-list', [
                'data'  => $data['visitors'],
                'total' => $data['total'],
                'mode'  => 'online',
                'empty' => [
                    'title' => __('No one online right now', 'wp-statistics'),
                    /* translators: %s: Number of minutes. */
                    'hint'  => sprintf(_n('Visitors from the last %s minute show here.', 'Visitors from the last %s minutes show here.', $timeframe, 'wp-statistics'), number_format_i18n($timeframe)),
                ],
            ], true);
        }

        $data = $this->dataProvider->getOnlineVisitorsData();

        $output = View::load('metabox/currently-online', ['data' => $data], true);

        return $output;
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}