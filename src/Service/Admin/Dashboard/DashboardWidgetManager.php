<?php

namespace WP_Statistics\Service\Admin\Dashboard;

use WP_Statistics\Components\View;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Utils\Format;
use WP_Statistics\Utils\Math;
use WP_Statistics\Utils\User;

/**
 * WordPress Dashboard Summary Widget.
 *
 * Adds a traffic overview widget to the native WordPress admin dashboard
 * (/wp-admin/index.php) showing today's visitors/views, monthly visitors,
 * top pages, and top referrers.
 *
 * @since 15.0.0
 */
class DashboardWidgetManager
{
    public function __construct()
    {
        add_action('wp_dashboard_setup', [$this, 'register']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /**
     * Whether the dashboard widget should show for the current user.
     *
     * @return bool
     */
    private function shouldShow(): bool
    {
        return (bool) User::hasAccess('read');
    }

    /**
     * Register the dashboard widget and enqueue assets.
     */
    public function register(): void
    {
        if (!$this->shouldShow()) {
            return;
        }

        $online = (int) wp_statistics_useronline();

        $title = esc_html__('WP Statistics', 'wp-statistics');

        if ($online > 0) {
            $title .= ' <span class="wps-dw-online"><span class="wps-dw-online-dot"></span> '
                . esc_html(sprintf(
                    /* translators: %s: number of online visitors */
                    __('%s online', 'wp-statistics'),
                    number_format_i18n($online)
                ))
                . '</span>';
        }

        wp_add_dashboard_widget(
            'wp_statistics_dashboard_widget',
            $title,
            [$this, 'render']
        );

    }

    /**
     * Fetch all widget data via a single batch query.
     *
     * @return array
     */
    private function fetchData(): array
    {
        $today      = DateRange::get('today');
        $yesterday  = DateRange::getPrevPeriod('today');
        $thisMonth  = DateRange::get('this_month');
        $periodEnd  = date('Y-m-d');
        $periodStart = date('Y-m-d', strtotime('-27 days'));

        $handler = new AnalyticsQueryHandler(true);

        $queries = [
            [
                'id'        => 'today',
                'sources'   => ['visitors', 'views'],
                'date_from' => $today['from'],
                'date_to'   => $today['to'],
                'format'    => 'flat',
            ],
            [
                'id'        => 'yesterday',
                'sources'   => ['visitors', 'views'],
                'date_from' => $yesterday['from'],
                'date_to'   => $yesterday['to'],
                'format'    => 'flat',
            ],
            [
                'id'        => 'this_month',
                'sources'   => ['visitors'],
                'date_from' => $thisMonth['from'],
                'date_to'   => $thisMonth['to'],
                'format'    => 'flat',
            ],
            [
                'id'        => 'top_pages',
                'sources'   => ['views'],
                'group_by'  => ['page'],
                'columns'   => ['page_title', 'page_uri', 'page_wp_id', 'views'],
                'date_from' => $periodStart,
                'date_to'   => $periodEnd,
                'format'    => 'table',
                'per_page'  => 5,
            ],
            [
                'id'        => 'top_referrers',
                'sources'   => ['visitors'],
                'group_by'  => ['referrer'],
                'columns'   => ['referrer_domain', 'visitors'],
                'date_from' => $periodStart,
                'date_to'   => $periodEnd,
                'format'    => 'table',
                'per_page'  => 3,
            ],
        ];

        try {
            $batch = $handler->handleBatch($queries);
            $items = $batch['items'] ?? [];
        } catch (\Exception $e) {
            $items = [];
        }

        $flat = function (string $id) use ($items): array {
            $item = $items[$id] ?? [];
            if (isset($item['items'][0])) {
                return $item['items'][0];
            }
            return [];
        };

        $rows = function (string $id) use ($items): array {
            $item = $items[$id] ?? [];
            return $item['data']['rows'] ?? [];
        };

        $todayData     = $flat('today');
        $yesterdayData  = $flat('yesterday');

        $visitorsToday    = (int) ($todayData['visitors'] ?? 0);
        $viewsToday       = (int) ($todayData['views'] ?? 0);
        $visitorsYesterday = (int) ($yesterdayData['visitors'] ?? 0);
        $viewsYesterday   = (int) ($yesterdayData['views'] ?? 0);
        $visitorsMonth    = (int) ($flat('this_month')['visitors'] ?? 0);

        // Percentage changes vs yesterday
        $visitorsChange = (int) Math::percentageChange($visitorsYesterday, $visitorsToday, 0, 'zero');
        $viewsChange    = (int) Math::percentageChange($viewsYesterday, $viewsToday, 0, 'zero');

        // Top pages
        $dashboardBase = admin_url('admin.php?page=wp-statistics');
        $topPages = [];
        foreach ($rows('top_pages') as $row) {
            $pageWpId = $row['page_wp_id'] ?? null;
            $url      = $pageWpId ? $dashboardBase . '#/content/' . $pageWpId : null;

            $topPages[] = [
                'title' => $row['page_title'] ?? $row['page_uri'] ?? __('(no title)', 'wp-statistics'),
                'views' => (int) ($row['views'] ?? 0),
                'url'   => $url,
            ];
        }

        // Top referrers
        $topReferrers = [];
        foreach ($rows('top_referrers') as $row) {
            $topReferrers[] = [
                'domain'   => $row['referrer_domain'] ?? __('(unknown)', 'wp-statistics'),
                'visitors' => (int) ($row['visitors'] ?? 0),
            ];
        }

        $hasData = $visitorsToday > 0 || $viewsToday > 0 || $visitorsMonth > 0 || !empty($topPages);

        return [
            'visitors_today'   => $visitorsToday,
            'views_today'      => $viewsToday,
            'visitors_month'   => $visitorsMonth,
            'visitors_change'  => $visitorsChange,
            'views_change'     => $viewsChange,
            'top_pages'        => $topPages,
            'top_referrers'    => $topReferrers,
            'overview_url'     => $dashboardBase . '#/overview',
            'has_data'         => $hasData,
        ];
    }

    /**
     * Render the widget content.
     */
    public function render(): void
    {
        $data = $this->fetchData();

        View::load('components/dashboard-widget/summary', [
            'metrics'       => [
                'visitors_today' => Format::compactNumber($data['visitors_today']),
                'views_today'    => Format::compactNumber($data['views_today']),
                'visitors_month' => Format::compactNumber($data['visitors_month']),
                'visitors_change' => $data['visitors_change'],
                'views_change'   => $data['views_change'],
            ],
            'top_pages'     => $data['top_pages'],
            'top_referrers' => $data['top_referrers'],
            'overview_url'  => $data['overview_url'],
            'has_data'      => $data['has_data'],
        ]);
    }

    /**
     * Enqueue the widget CSS on the dashboard page.
     */
    public function enqueueAssets(): void
    {
        if (!$this->shouldShow()) {
            return;
        }

        $screen = get_current_screen();
        if (!$screen || $screen->id !== 'dashboard') {
            return;
        }

        $baseUrl = defined('WP_STATISTICS_URL')
            ? WP_STATISTICS_URL
            : plugin_dir_url(dirname(__DIR__, 4) . '/wp-statistics.php');

        wp_enqueue_style(
            'wp-statistics-dashboard-widget',
            $baseUrl . 'resources/dashboard-widget/dashboard-widget.css',
            [],
            WP_STATISTICS_VERSION
        );
    }
}
