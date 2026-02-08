<?php

namespace WP_Statistics\Service\Admin\AdminBar;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Admin\AccessControl\AccessLevel;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Utils\Format;
use WP_Statistics\Utils\User;

/**
 * Admin Bar Statistics Widget.
 *
 * Adds a statistics summary item to the WordPress admin bar
 * with a hover popup showing today's visitors, views, and online users.
 *
 * @since 15.0.0
 */
class AdminBarManager
{
    /**
     * Popup HTML stored during admin_bar_menu, rendered in footer.
     *
     * @var string
     */
    private $popupHtml = '';

    /**
     * Whether the admin bar node was added (guards footer callbacks).
     *
     * @var bool
     */
    private $isActive = false;

    /**
     * Which tab is active by default (set after filter in renderTabs).
     *
     * @var string
     */
    private $defaultTab = 'overview';

    public function __construct()
    {
        add_action('admin_bar_menu', [$this, 'addStatsNode'], 100);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('admin_footer', [$this, 'renderPopup'], 100);
        add_action('wp_footer', [$this, 'renderPopup'], 100);
    }

    /**
     * Whether the admin bar stats should show.
     *
     * @return bool
     */
    private function shouldShow(): bool
    {
        if (!Option::getValue('menu_bar', true)) {
            return false;
        }

        if (!User::hasAccessLevel(AccessLevel::OWN_CONTENT)) {
            return false;
        }

        return true;
    }

    /**
     * Fetch stats data using AnalyticsQueryHandler batch queries.
     *
     * @return array
     */
    private function fetchStatsData(): array
    {
        $today       = date('Y-m-d');
        $periodStart = date('Y-m-d', strtotime('-27 days'));
        $prevEnd     = date('Y-m-d', strtotime('-28 days'));
        $prevStart   = date('Y-m-d', strtotime('-55 days'));

        $handler = new AnalyticsQueryHandler(true);

        $queries = [
            [
                'id'        => 'period',
                'sources'   => ['visitors', 'views', 'bounce_rate'],
                'date_from' => $periodStart,
                'date_to'   => $today,
                'format'    => 'flat',
            ],
            [
                'id'        => 'prev_period',
                'sources'   => ['visitors', 'views', 'bounce_rate'],
                'date_from' => $prevStart,
                'date_to'   => $prevEnd,
                'format'    => 'flat',
            ],
            [
                'id'        => 'sparkline',
                'sources'   => ['visitors'],
                'group_by'  => ['date'],
                'date_from' => $periodStart,
                'date_to'   => $today,
                'format'    => 'table',
                'per_page'  => 28,
            ],
            [
                'id'        => 'top_content',
                'sources'   => ['views'],
                'group_by'  => ['page'],
                'columns'   => ['page_title', 'page_uri', 'page_wp_id', 'resource_id', 'views'],
                'date_from' => $periodStart,
                'date_to'   => $today,
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

        $periodData   = $flat('period');
        $prevData     = $flat('prev_period');
        $visitors     = (int) ($periodData['visitors'] ?? 0);
        $views        = (int) ($periodData['views'] ?? 0);
        $bounceRate   = round((float) ($periodData['bounce_rate'] ?? 0), 1);
        $online       = (int) wp_statistics_useronline();

        // Comparison: 28d vs previous 28d
        $prevVisitors = (int) ($prevData['visitors'] ?? 0);
        $comparison   = $this->calcComparison($visitors, $prevVisitors);

        // Sparkline: 28 data points
        $sparklineRows = $rows('sparkline');
        $sparkline     = [];
        foreach ($sparklineRows as $row) {
            $sparkline[] = (int) ($row['visitors'] ?? 0);
        }
        $sparkline = $this->padSparkline($sparkline, 28);

        // Top content: 3 rows (28-day data)
        $topContentRows = $rows('top_content');
        $topContent     = [];
        $dashboardBase  = admin_url('admin.php?page=wp-statistics');
        foreach ($topContentRows as $row) {
            $pageWpId   = $row['page_wp_id'] ?? null;
            $resourceId = $row['resource_id'] ?? null;
            $url        = null;
            if ($pageWpId) {
                $url = $dashboardBase . '#/content/' . $pageWpId;
            } elseif ($resourceId) {
                $url = $dashboardBase . '#/url/' . $resourceId;
            }
            $topContent[] = [
                'title' => $row['page_title'] ?? $row['page_uri'] ?? __('(no title)', 'wp-statistics'),
                'views' => (int) ($row['views'] ?? 0),
                'url'   => $url,
            ];
        }

        $data = [
            'bar_count'           => $views,
            'visitors'            => $visitors,
            'views'               => $views,
            'bounce_rate'         => $bounceRate,
            'prev_views'          => (int) ($prevData['views'] ?? 0),
            'prev_bounce_rate'    => round((float) ($prevData['bounce_rate'] ?? 0), 1),
            'visitors_comparison' => $comparison,
            'online'              => $online,
            'sparkline'           => $sparkline,
            'top_content'         => $topContent,
        ];

        /**
         * Filters the admin bar stats data before rendering.
         *
         * @param array $data Stats data array.
         */
        return apply_filters('wp_statistics_admin_bar_data', $data);
    }

    /**
     * Calculate comparison string between two periods.
     *
     * @param int $current  Current period value.
     * @param int $previous Previous period value.
     * @return string e.g. "+12%" or "" if no meaningful comparison.
     */
    private function calcComparison(int $current, int $previous): string
    {
        if ($previous === 0 || $current === $previous) {
            return '';
        }

        $change  = (($current - $previous) / $previous) * 100;
        $percent = (int) round($change);

        if ($percent === 0) {
            return '';
        }

        $sign = $percent > 0 ? '+' : '';

        return $sign . $percent . '%';
    }

    /**
     * Build the popup HTML with tabbed structure.
     *
     * @param array $data Stats data.
     * @return string
     */
    private function getPopupHtml(array $data): string
    {
        $dashboardUrl = admin_url('admin.php?page=wp-statistics#/overview');

        ob_start();
        ?>
        <div class="wps-popup" id="wps-popup" role="tooltip">
            <?php echo $this->renderTabs(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <div class="wps-content" id="wps-tab-overview"<?php echo $this->defaultTab !== 'overview' ? ' style="display:none"' : ''; ?>>
                <?php echo $this->renderHero($data); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <div class="wps-sparkline" id="wps-sparkline-overview"></div>
                <div class="wps-metrics">
                    <?php
                    $viewsTrend = null;
                    $viewsTrendClass = null;
                    if (!empty($data['views_trend']) && $data['views_trend']['percent'] > 0) {
                        $sign = $data['views_trend']['direction'] === 'up' ? '+' : '-';
                        $viewsTrend = $sign . $data['views_trend']['percent'] . '%';
                        $viewsTrendClass = $data['views_trend']['direction'] === 'up' ? 'wps-metric-trend--positive' : 'wps-metric-trend--negative';
                    }
                    echo $this->renderMetric(
                        number_format_i18n($data['views']),
                        __('Views', 'wp-statistics'),
                        $viewsTrend,
                        $viewsTrendClass
                    ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

                    $bounceTrend = null;
                    $bounceTrendClass = null;
                    if (!empty($data['bounce_trend']['text'])) {
                        $bounceTrend = $data['bounce_trend']['text'];
                        $bounceTrendClass = $data['bounce_trend']['class'];
                    }
                    echo $this->renderMetric(
                        $data['bounce_rate'] . '%',
                        __('Bounce rate', 'wp-statistics'),
                        $bounceTrend,
                        $bounceTrendClass
                    ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    ?>
                    <?php
                    /**
                     * Fires inside the metrics row to allow adding more metric cells.
                     *
                     * @param array $data Stats data array.
                     */
                    do_action('wp_statistics_admin_bar_overview_metrics', $data);
                    ?>
                </div>
                <?php
                /**
                 * Fires after the metrics row for additional overview sections.
                 *
                 * @param array $data Stats data array.
                 */
                do_action('wp_statistics_admin_bar_overview_sections', $data);

                if (!empty($data['top_content'])) :
                ?>
                <div class="wps-section">
                    <div class="wps-section-label"><?php esc_html_e('Top content', 'wp-statistics'); ?></div>
                    <?php foreach ($data['top_content'] as $content) : ?>
                        <?php echo $this->renderContentRow($content['title'], $content['views'], $content['url'] ?? null); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php echo $this->renderFooter($dashboardUrl); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
            <div class="wps-content" id="wps-tab-this-page"<?php echo $this->defaultTab !== 'this-page' ? ' style="display:none"' : ''; ?>>
                <?php
                /**
                 * Filters the This Page tab content.
                 * Default: fallback message.
                 *
                 * @param string $html Default HTML.
                 * @param array  $data Stats data array.
                 */
                echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    'wp_statistics_admin_bar_this_page_content',
                    '<div class="wps-fallback"><p>' . esc_html__('Navigate to a post, page, category, or author archive to see stats for that page.', 'wp-statistics') . '</p></div>',
                    $data
                );
                ?>
            </div>
            <script>window.wpsAdminBarData = <?php echo wp_json_encode($data, JSON_HEX_TAG); ?>;</script>
        </div>
        <?php
        $html = ob_get_clean();

        /**
         * Filters the complete admin bar popup HTML.
         *
         * @param string $html Popup HTML.
         * @param array  $data Stats data.
         */
        return apply_filters('wp_statistics_admin_bar_popup_html', $html, $data);
    }

    /**
     * Render the tab bar.
     *
     * @return string
     */
    private function renderTabs(): string
    {
        $tabs = [
            [
                'id'       => 'overview',
                'label'    => __('Overview', 'wp-statistics'),
                'active'   => true,
                'disabled' => false,
            ],
            [
                'id'       => 'this-page',
                'label'    => __('This Page', 'wp-statistics'),
                'active'   => false,
                'disabled' => true,
            ],
        ];

        /**
         * Filters the admin bar tab definitions.
         * Premium can enable the "This Page" tab by setting disabled to false.
         *
         * @param array $tabs Tab definitions.
         */
        $tabs = apply_filters('wp_statistics_admin_bar_tabs', $tabs);

        foreach ($tabs as $tab) {
            if (!empty($tab['active'])) {
                $this->defaultTab = $tab['id'];
                break;
            }
        }

        $html = '<div class="wps-tabs">';
        foreach ($tabs as $tab) {
            $classes = 'wps-tab';
            if (!empty($tab['active'])) {
                $classes .= ' active';
            }
            if (!empty($tab['disabled'])) {
                $classes .= ' disabled';
            }
            $html .= '<button class="' . esc_attr($classes) . '" data-tab="' . esc_attr($tab['id']) . '">';
            $html .= esc_html($tab['label']);
            $html .= '</button>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Render the hero section with visitor count (left) and online count (right).
     *
     * @param array $data Stats data.
     * @return string
     */
    private function renderHero(array $data): string
    {
        $html = '<div class="wps-hero">';

        // Left side: number + sub label
        $html .= '<div>';
        $html .= '<div class="wps-hero-number">' . esc_html(number_format_i18n($data['visitors'])) . '</div>';
        $html .= '<div class="wps-hero-sub">';
        $html .= '<span class="wps-hero-label">' . esc_html__('visitors', 'wp-statistics') . ' · ' . esc_html__('last 28 days', 'wp-statistics') . '</span>';
        if (!empty($data['visitors_comparison'])) {
            $trendClass = strpos($data['visitors_comparison'], '+') === 0 ? 'wps-trend--up' : 'wps-trend--down';
            $html .= ' <span class="wps-trend ' . esc_attr($trendClass) . '">' . esc_html($data['visitors_comparison']) . '</span>';
        }
        $html .= '</div>';
        $html .= '</div>';

        // Right side: online count
        if ($data['online'] > 0) {
            $html .= '<div class="wps-hero-online">';
            $html .= '<span class="wps-online-dot"></span> ';
            $html .= esc_html(number_format_i18n($data['online']));
            $html .= ' ' . esc_html__('online', 'wp-statistics');
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render a single metric cell for the horizontal metrics row.
     *
     * @param string      $value      Display value (e.g. "8,392").
     * @param string      $label      Label (e.g. "Views").
     * @param string|null $trend      Trend text (e.g. "+8%") or null.
     * @param string|null $trendClass CSS class for trend direction.
     * @return string
     */
    private function renderMetric(string $value, string $label, ?string $trend, ?string $trendClass): string
    {
        $html = '<div class="wps-metric">';
        $html .= '<div class="wps-metric-value">' . esc_html($value) . '</div>';
        $html .= '<div class="wps-metric-sub">';
        $html .= '<span class="wps-metric-label">' . esc_html($label) . '</span>';
        if ($trend) {
            $html .= ' <span class="wps-metric-trend ' . esc_attr($trendClass ?? '') . '">' . esc_html($trend) . '</span>';
        }
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render a top content row with optional link.
     *
     * @param string      $title Page title.
     * @param int         $views View count.
     * @param string|null $url   Optional link to single content report.
     * @return string
     */
    private function renderContentRow(string $title, int $views, ?string $url = null): string
    {
        $html = '<div class="wps-content-row">';
        if ($url) {
            $html .= '<a class="wps-content-title" href="' . esc_url($url) . '">' . esc_html($title) . '</a>';
        } else {
            $html .= '<span class="wps-content-title">' . esc_html($title) . '</span>';
        }
        $html .= '<span class="wps-content-number">' . esc_html(number_format_i18n($views)) . '</span>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render the footer with logo placeholder, upgrade nudge, and dashboard link.
     *
     * @param string $dashboardUrl Dashboard URL.
     * @return string
     */
    private function renderFooter(string $dashboardUrl): string
    {
        $html = '<div class="wps-footer">';

        $footerContent = '<span class="wps-footer-nudge">';
        $footerContent .= '<a href="' . esc_url(admin_url('admin.php?page=wp-statistics#/premium')) . '">';
        $footerContent .= esc_html__('Unlock insights', 'wp-statistics');
        $footerContent .= '</a>';
        $footerContent .= '</span>';
        $footerContent .= '<a class="wps-footer-link" href="' . esc_url($dashboardUrl) . '">';
        $footerContent .= esc_html__('Dashboard', 'wp-statistics');
        $footerContent .= ' <span class="wps-footer-arrow">&rarr;</span>';
        $footerContent .= '</a>';

        /**
         * Filters the footer content. Premium replaces the nudge with a logo.
         *
         * @param string $footerContent Footer HTML.
         */
        $html .= apply_filters('wp_statistics_admin_bar_footer', $footerContent);

        $html .= '</div>';

        return $html;
    }

    /**
     * Pad sparkline data to the desired length.
     *
     * @param array $data   Data points.
     * @param int   $length Desired length.
     * @return array
     */
    private function padSparkline(array $data, int $length): array
    {
        while (count($data) < $length) {
            array_unshift($data, 0);
        }

        return array_slice($data, -$length);
    }

    /**
     * Add the stats node to the admin bar.
     *
     * @param \WP_Admin_Bar $wp_admin_bar
     */
    public function addStatsNode($wp_admin_bar): void
    {
        if (!$this->shouldShow()) {
            return;
        }

        $this->isActive = true;

        $data = $this->fetchStatsData();

        $this->popupHtml = $this->getPopupHtml($data);

        $icon = '<svg class="wps-bar-icon" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">'
            . '<rect x="1" y="10" width="3.5" height="7" rx="1" fill="#c3c4c7" opacity="0.5"/>'
            . '<rect x="7.25" y="6" width="3.5" height="11" rx="1" fill="#c3c4c7" opacity="0.7"/>'
            . '<rect x="13.5" y="1" width="3.5" height="16" rx="1" fill="#c3c4c7"/>'
            . '</svg>';

        $count = '<span class="wps-bar-count">' . esc_html(Format::compactNumber($data['bar_count'])) . '</span>'
    . '<span class="wps-bar-label">' . esc_html__('Views', 'wp-statistics') . '</span>';

        $wp_admin_bar->add_node([
            'id'    => 'wp-statistics',
            'title' => $icon . $count,
            'href'  => false,
            'meta'  => [
                'class'    => 'wps-admin-bar-stats',
                'tabindex' => 0,
            ],
        ]);
    }

    /**
     * Render popup HTML as a direct child of body (portal pattern).
     */
    public function renderPopup(): void
    {
        if (!$this->isActive || empty($this->popupHtml)) {
            return;
        }

        echo $this->popupHtml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML escaped in getPopupHtml
        $this->popupHtml = '';
    }

    /**
     * Enqueue admin bar CSS and JS.
     */
    public function enqueueAssets(): void
    {
        if (!$this->shouldShow()) {
            return;
        }

        $baseUrl = defined('WP_STATISTICS_URL') ? WP_STATISTICS_URL : plugin_dir_url(dirname(__DIR__, 4) . '/wp-statistics.php');

        wp_enqueue_style(
            'wp-statistics-admin-bar',
            $baseUrl . 'resources/admin-bar/admin-bar.css',
            [],
            WP_STATISTICS_VERSION
        );

        wp_enqueue_script(
            'wp-statistics-admin-bar',
            $baseUrl . 'resources/admin-bar/admin-bar.js',
            [],
            WP_STATISTICS_VERSION,
            true
        );
    }
}
