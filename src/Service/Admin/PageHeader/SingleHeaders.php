<?php

namespace WP_Statistics\Service\Admin\PageHeader;

use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Country;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;
use WP_Statistics\Utils\Request;
use WP_Statistics\Utils\Url;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Header data (crumbs, title, facts) for each single report. See {@see PageHeader} for the array shape.
 */
class SingleHeaders
{
    /**
     * Header for the current request, when it is a known single report.
     *
     * @param array $args Template args (`data` is used for facts when present).
     * @return array|null
     */
    public static function forRequest($args = [])
    {
        $type = Request::get('type');
        $data = isset($args['data']) && is_array($args['data']) ? $args['data'] : [];

        if (Menus::in_page('visitors') && $type === 'single-visitor') {
            return !empty($data['visitor']) && $data['visitor'] instanceof VisitorDecorator ? self::visitor($data['visitor']) : null;
        }

        if (Menus::in_page('content-analytics') && $type === 'single') {
            return self::post(Request::get('post_id', 0, 'number'));
        }

        if (Menus::in_page('content-analytics') && $type === 'single-resource') {
            return self::resource(rawurldecode(Request::get('uri', '', 'raw')));
        }

        if (Menus::in_page('category-analytics') && $type === 'single') {
            return self::term(Request::get('term_id', 0, 'number'));
        }

        if (Menus::in_page('author-analytics') && $type === 'single-author') {
            return self::author(Request::get('author_id', 0, 'number'));
        }

        if (Menus::in_page('geographic') && $type === 'single-country') {
            return self::country(Request::get('country'), $data);
        }

        if (Menus::in_page('devices') && $type === 'single-browser') {
            return self::browser(Request::get('browser'), $data);
        }

        if ((Menus::in_page('download_tracker') || Menus::in_page('link_tracker')) && $type === 'single') {
            return self::tracker(Request::get('target', '', 'raw'), Menus::in_page('download_tracker') ? 'download' : 'link');
        }

        return null;
    }

    /**
     * Short name for the browser tab title, or '' when the screen is not a single report.
     */
    public static function documentTitle()
    {
        $type = Request::get('type');

        if (empty($type) || !is_admin()) {
            return '';
        }

        if (Menus::in_page('visitors') && $type === 'single-visitor') {
            $visitorId = Request::get('visitor_id', 0, 'number');
            if (empty($visitorId)) return '';

            $visitor = (new VisitorsModel())->getVisitorData(['visitor_id' => $visitorId, 'page_info' => false, 'user_info' => false]);
            if (empty($visitor)) return '';

            return $visitor->getDisplayLabel();
        }

        if (Menus::in_page('content-analytics') && $type === 'single') {
            $postId = Request::get('post_id', 0, 'number');
            return $postId ? wp_strip_all_tags(get_the_title($postId)) : '';
        }

        if (Menus::in_page('category-analytics') && $type === 'single') {
            $term = get_term(Request::get('term_id', 0, 'number'));
            return ($term && !is_wp_error($term)) ? $term->name : '';
        }

        if (Menus::in_page('author-analytics') && $type === 'single-author') {
            $user = get_userdata(Request::get('author_id', 0, 'number'));
            return $user ? $user->display_name : '';
        }

        if (Menus::in_page('geographic') && $type === 'single-country') {
            return Country::getName(Request::get('country'));
        }

        if (Menus::in_page('devices') && $type === 'single-browser') {
            return (string)Request::get('browser');
        }

        if (Menus::in_page('content-analytics') && $type === 'single-resource') {
            return rawurldecode(Request::get('uri', '', 'raw'));
        }

        return '';
    }

    /**
     * Single visitor: IP or hash (or the user name), facts = location · browser on OS · last seen.
     */
    public static function visitor(VisitorDecorator $visitor)
    {
        // Same privacy rule as the visitor card (views/components/visitor-information.php).
        $user = $visitor->getVisibleUser();

        $listTabs = [
            'visitors'        => __('Visitors', 'wp-statistics'),
            'views'           => __('Views', 'wp-statistics'),
            'online'          => __('Online Visitors', 'wp-statistics'),
            'top-visitors'    => __('Top Visitors', 'wp-statistics'),
            'logged-in-users' => __('Logged-in Users', 'wp-statistics'),
        ];

        $header = [
            'crumbs' => [
                ['label' => __('Visitor Insights', 'wp-statistics'), 'url' => Menus::admin_url('visitors'), 'from' => [['tab' => '']]],
                [
                    'label'  => __('Visitors', 'wp-statistics'),
                    'url'    => Menus::admin_url('visitors', ['tab' => 'visitors']),
                    'from'   => array_map(function ($tab) {
                        return ['tab' => $tab];
                    }, array_keys($listTabs)),
                    'labels' => $listTabs,
                ],
            ],
            'title'  => $visitor->getDisplayLabel(),
            // Login status, with the same icons as the Logged-in Users widget and the visitor pill.
            'icon'   => $user
                ? ['src' => get_avatar_url($user->getId(), ['size' => 80]), 'alt' => '', 'shape' => 'avatar']
                : ['src' => WP_STATISTICS_URL . 'assets/images/anonymous.svg', 'alt' => '', 'shape' => 'anonymous'],
            'tag'    => $user ? __('Logged in', 'wp-statistics') : __('Anonymous', 'wp-statistics'),
            'facts'  => [],
        ];

        $location = self::visitorLocation($visitor);
        if ($location) {
            $header['facts'][] = [
                'text'  => $location,
                'icon'  => $visitor->getLocation()->getCountryFlag(),
                'shape' => 'flag',
            ];
        }

        $browser = $visitor->getBrowser()->getName();
        $os      = $visitor->getOs()->getName();
        if (!Admin_Template::isUnknown($browser) && $browser !== __('(not set)', 'wp-statistics')) {
            $header['facts'][] = [
                /* translators: 1: Browser name, 2: Operating system name. */
                'text'  => (!Admin_Template::isUnknown($os) && $os !== __('(not set)', 'wp-statistics')) ? sprintf(__('%1$s on %2$s', 'wp-statistics'), $browser, $os) : $browser,
                'icon'  => $visitor->getBrowser()->getLogo(),
                'shape' => 'logo',
            ];
        }

        $lastSeen = $visitor->getLastView(true);
        if (!empty($lastSeen)) {
            $header['facts'][] = [
                /* translators: %s: Date and time. */
                'text' => sprintf(__('Last seen %s', 'wp-statistics'), self::formatDateTime($lastSeen)),
                'svg'  => 'clock',
            ];
        }

        // IP privacy is a setting, not a login status: its own fact.
        if ($visitor->isHashedIP() || !$visitor->isIpVisible()) {
            $header['facts'][] = [
                // Short hash ("#e7b398f9 · hashed"), same as the visitor card, so visits can be matched.
                'text' => $visitor->isHashedIP()
                    ? ($visitor->getIP() ? /* translators: %s: Short hash of the visitor's IP, e.g. #e7b398f9. */ sprintf(__('%s · hashed', 'wp-statistics'), $visitor->getIP()) : __('Hashed IP', 'wp-statistics'))
                    : __('IP hidden', 'wp-statistics'),
                'svg'  => 'lock',
            ];
        }

        return $header;
    }

    /**
     * "Poznań, Poland" (city left out when unknown).
     */
    public static function visitorLocation(VisitorDecorator $visitor)
    {
        $country = $visitor->getLocation()->getCountryName();
        $city    = $visitor->getLocation()->getCity();
        $parts   = [];

        $notSet = __('(not set)', 'wp-statistics');

        if (!empty($city) && !Admin_Template::isUnknown($city) && $city !== $notSet) {
            $parts[] = $city;
        }

        if (!empty($country) && !Admin_Template::isUnknown($country) && $country !== $notSet) {
            $parts[] = $country;
        }

        return implode(', ', $parts);
    }

    /**
     * Single content: post title + ↗, facts = type, date, author, terms.
     */
    public static function post($postId)
    {
        $post = get_post($postId);

        if (empty($post)) {
            return null;
        }

        $postType = $post->post_type;
        $tabArgs  = $postType === 'post' ? [['tab' => $postType], ['tab' => '']] : [['tab' => $postType]];

        $header = [
            'crumbs'     => [
                ['label' => __('Content Analytics', 'wp-statistics'), 'url' => Menus::admin_url('content-analytics')],
                ['label' => Helper::getPostTypeName($postType) ?: $postType, 'url' => Menus::admin_url('content-analytics', ['tab' => $postType]), 'from' => $tabArgs],
            ],
            'title'      => wp_strip_all_tags(get_the_title($post)),
            'link'       => get_permalink($post),
            'link_label' => __('Open page in a new tab', 'wp-statistics'),
            'facts'      => [
                [
                    'text' => Helper::getPostTypeName($postType, true),
                    'tag'  => true,
                    'url'  => Menus::admin_url('pages', ['pt' => $postType]),
                ],
            ],
        ];

        $published = get_the_date(Helper::getDefaultDateFormat(), $post);
        $updated   = get_the_modified_date(Helper::getDefaultDateFormat(), $post);

        if ($published) {
            $header['facts'][] = ['text' => $published, 'svg' => 'calendar', 'title' => __('Published', 'wp-statistics')];
        }

        if ($updated && $updated !== $published) {
            /* translators: %s: Date. */
            $header['facts'][] = ['text' => sprintf(__('Updated %s', 'wp-statistics'), $updated)];
        }

        if ((int)$post->post_author > 0) {
            $header['facts'][] = [
                'text' => get_the_author_meta('display_name', $post->post_author),
                'url'  => Menus::admin_url('author-analytics', ['type' => 'single-author', 'author_id' => $post->post_author]),
                'svg'  => 'user',
            ];
        }

        $terms = [];
        foreach (get_post_taxonomies($post) as $taxonomy) {
            $taxonomyObject = get_taxonomy($taxonomy);
            if (empty($taxonomyObject) || empty($taxonomyObject->public)) continue;

            $postTerms = get_the_terms($post, $taxonomy);
            if (is_array($postTerms)) {
                $terms = array_merge($terms, $postTerms);
            }
        }

        $shown = array_slice($terms, 0, 3);
        foreach ($shown as $term) {
            $header['facts'][] = [
                'text' => $term->name,
                'url'  => Menus::admin_url('category-analytics', ['type' => 'single', 'term_id' => $term->term_id]),
            ];
        }

        if (count($terms) > 3) {
            $rest = wp_list_pluck(array_slice($terms, 3), 'name');
            $header['facts'][] = [
                /* translators: %s: Number of more terms. */
                'text'  => sprintf(__('+%s more', 'wp-statistics'), number_format_i18n(count($rest))),
                'title' => implode(', ', $rest),
            ];
        }

        return $header;
    }

    /**
     * Single URL (a page WordPress has no post for).
     */
    public static function resource($uri)
    {
        return [
            'crumbs' => [
                ['label' => __('Content Analytics', 'wp-statistics'), 'url' => Menus::admin_url('content-analytics'), 'from' => true],
            ],
            'title'  => (string)$uri,
            'link'   => $uri !== '' ? home_url($uri) : '',
        ];
    }

    /**
     * Single category / tag / term: name without quotes + ↗, facts = taxonomy, post count.
     */
    public static function term($termId)
    {
        $term = get_term($termId);

        if (empty($term) || is_wp_error($term)) {
            return null;
        }

        $taxonomy = $term->taxonomy;
        $link     = get_term_link($term);

        $header = [
            'crumbs' => [
                ['label' => __('Category Analytics', 'wp-statistics'), 'url' => Menus::admin_url('category-analytics')],
                [
                    'label' => Helper::getTaxonomyName($taxonomy) ?: $taxonomy,
                    'url'   => Menus::admin_url('category-analytics', ['tab' => $taxonomy]),
                    'from'  => array_merge(
                        [['tab' => $taxonomy, 'type' => ''], ['type' => 'report', 'tx' => $taxonomy]],
                        $taxonomy === 'category' ? [['tab' => '', 'type' => '']] : []
                    ),
                ],
            ],
            'title'  => $term->name,
            'link'   => is_wp_error($link) ? '' : $link,
            'facts'  => [
                ['text' => Helper::getTaxonomyName($taxonomy, true), 'tag' => true],
                /* translators: %s: Number of posts. */
                ['text' => sprintf(_n('%s post', '%s posts', (int)$term->count, 'wp-statistics'), number_format_i18n((int)$term->count))],
            ],
        ];

        return $header;
    }

    /**
     * Single author: avatar + name + role, facts = joined date and profile / posts / email links.
     */
    public static function author($authorId)
    {
        $user = get_userdata($authorId);

        $header = [
            'crumbs' => [
                ['label' => __('Author Analytics', 'wp-statistics'), 'url' => Menus::admin_url('author-analytics'), 'from' => [['type' => '']]],
                ['label' => __('Authors', 'wp-statistics'), 'url' => Menus::admin_url('author-analytics', ['type' => 'authors']), 'from' => [['type' => 'authors']]],
            ],
            'title'  => $user ? $user->display_name : __('Unknown User', 'wp-statistics'),
            'icon'   => ['src' => get_avatar_url($authorId, ['size' => 80]), 'alt' => '', 'shape' => 'avatar'],
            'facts'  => [],
        ];

        if (!$user) {
            return $header;
        }

        $role = !empty($user->roles) ? reset($user->roles) : '';
        if ($role) {
            $roles         = wp_roles()->get_names();
            $header['tag'] = isset($roles[$role]) ? translate_user_role($roles[$role]) : ucfirst($role);
        }

        if (!empty($user->user_registered)) {
            /* translators: %s: Date. */
            $header['facts'][] = ['text' => sprintf(__('Joined %s', 'wp-statistics'), date_i18n(Helper::getDefaultDateFormat(), strtotime($user->user_registered)))];
        }

        $header['facts'][] = ['text' => __('Profile', 'wp-statistics'), 'url' => get_edit_user_link($authorId)];
        $header['facts'][] = ['text' => __('Posts', 'wp-statistics'), 'url' => Menus::admin_url('pages', ['tab' => 'top', 'author_id' => $authorId, 'pt' => 'post'])];

        if (!empty($user->user_email)) {
            $header['facts'][] = ['text' => __('Email', 'wp-statistics'), 'url' => 'mailto:' . $user->user_email];
        }

        return $header;
    }

    /**
     * Single country: flag + name, facts = top city (Data Plus data when present).
     */
    public static function country($code, $data = [])
    {
        $code = is_string($code) ? strtoupper($code) : '';

        $header = [
            'crumbs' => [
                ['label' => __('Geographic', 'wp-statistics'), 'url' => Menus::admin_url('geographic'), 'from' => [['tab' => '']]],
                [
                    'label'  => __('Countries', 'wp-statistics'),
                    'url'    => Menus::admin_url('geographic', ['tab' => 'countries']),
                    'from'   => [['tab' => 'countries'], ['tab' => 'europe'], ['tab' => 'cities']],
                    'labels' => ['europe' => __('European Countries', 'wp-statistics'), 'cities' => __('Cities', 'wp-statistics')],
                ],
            ],
            'title'  => Country::getName($code),
            'icon'   => ['src' => Country::flag($code), 'alt' => '', 'shape' => 'flag'],
            'facts'  => [],
        ];

        $topCity = $data['glance']['city']['value'] ?? '';
        if (is_string($topCity) && $topCity !== '' && !Admin_Template::isUnknown($topCity) && $topCity !== '-') {
            /* translators: %s: City name. */
            $header['facts'][] = ['text' => sprintf(__('Top city %s', 'wp-statistics'), $topCity)];
        }

        return $header;
    }

    /**
     * Single browser: logo + name, facts = number of versions and a link to its visitors.
     */
    public static function browser($browser, $data = [])
    {
        $browser = (string)$browser;

        $header = [
            'crumbs' => [
                ['label' => __('Devices', 'wp-statistics'), 'url' => Menus::admin_url('devices'), 'from' => [['tab' => '']]],
                ['label' => __('Browsers', 'wp-statistics'), 'url' => Menus::admin_url('devices', ['tab' => 'browsers']), 'from' => [['tab' => 'browsers']]],
            ],
            'title'  => $browser,
            'icon'   => ['src' => DeviceHelper::getBrowserLogo($browser), 'alt' => '', 'shape' => 'logo'],
            'facts'  => [],
        ];

        if (isset($data['total']) && (int)$data['total'] > 0) {
            /* translators: %s: Number of browser versions. */
            $header['facts'][] = ['text' => sprintf(_n('%s version', '%s versions', (int)$data['total'], 'wp-statistics'), number_format_i18n((int)$data['total']))];
        }

        $header['facts'][] = [
            'text' => __('See visitors', 'wp-statistics'),
            'url'  => Menus::admin_url('visitors', ['tab' => 'visitors', 'agent' => $browser]),
        ];

        return $header;
    }

    /**
     * Single tracked link or download (Data Plus): file or URL as the title + ↗.
     */
    public static function tracker($target, $kind = 'link')
    {
        $target = (string)$target;
        $page   = $kind === 'download' ? 'download_tracker' : 'link_tracker';
        $title  = $target;
        $facts  = [['text' => $kind === 'download' ? __('Download', 'wp-statistics') : __('Link', 'wp-statistics'), 'tag' => true]];

        $path     = (string)Url::getPath($target);
        $filename = $path !== '' ? basename($path) : '';

        if ($kind === 'download' && $filename !== '') {
            $title   = $filename;
            $folder  = preg_replace('#(?<!:)/{2,}#', '/', substr($target, 0, strrpos($target, $filename)));
            $facts[] = ['text' => $folder, 'title' => $target];
        } else {
            $title = preg_replace('#^https?://(www\.)?#i', '', $target);
        }

        return [
            'crumbs' => [
                ['label' => PageHeader::getMenuTitle(Menus::get_page_slug($page)) ?: ($kind === 'download' ? __('Download Tracker', 'wp-statistics') : __('Link Tracker', 'wp-statistics')), 'url' => Menus::admin_url($page), 'from' => true],
            ],
            'title'  => untrailingslashit($title),
            'link'   => $target,
            'facts'  => $facts,
        ];
    }

    /**
     * "Nov 20, 7:40 pm", with the year only when it is not this year.
     */
    public static function formatDateTime($date)
    {
        if (empty($date) || (!is_numeric($date) && strtotime($date) === false)) {
            return '';
        }

        return DateTime::format($date, [
            'include_time' => true,
            'exclude_year' => DateTime::format($date, ['date_format' => 'Y']) === DateTime::get('now', 'Y'),
            'short_month'  => true,
            'separator'    => ', ',
        ]);
    }
}
