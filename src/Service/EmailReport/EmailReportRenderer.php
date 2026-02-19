<?php

namespace WP_Statistics\Service\EmailReport;

use WP_Statistics\Components\View;

/**
 * Assembles email report sections into HTML body content.
 *
 * @since 15.0.0
 */
class EmailReportRenderer
{
    /**
     * Default section order for free tier.
     *
     * @var string[]
     */
    protected $sections = ['quick-stats', 'trend-chart', 'top-pages', 'top-referrers'];

    /**
     * Render the full email body content from report data.
     *
     * @param array $data   Report data from EmailReportDataProvider.
     * @param array $colors Color palette.
     * @return string HTML content.
     */
    public function render(array $data, array $colors = []): string
    {
        $colors = wp_parse_args($colors, $this->getDefaultColors());

        /**
         * Filter the email report sections list.
         *
         * @since 15.0.0
         * @param string[] $sections Section slugs.
         * @param array    $data     Report data.
         */
        $sections = apply_filters('wp_statistics_email_report_sections', $this->sections, $data);
        $sections = is_array($sections) ? $sections : $this->sections;

        $content = '';
        foreach ($sections as $section) {
            $html = $this->renderSection($section, $data, $colors);
            if (!empty($html)) {
                $content .= $html;
            }
        }

        return $content;
    }

    /**
     * Render a single report section.
     *
     * @param string $section Section slug.
     * @param array  $data    Report data.
     * @param array  $colors  Color palette.
     * @return string HTML or empty string.
     */
    protected function renderSection(string $section, array $data, array $colors): string
    {
        /**
         * Filter the view path for a specific email report section.
         * Return a view name or null to use the default.
         *
         * @since 15.0.0
         * @param string|null $viewPath Custom view path or null for default.
         * @param array       $data     Report data.
         * @param array       $colors   Color palette.
         */
        $viewPath = apply_filters("wp_statistics_email_report_section_{$section}_view", null, $data, $colors);

        $vars = array_merge($colors, [
            'show_comparison' => apply_filters('wp_statistics_email_report_show_comparison', true),
        ]);

        switch ($section) {
            case 'quick-stats':
                $vars['kpis'] = $data['kpis'] ?? [];
                return $this->loadView($viewPath ?? 'emails/partials/kpi-row', $vars);

            case 'engagement-overview':
                $vars['title'] = __('Engagement Overview', 'wp-statistics');
                $vars['kpis']  = $data['engagement_kpis'] ?? [];
                return $this->loadView($viewPath ?? 'emails/partials/kpi-row', $vars);

            case 'trend-chart':
                $vars['title']      = __('Daily Visitors', 'wp-statistics');
                $vars['chart_data'] = $data['daily_chart'] ?? [];
                return $this->loadView($viewPath ?? 'emails/partials/bar-chart', $vars);

            case 'top-pages':
                return $this->renderDataTableSection(
                    $viewPath,
                    $vars,
                    __('Top Pages', 'wp-statistics'),
                    __('Page', 'wp-statistics'),
                    __('Views', 'wp-statistics'),
                    $data['top_pages'] ?? []
                );

            case 'top-referrers':
                return $this->renderDataTableSection(
                    $viewPath,
                    $vars,
                    __('Top Referrers', 'wp-statistics'),
                    __('Source', 'wp-statistics'),
                    __('Visitors', 'wp-statistics'),
                    $data['top_referrers'] ?? []
                );

            case 'top-entry-pages':
                return $this->renderDataTableSection(
                    $viewPath,
                    $vars,
                    __('Top Entry Pages', 'wp-statistics'),
                    __('Page', 'wp-statistics'),
                    __('Sessions', 'wp-statistics'),
                    $data['top_entry_pages'] ?? []
                );

            case 'top-exit-pages':
                return $this->renderDataTableSection(
                    $viewPath,
                    $vars,
                    __('Top Exit Pages', 'wp-statistics'),
                    __('Page', 'wp-statistics'),
                    __('Sessions', 'wp-statistics'),
                    $data['top_exit_pages'] ?? []
                );

            case 'top-countries':
                return $this->renderDataTableSection(
                    $viewPath,
                    $vars,
                    __('Top Countries', 'wp-statistics'),
                    __('Country', 'wp-statistics'),
                    __('Visitors', 'wp-statistics'),
                    $data['top_countries'] ?? []
                );

            case 'search-terms':
                return $this->renderDataTableSection(
                    $viewPath,
                    $vars,
                    __('Top Search Terms', 'wp-statistics'),
                    __('Term', 'wp-statistics'),
                    __('Searches', 'wp-statistics'),
                    $data['search_terms'] ?? []
                );

            case 'devices':
                $vars['title']            = __('Device Breakdown', 'wp-statistics');
                $vars['device_breakdown'] = $data['device_breakdown'] ?? [];
                return $this->loadView($viewPath ?? 'emails/partials/device-breakdown', $vars);

            default:
                // Premium or custom sections handle their own rendering via filter
                if ($viewPath) {
                    return $this->loadView($viewPath, array_merge($vars, ['data' => $data]));
                }
                return '';
        }
    }

    /**
     * Load a view template and return as string.
     *
     * @param string $view View name relative to views/ directory.
     * @param array  $vars Template variables.
     * @return string
     */
    private function loadView(string $view, array $vars): string
    {
        return View::load($view, $vars, true) ?: '';
    }

    /**
     * Render a generic data-table section.
     *
     * @param string|null $viewPath     Optional override view path.
     * @param array       $vars         Shared view variables.
     * @param string      $title        Section title.
     * @param string      $columnLabel  Label column header.
     * @param string      $valueLabel   Metric column header.
     * @param array       $rows         Section rows.
     * @return string
     */
    private function renderDataTableSection(?string $viewPath, array $vars, string $title, string $columnLabel, string $valueLabel, array $rows): string
    {
        $vars['title']        = $title;
        $vars['column_label'] = $columnLabel;
        $vars['value_label']  = $valueLabel;
        $vars['rows']         = $rows;

        return $this->loadView($viewPath ?? 'emails/partials/data-table', $vars);
    }

    /**
     * Get default color palette (Ocean Blue).
     *
     * @return array
     */
    public function getDefaultColors(): array
    {
        return [
            'primary_color'  => '#1e40af',
            'positive_color' => '#059669',
            'negative_color' => '#dc2626',
            'muted_color'    => '#6b7280',
        ];
    }
}
