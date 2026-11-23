<?php

namespace WP_Statistics\Service\Admin\LicenseManagement\Plugin;

/**
 * Short, translatable sales copy for the 8 Premium add-ons, kept in one place so every upsell surface
 * says the same thing: the Premium popup, the settings banner on each add-on tab and the saved add-on
 * list the Add-ons page falls back to when the remote list can't be loaded.
 *
 * Each entry:
 *  - name      Add-on name.
 *  - headline  One line for the popup.
 *  - benefits  Three short benefits (popup).
 *  - value     One-line value for the settings banner.
 *  - chips     Three or four feature chips for the settings banner.
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
                'value'    => __('Track clicks, downloads and custom post types.', 'wp-statistics'),
                'chips'    => [
                    __('Link clicks', 'wp-statistics'),
                    __('Downloads', 'wp-statistics'),
                    __('Custom post types', 'wp-statistics'),
                    __('UTM filters', 'wp-statistics'),
                ],
                'short'    => __('Clicks, downloads, search terms, entry and exit pages.', 'wp-statistics'),
            ],
            'wp-statistics-marketing'          => [
                'name'     => __('Marketing', 'wp-statistics'),
                'headline' => __('Know which campaigns bring visitors', 'wp-statistics'),
                'benefits' => [
                    __('Campaign and UTM link reports', 'wp-statistics'),
                    __('Google Search Console keywords in WordPress', 'wp-statistics'),
                    __('Set goals and count conversions', 'wp-statistics'),
                ],
                'value'    => __('Campaigns, Google Search keywords and goals in one place.', 'wp-statistics'),
                'chips'    => [
                    __('UTM campaigns', 'wp-statistics'),
                    __('Google Search Console', 'wp-statistics'),
                    __('Goals', 'wp-statistics'),
                    __('Click events', 'wp-statistics'),
                ],
                'short'    => __('UTM campaigns, Google Search Console and goals.', 'wp-statistics'),
            ],
            'wp-statistics-mini-chart'         => [
                'name'     => __('Mini Chart', 'wp-statistics'),
                'headline' => __('Traffic at a glance on every post', 'wp-statistics'),
                'benefits' => [
                    __('Small view charts in your post lists', 'wp-statistics'),
                    __('Traffic chart in the admin bar', 'wp-statistics'),
                    __('Pick your chart type and colors', 'wp-statistics'),
                ],
                'value'    => __('Tiny traffic charts on posts, pages and the admin bar.', 'wp-statistics'),
                'chips'    => [
                    __('Post list charts', 'wp-statistics'),
                    __('Admin bar chart', 'wp-statistics'),
                    __('Chart type', 'wp-statistics'),
                    __('Colors', 'wp-statistics'),
                ],
                'short'    => __('Small traffic charts on posts and in the admin bar.', 'wp-statistics'),
            ],
            'wp-statistics-advanced-reporting' => [
                'name'     => __('Advanced Reporting', 'wp-statistics'),
                'headline' => __('Reports in your inbox, on schedule', 'wp-statistics'),
                'benefits' => [
                    __('Email reports with charts and top pages', 'wp-statistics'),
                    __('Send to any inbox, any schedule', 'wp-statistics'),
                    __('Export to CSV and PDF', 'wp-statistics'),
                ],
                'value'    => __('Scheduled email reports with charts, plus CSV and PDF.', 'wp-statistics'),
                'chips'    => [
                    __('Charts in email', 'wp-statistics'),
                    __('Any schedule', 'wp-statistics'),
                    __('Any inbox', 'wp-statistics'),
                    __('CSV & PDF', 'wp-statistics'),
                ],
                'short'    => __('Scheduled email reports with charts, CSV and PDF.', 'wp-statistics'),
            ],
            'wp-statistics-realtime-stats'     => [
                'name'     => __('Real-Time', 'wp-statistics'),
                'headline' => __('See who is on your site now', 'wp-statistics'),
                'benefits' => [
                    __('Online visitors and what they view', 'wp-statistics'),
                    __('Live chart and map', 'wp-statistics'),
                    __('Updates without a page refresh', 'wp-statistics'),
                ],
                'value'    => __('Watch visitors and views as they happen.', 'wp-statistics'),
                'chips'    => [
                    __('Online users', 'wp-statistics'),
                    __('Live chart', 'wp-statistics'),
                    __('Live map', 'wp-statistics'),
                    __('No refresh', 'wp-statistics'),
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
                'value'    => __('Show your stats on your site with blocks and widgets.', 'wp-statistics'),
                'chips'    => [
                    __('Gutenberg blocks', 'wp-statistics'),
                    __('Theme widgets', 'wp-statistics'),
                    __('Top pages', 'wp-statistics'),
                    __('Caching', 'wp-statistics'),
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
                'value'    => __('Your brand instead of ours, in menus and the Overview.', 'wp-statistics'),
                'chips'    => [
                    __('White label', 'wp-statistics'),
                    __('Your branding', 'wp-statistics'),
                    __('Menu control', 'wp-statistics'),
                    __('Overview widget', 'wp-statistics'),
                ],
                'short'    => __('White label: your brand, your menus.', 'wp-statistics'),
            ],
            'wp-statistics-rest-api'           => [
                'name'     => __('REST API', 'wp-statistics'),
                'headline' => __('Your stats in any app or AI agent', 'wp-statistics'),
                'benefits' => [
                    __('Connect ChatGPT, Claude and other AI agents', 'wp-statistics'),
                    __('Endpoints for visitors, pages and referrers', 'wp-statistics'),
                    __('Build custom dashboards and automations', 'wp-statistics'),
                ],
                'value'    => __('Read your stats from other apps and AI agents.', 'wp-statistics'),
                'chips'    => [
                    __('AI agents (MCP)', 'wp-statistics'),
                    __('Visitors & pages', 'wp-statistics'),
                    __('Referrers & search', 'wp-statistics'),
                    __('Summary', 'wp-statistics'),
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
     * Add-on page on wp-statistics.com.
     *
     * @param string $slug     Add-on slug.
     * @param string $campaign utm_campaign value.
     *
     * @return string
     */
    public static function getProductUrl($slug, $campaign = 'pop-up-premium')
    {
        return add_query_arg([
            'utm_source'   => 'wp-statistics',
            'utm_medium'   => 'link',
            'utm_campaign' => $campaign,
        ], WP_STATISTICS_SITE_URL . '/add-ons/' . $slug . '/');
    }

    /**
     * Pricing page on wp-statistics.com.
     *
     * @param string $campaign utm_campaign value.
     *
     * @return string
     */
    public static function getPricingUrl($campaign = 'pop-up-premium')
    {
        return add_query_arg([
            'utm_source'   => 'wp-statistics',
            'utm_medium'   => 'link',
            'utm_campaign' => $campaign,
        ], WP_STATISTICS_SITE_URL . '/pricing/');
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
