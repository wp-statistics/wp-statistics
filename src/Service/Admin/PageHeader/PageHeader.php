<?php

namespace WP_Statistics\Service\Admin\PageHeader;

use WP_STATISTICS\Menus;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * One page header for every single (detail) report.
 *
 * Line 1: breadcrumb (section / list). Line 2: short title with an optional icon, tag and ↗ link.
 * Line 3: facts line. Filters and export stay on the right (layout/title.php).
 *
 * Header array (all keys optional except title):
 *  - crumbs     array  [['label' => 'Devices', 'url' => '...', 'from' => [['tab' => 'browsers']]], ...]
 *                      `from` lists the list views (query args) whose URL replaces the crumb link when the user came
 *                      from there, so the crumb goes back to the same filtered list. `true` = any non-single view of
 *                      the same admin page. `labels` (tab => label) renames the crumb to the list the user came from.
 *  - title      string Plain text.
 *  - icon       array  ['src' => url, 'alt' => '', 'shape' => 'flag'|'logo'|'avatar']
 *  - tag        string Small grey label after the title (role, "Category").
 *  - link       string Live URL, printed as the shared ↗ new-tab link after the title.
 *  - link_label string Accessible name for the ↗ link.
 *  - facts      array  [['text' => '', 'url' => '', 'icon' => url, 'shape' => 'flag'|'logo', 'tag' => bool, 'title' => '', 'svg' => 'clock'|'user', 'target' => '_blank'], ...]
 *  - tooltip    string
 *
 * Views pass the header as `pageHeader`. Pages rendered by add-ons (Data Plus single country, single author, trackers;
 * Marketing goals) keep passing the old `title` / `backUrl` / `backTitle` args: known contexts are built here
 * ({@see SingleHeaders::forRequest()}), anything else gets a generic crumb from `backUrl`.
 * Add-ons can change the result with the `wp_statistics_page_header` filter.
 */
class PageHeader
{
    /**
     * Back titles that only say "go back" and so cannot name a crumb.
     */
    private static function genericBackTitles()
    {
        return [
            'Previous Page',
            'Back to Previous Page',
            __('Previous Page', 'wp-statistics'),
            __('Back to Previous Page', 'wp-statistics'),
        ];
    }

    /**
     * Build the header for the args a view passed to layout/title.php.
     *
     * @param array $args Template args.
     * @return array|null Null keeps the old title markup (pages without a back link).
     */
    public static function resolve($args)
    {
        $args   = is_array($args) ? $args : [];
        $header = null;

        if (!empty($args['pageHeader']) && is_array($args['pageHeader'])) {
            $header = $args['pageHeader'];
        }

        if ($header === null) {
            $header = SingleHeaders::forRequest($args);
        }

        if ($header === null && !empty($args['backUrl'])) {
            $header = self::fromLegacyArgs($args);
        }

        /**
         * Change the shared page header of a single report.
         *
         * @param array|null $header Header array (see PageHeader), null for the old title markup.
         * @param array $args Template args passed to layout/title.php.
         */
        $header = apply_filters('wp_statistics_page_header', $header, $args);

        if (empty($header) || !is_array($header)) {
            return null;
        }

        $header = self::normalize($header, $args);

        if (empty($args['forceBackUrl'])) {
            $header['crumbs'] = self::applyReferrer($header['crumbs'], self::getReferrer());
        }

        return $header;
    }

    /**
     * Header for add-on pages that only pass title + backUrl + backTitle.
     */
    public static function fromLegacyArgs($args)
    {
        $backUrl   = (string)$args['backUrl'];
        $backTitle = isset($args['backTitle']) ? wp_strip_all_tags((string)$args['backTitle']) : '';
        $query     = self::getQueryArgs($backUrl);
        $page      = $query['page'] ?? '';
        $section   = self::getMenuTitle($page);

        if (in_array($backTitle, self::genericBackTitles(), true)) {
            $backTitle = '';
        }

        $crumbs = [];

        if (!empty($query['tab']) || !empty($query['type'])) {
            // The back link is a list inside a section: Section / List
            if ($section) {
                $crumbs[] = ['label' => $section, 'url' => Menus::admin_url($page)];
            }
            $crumbs[] = ['label' => $backTitle ?: $section, 'url' => $backUrl, 'from' => true];
        } else {
            $crumbs[] = ['label' => $backTitle ?: ($section ?: __('Back', 'wp-statistics')), 'url' => $backUrl, 'from' => true];
        }

        return [
            'crumbs' => $crumbs,
            'title'  => isset($args['title']) ? wp_strip_all_tags((string)$args['title']) : '',
            'icon'   => !empty($args['flagImage']) ? ['src' => $args['flagImage'], 'alt' => '', 'shape' => 'flag'] : null,
        ];
    }

    /**
     * Fill defaults and drop broken entries.
     */
    public static function normalize($header, $args = [])
    {
        $header = wp_parse_args($header, [
            'crumbs'     => [],
            'title'      => '',
            'icon'       => null,
            'tag'        => '',
            'link'       => '',
            'link_label' => '',
            'facts'      => [],
            'tooltip'    => $args['tooltip'] ?? '',
        ]);

        if ($header['title'] === '' && !empty($args['title'])) {
            $header['title'] = wp_strip_all_tags((string)$args['title']);
        }

        $header['crumbs'] = array_values(array_filter((array)$header['crumbs'], function ($crumb) {
            return is_array($crumb) && isset($crumb['label']) && $crumb['label'] !== '';
        }));

        $header['facts'] = array_values(array_filter((array)$header['facts'], function ($fact) {
            return is_array($fact) && isset($fact['text']) && $fact['text'] !== '' && $fact['text'] !== null;
        }));

        if (!empty($header['icon']) && (!is_array($header['icon']) || empty($header['icon']['src']))) {
            $header['icon'] = null;
        }

        return $header;
    }

    /**
     * The referrer, only when it is an admin.php screen of this site.
     *
     * @return string
     */
    public static function getReferrer()
    {
        $referrer = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'])) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        return self::isAdminScreenUrl($referrer) ? $referrer : '';
    }

    /**
     * Is the URL an admin.php screen on this site?
     */
    public static function isAdminScreenUrl($url)
    {
        if (empty($url)) {
            return false;
        }

        $admin = wp_parse_url(admin_url('admin.php'));
        $parts = wp_parse_url($url);

        if (empty($parts['host']) || empty($parts['path']) || empty($admin['host'])) {
            return false;
        }

        return strtolower($parts['host']) === strtolower($admin['host'])
            && untrailingslashit($parts['path']) === untrailingslashit($admin['path']);
    }

    /**
     * Point the deepest matching crumb at the list the user came from, so the filters, sort and page they had are kept.
     * This is the one place that keeps the old "back to the previous page" behaviour.
     *
     * @param array $crumbs
     * @param string $referrer Admin URL (already validated).
     * @return array
     */
    public static function applyReferrer($crumbs, $referrer)
    {
        if (empty($referrer) || empty($crumbs)) {
            return $crumbs;
        }

        $from = self::getQueryArgs($referrer);

        // Never "go back" to another single report.
        if (empty($from['page']) || (!empty($from['type']) && strpos((string)$from['type'], 'single') === 0)) {
            return $crumbs;
        }

        for ($i = count($crumbs) - 1; $i >= 0; $i--) {
            $crumb = $crumbs[$i];

            if (empty($crumb['url']) || empty($crumb['from'])) {
                continue;
            }

            $own = self::getQueryArgs($crumb['url']);

            if (($own['page'] ?? '') !== $from['page']) {
                continue;
            }

            $rules = $crumb['from'] === true ? [[]] : (array)$crumb['from'];

            foreach ($rules as $rule) {
                $rule    = (array)$rule;
                $matches = true;

                foreach ($rule as $key => $value) {
                    if ((string)($from[$key] ?? '') !== (string)$value) {
                        $matches = false;
                        break;
                    }
                }

                if (!$matches) {
                    continue;
                }

                $crumbs[$i]['url'] = $referrer;

                // Rename "Visitors" to "Top Visitors" when that is the list the user came from.
                $tab = (string)($from['tab'] ?? '');
                if ($tab !== '' && !empty($crumb['labels'][$tab])) {
                    $crumbs[$i]['label'] = $crumb['labels'][$tab];
                }

                return $crumbs;
            }
        }

        return $crumbs;
    }

    /**
     * @return array Query args of a URL.
     */
    public static function getQueryArgs($url)
    {
        $query = wp_parse_url((string)$url, PHP_URL_QUERY);
        $args  = [];

        if (!empty($query)) {
            wp_parse_str(html_entity_decode($query), $args);
        }

        return $args;
    }

    /**
     * Menu title for an admin page slug (wps_visitors_page => "Visitor Insights").
     */
    public static function getMenuTitle($pageSlug)
    {
        if (empty($pageSlug)) {
            return '';
        }

        foreach (Menus::get_menu_list() as $menu) {
            if (!empty($menu['page_url']) && Menus::get_page_slug($menu['page_url']) === $pageSlug) {
                return wp_strip_all_tags($menu['title'] ?? ($menu['name'] ?? ''));
            }
        }

        return '';
    }

    /**
     * Put the short report name in front of the browser tab title on single reports:
     * "203.0.113.7 ‹ Visitor Insights ‹ Site — WordPress".
     *
     * @param string $adminTitle
     * @return string
     */
    public static function filterAdminTitle($adminTitle)
    {
        $short = SingleHeaders::documentTitle();

        if (empty($short)) {
            return $adminTitle;
        }

        /* translators: 1: Report name (IP, post title, country...), 2: Admin page title. */
        return sprintf(__('%1$s &lsaquo; %2$s', 'wp-statistics'), esc_html($short), $adminTitle);
    }
}
