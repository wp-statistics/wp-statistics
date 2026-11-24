<?php

namespace WP_Statistics\Service\Admin\LicenseManagement\Plugin;

use WP_Statistics\Service\Admin\LicenseManagement\UpsellLink;

/**
 * Short, translatable sales copy for the 8 Premium add-ons, kept in one place so every upsell surface
 * says the same thing: the Premium popup, the settings card on each add-on tab, the add-on level lock
 * pages (Marketing campaigns, Mini Chart, Real-Time) and the saved add-on list the Add-ons page falls
 * back to when the remote list can't be loaded.
 *
 * Each entry:
 *  - name      Add-on name.
 *  - headline  One headline (popup, settings card, add-on lock pages).
 *  - benefits  Three short benefits (same surfaces).
 *  - short     Short description for the saved add-on list.
 */
class AddonCatalog
{
    /**
     * Add-on slugs in display order.
     */
    const SLUGS = [
        'wp-statistics-data-plus',
        'wp-statistics-marketing',
        'wp-statistics-mini-chart',
        'wp-statistics-advanced-reporting',
        'wp-statistics-realtime-stats',
        'wp-statistics-widgets',
        'wp-statistics-customization',
        'wp-statistics-rest-api',
    ];

    /**
     * Copy for every add-on, keyed by slug.
     *
     * @return array<string, array>
     */
    public static function all()
    {
        return [
            'wp-statistics-data-plus'          => [
                'name'     => __('Data Plus', 'wp-statistics'),
                'headline' => __('See what visitors click and download', 'wp-statistics'),
                'benefits' => [
                    __('Track link clicks and file downloads', 'wp-statistics'),
                    __('Search terms, entry and exit pages', 'wp-statistics'),
                    __('Filter any report by UTM or query', 'wp-statistics'),
                ],
                'short'    => __('Clicks, downloads, search terms, entry and exit pages.', 'wp-statistics'),
            ],
            'wp-statistics-marketing'          => [
                'name'     => __('Marketing', 'wp-statistics'),
                'headline' => __('See which campaigns bring your visitors', 'wp-statistics'),
                'benefits' => [
                    __('Campaign and UTM link reports', 'wp-statistics'),
                    __('Google Search keywords, clicks and impressions', 'wp-statistics'),
                    __('Goals that count your conversions', 'wp-statistics'),
                ],
                'short'    => __('UTM campaigns, Google Search Console and goals.', 'wp-statistics'),
            ],
            'wp-statistics-mini-chart'         => [
                'name'     => __('Mini Chart', 'wp-statistics'),
                'headline' => __('Traffic for every post at a glance', 'wp-statistics'),
                'benefits' => [
                    __('Small charts in your posts list', 'wp-statistics'),
                    __('Traffic chart in the admin bar', 'wp-statistics'),
                    __('Choose chart type and colors', 'wp-statistics'),
                ],
                'short'    => __('Small traffic charts on posts and in the admin bar.', 'wp-statistics'),
            ],
            'wp-statistics-advanced-reporting' => [
                'name'     => __('Advanced Reporting', 'wp-statistics'),
                'headline' => __('Schedule reports, export CSV and PDF', 'wp-statistics'),
                'benefits' => [
                    __('Email reports to any inbox, on schedule', 'wp-statistics'),
                    __('Export any report table to CSV', 'wp-statistics'),
                    __('Save any report page as PDF', 'wp-statistics'),
                ],
                'short'    => __('Scheduled email reports, CSV and PDF export.', 'wp-statistics'),
            ],
            'wp-statistics-realtime-stats'     => [
                'name'     => __('Real-Time Stats', 'wp-statistics'),
                'headline' => __('Watch your site live', 'wp-statistics'),
                'benefits' => [
                    __('Visitors online right now', 'wp-statistics'),
                    __('Live views chart and map', 'wp-statistics'),
                    __('Updates without a page refresh', 'wp-statistics'),
                ],
                'short'    => __('Live visitors, views, chart and map.', 'wp-statistics'),
            ],
            'wp-statistics-widgets'            => [
                'name'     => __('Widgets', 'wp-statistics'),
                'headline' => __('Show your stats to your visitors', 'wp-statistics'),
                'benefits' => [
                    __('Gutenberg blocks and theme widgets', 'wp-statistics'),
                    __('Traffic, top pages, browsers and more', 'wp-statistics'),
                    __('Cached so your pages stay fast', 'wp-statistics'),
                ],
                'short'    => __('Stats blocks and widgets for your site.', 'wp-statistics'),
            ],
            'wp-statistics-customization'      => [
                'name'     => __('Customization', 'wp-statistics'),
                'headline' => __('Make WP Statistics your own brand', 'wp-statistics'),
                'benefits' => [
                    __('Replace our branding with yours', 'wp-statistics'),
                    __('Choose which menus people see', 'wp-statistics'),
                    __('Add your own Overview widget', 'wp-statistics'),
                ],
                'short'    => __('White label: your brand, your menus.', 'wp-statistics'),
            ],
            'wp-statistics-rest-api'           => [
                'name'     => __('REST API', 'wp-statistics'),
                'headline' => __('Your stats in any app or AI agent', 'wp-statistics'),
                'benefits' => [
                    __('Connect ChatGPT, Claude and other AI agents (MCP)', 'wp-statistics'),
                    __('Endpoints for visitors, pages and referrers', 'wp-statistics'),
                    __('Build custom dashboards and automations', 'wp-statistics'),
                ],
                'short'    => __('REST endpoints and MCP for AI agents.', 'wp-statistics'),
            ],
        ];
    }

    /**
     * Copy for one add-on.
     *
     * @param string $slug Add-on slug, e.g. "wp-statistics-data-plus".
     *
     * @return array|null
     */
    public static function get($slug)
    {
        $all = self::all();

        return $all[$slug] ?? null;
    }

    /**
     * Add-on page on wp-statistics.com ("Learn more").
     *
     * @deprecated Use {@see UpsellLink::addon()}, which also tags the surface and the button.
     *
     * @param string $slug    Add-on slug.
     * @param string $surface utm_medium.
     *
     * @return string
     */
    public static function getProductUrl($slug, $surface = 'link')
    {
        return UpsellLink::addon($slug, $surface);
    }

    /**
     * Pricing page on wp-statistics.com ("Upgrade to Premium").
     *
     * @deprecated Use {@see UpsellLink::pricing()}, which also tags the surface and the add-on.
     *
     * @param string $surface utm_medium.
     *
     * @return string
     */
    public static function getPricingUrl($surface = 'link')
    {
        return UpsellLink::pricing($surface);
    }

    /**
     * Monochrome line icon for an add-on (24x24 grid, stroke = currentColor), used by the popup switcher and
     * the settings card kicker. Decorative: hidden from screen readers.
     *
     * @param string $slug  Add-on slug.
     * @param int    $size  Width and height in px.
     * @param string $class CSS class.
     *
     * @return string SVG markup, or an empty string for an unknown slug.
     */
    public static function getLineIcon($slug, $size = 18, $class = '')
    {
        $paths = [
            'wp-statistics-data-plus'          => '<path d="M5 4l5.5 15 2.2-6.3L19 10.5z"/><path d="M13 13l5 5"/>',
            'wp-statistics-marketing'          => '<circle cx="12" cy="12" r="8.5"/><circle cx="12" cy="12" r="4.5"/><circle cx="12" cy="12" r=".9" fill="currentColor"/>',
            'wp-statistics-mini-chart'         => '<path d="M4 19h16"/><path d="M5 15l4.5-4.5 3.5 3 6-6.5"/><path d="M15 7h4v4"/>',
            'wp-statistics-advanced-reporting' => '<rect x="3.5" y="5.5" width="17" height="13" rx="2"/><path d="M4 7.5l8 6 8-6"/>',
            'wp-statistics-realtime-stats'     => '<path d="M3 12h4l2.5-6 5 12 2.5-6h4"/>',
            'wp-statistics-widgets'            => '<rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="13" y="4" width="7" height="7" rx="1.5"/><rect x="4" y="13" width="7" height="7" rx="1.5"/><rect x="13" y="13" width="7" height="7" rx="1.5"/>',
            'wp-statistics-customization'      => '<path d="M4 7h9M17 7h3M4 17h3M11 17h9"/><circle cx="15" cy="7" r="2"/><circle cx="9" cy="17" r="2"/>',
            'wp-statistics-rest-api'           => '<path d="M8.5 7.5 4 12l4.5 4.5M15.5 7.5 20 12l-4.5 4.5M13.5 5.5l-3 13"/>',
        ];

        if (!isset($paths[$slug])) {
            return '';
        }

        $size = (int)$size;

        return sprintf(
            '<svg class="%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
            esc_attr($class),
            $size,
            $paths[$slug]
        );
    }

    /**
     * Add-on icon shipped with the plugin, or an empty string.
     *
     * @param string $slug Add-on slug.
     *
     * @return string
     */
    public static function getIconUrl($slug)
    {
        $path = 'assets/images/add-ons/' . sanitize_file_name($slug) . '.svg';

        return file_exists(WP_STATISTICS_DIR . $path) ? WP_STATISTICS_URL . $path : '';
    }
}
