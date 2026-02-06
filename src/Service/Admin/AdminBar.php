<?php

namespace WP_Statistics\Service\Admin;

use WP_Admin_Bar;
use WP_Statistics\Components\Addons;
use WP_Statistics\Components\Assets;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Utils\Route;
use WP_Statistics\Utils\UrlBuilder;

/**
 * Class AdminBar
 *
 * Handles the WordPress admin bar integration for WP Statistics.
 * Displays statistics data and links in the admin bar.
 *
 * This class is responsible for:
 * - Adding WP Statistics menu items to the WordPress admin bar
 * - Displaying real-time statistics data in the admin bar
 * - Providing contextual links based on the current page being viewed
 * - Integrating with WordPress admin bar system
 *
 * @package WP_Statistics\Service\Admin
 * @since 15.0.0
 */
class AdminBar
{
    /**
     * Analytics query handler for v15 API
     *
     * @var AnalyticsQueryHandler
     */
    private $queryHandler;

    /**
     * AdminBar constructor.
     *
     * Initializes the admin bar service by setting up the analytics query handler
     * and registering the callback for the admin bar menu.
     */
    public function __construct()
    {
        $this->queryHandler = new AnalyticsQueryHandler();

        add_action('admin_bar_menu', [$this, 'renderAdminBar'], 69);

        // Enqueue admin-bar styles and scripts (admin only - frontend is handled by FrontendHandler)
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminBarAssets']);
    }

    /**
     * Enqueue admin bar assets (styles and scripts) in admin area.
     *
     * Loads mini-chart.js and necessary styles for the admin bar.
     * Frontend assets are handled by FrontendHandler.
     *
     * @return void
     */
    public function enqueueAdminBarAssets()
    {
        if (!Route::isAdminBarShowing()) {
            return;
        }

        // Load mini-chart.js for admin bar charts
        Assets::script('mini-chart', 'js/mini-chart.min.js', [], [], true, false, null, '', '', true);

        // Load admin bar styles
        Assets::style('front', 'css/frontend.min.css');
    }

    /**
     * Get analytics data using the v15 AnalyticsQuery API.
     *
     * Uses batch queries for efficient data retrieval.
     *
     * @return array Analytics data including visitors, views, and online users
     */
    private function getAnalyticsData()
    {
        $today     = DateRange::get('today');
        $yesterday = DateRange::get('yesterday');

        // Online visitors date range: last 5 minutes (using UTC to match database storage)
        $now            = gmdate('Y-m-d H:i:s');
        $fiveMinutesAgo = gmdate('Y-m-d H:i:s', time() - (5 * 60));

        try {
            // Batch query for visitors and views with compare feature
            $result = $this->queryHandler->handleBatch(
                [
                    [
                        'id'      => 'visitors',
                        'sources' => ['visitors'],
                        'format'  => 'flat',
                    ],
                    [
                        'id'      => 'views',
                        'sources' => ['views'],
                        'format'  => 'flat',
                    ],
                    [
                        'id'        => 'online',
                        'sources'   => ['visitors'],
                        'group_by'  => ['online_visitor'],
                        'columns'   => ['visitor_id'],
                        'per_page'  => 1,
                        'format'    => 'table',
                        'compare'   => false,
                        'date_from' => $fiveMinutesAgo,
                        'date_to'   => $now,
                    ],
                ],
                $today['from'],
                $today['to'],
                [],
                true,
                null,
                $yesterday['from'],
                $yesterday['to']
            );

            return [
                'visitors_today'     => $result['items']['visitors']['totals']['visitors']['current'] ?? 0,
                'visitors_yesterday' => $result['items']['visitors']['totals']['visitors']['previous'] ?? 0,
                'views_today'        => $result['items']['views']['totals']['views']['current'] ?? 0,
                'views_yesterday'    => $result['items']['views']['totals']['views']['previous'] ?? 0,
                'online_users'       => $result['items']['online']['meta']['total_rows'] ?? 0,
            ];
        } catch (\Exception $e) {
            return [
                'visitors_today'     => 0,
                'visitors_yesterday' => 0,
                'views_today'        => 0,
                'views_yesterday'    => 0,
                'online_users'       => 0,
            ];
        }
    }

    /**
     * Show WordPress Admin Bar items.
     *
     * Main method that renders the WP Statistics admin bar menu items.
     * Checks if admin bar should be shown, builds the menu structure,
     * and adds all menu items to the WordPress admin bar.
     *
     * @param WP_Admin_Bar $wp_admin_bar The admin-bar instance supplied by WordPress
     *
     * @return void
     */
    public function renderAdminBar($wp_admin_bar)
    {
        if (!Route::isAdminBarShowing()) {
            return;
        }

        $context      = $this->getPageContext();
        $adminBarList = $this->buildAdminBarMenu($context);
        $data         = $this->buildMenuData($context);

        /**
         * Filter: `wp_statistics_admin_bar`
         *
         * Allows other developers to alter the admin-bar items or add new ones.
         *
         * @param array $adminBarList Array of admin bar menu items
         * @param array $data Menu data for context
         * @param string $context_type Context type (legacy parameter)
         */
        $adminBarList = apply_filters('wp_statistics_admin_bar', $adminBarList, $data, '');

        foreach ($adminBarList as $id => $barArgs) {
            $wp_admin_bar->add_menu(array_merge(['id' => $id], $barArgs));
        }
    }

    /**
     * Determine the current page context and gather relevant data.
     *
     * Analyzes the current WordPress page to determine what type of content
     * is being viewed (post, category, tag, etc.) and returns contextual
     * information for building appropriate admin bar menu items.
     *
     * @return array Context information about the current page including:
     *               - object_id: The ID of the current object
     *               - view_type: Type of view (post, category, tag, etc.)
     *               - view_title: Human-readable title for the view
     *               - footer_text: Text for footer links
     *               - footer_link: URL for footer links
     *               - hit_number: Number of hits for this page
     */
    private function getPageContext()
    {
        $objectId = get_queried_object_ID();
        $context  = $this->getBaseContext($objectId);

        $contextHandlers = [
            'isPostContext'     => 'getPostContext',
            'isCategoryContext' => 'getCategoryContext',
            'isTagContext'      => 'getTagContext',
            'isTaxonomyContext' => 'getTaxonomyContext',
            'isAuthorContext'   => 'getAuthorContext',
        ];

        foreach ($contextHandlers as $conditionMethod => $handlerMethod) {
            if ($this->$conditionMethod($objectId)) {
                return $this->$handlerMethod($context, $objectId);
            }
        }

        return $this->getDefaultContext($context);
    }

    /**
     * Get base context array with default values.
     *
     * Creates the initial context array structure with default values
     * that will be used as the foundation for all page contexts.
     *
     * @param int $objectId The WordPress object ID for the current page
     * @return array Base context array with default values
     */
    private function getBaseContext($objectId)
    {
        return [
            'object_id'   => $objectId,
            'view_type'   => false,
            'view_title'  => false,
            'footer_text' => __('Explore Details', 'wp-statistics'),
            'footer_link' => esc_url(admin_url('admin.php?page=wp-statistics')),
            'hit_number'  => 0,
        ];
    }

    /**
     * Check if current page is a post context.
     *
     * Determines if the current page is a single post, page, or front page
     * that has a valid object ID.
     *
     * @param int $objectId The WordPress object ID
     * @return bool True if current page is a post context, false otherwise
     */
    private function isPostContext($objectId)
    {
        return (is_single() || is_page() || is_front_page()) && !empty($objectId);
    }

    /**
     * Check if current page is a category context.
     *
     * Determines if the current page is a category archive page.
     *
     * @param int $objectId The WordPress object ID (unused but kept for consistency)
     * @return bool True if current page is a category context, false otherwise
     */
    private function isCategoryContext($objectId)
    {
        return is_category();
    }

    /**
     * Check if current page is a tag context.
     *
     * Determines if the current page is a tag archive page.
     *
     * @param int $objectId The WordPress object ID (unused but kept for consistency)
     * @return bool True if current page is a tag context, false otherwise
     */
    private function isTagContext($objectId)
    {
        return is_tag();
    }

    /**
     * Check if current page is a taxonomy context.
     *
     * Determines if the current page is a custom taxonomy archive page.
     *
     * @param int $objectId The WordPress object ID (unused but kept for consistency)
     * @return bool True if current page is a taxonomy context, false otherwise
     */
    private function isTaxonomyContext($objectId)
    {
        return is_tax();
    }

    /**
     * Check if current page is an author context.
     *
     * Determines if the current page is an author archive page.
     *
     * @param int $objectId The WordPress object ID (unused but kept for consistency)
     * @return bool True if current page is an author context, false otherwise
     */
    private function isAuthorContext($objectId)
    {
        return is_author();
    }

    /**
     * Get context for single post, page, or front page.
     *
     * Builds context information specific to individual posts, pages,
     * or the front page, including appropriate titles and links.
     *
     * @param array $context Current context array to modify
     * @param int $objectId The WordPress post ID
     * @return array Updated context array with post-specific information
     */
    private function getPostContext(array $context, int $objectId)
    {
        $context['view_type']   = get_post_type($objectId);
        $context['view_title']  = __('Page Views', 'wp-statistics');
        $context['footer_text'] = __('View Page Performance', 'wp-statistics');
        $context['footer_link'] = esc_url(UrlBuilder::contentAnalytics($objectId));

        return $context;
    }

    /**
     * Get context for category pages.
     *
     * Builds context information specific to category archive pages,
     * including appropriate titles and links to category analytics.
     *
     * @param array $context Current context array to modify
     * @param int $objectId The WordPress category term ID
     * @return array Updated context array with category-specific information
     */
    private function getCategoryContext(array $context, int $objectId)
    {
        $context['view_type']   = 'category';
        $context['view_title']  = __('Category Views', 'wp-statistics');
        $context['footer_text'] = __('View Category Performance', 'wp-statistics');
        $context['footer_link'] = esc_url(UrlBuilder::categoryAnalytics($objectId));

        return $context;
    }

    /**
     * Get context for tag pages.
     *
     * Builds context information specific to tag archive pages,
     * including appropriate titles and links to tag analytics.
     *
     * @param array $context Current context array to modify
     * @param int $objectId The WordPress tag term ID
     * @return array Updated context array with tag-specific information
     */
    private function getTagContext(array $context, int $objectId)
    {
        $context['view_type']   = 'post_tag';
        $context['view_title']  = __('Tag Views', 'wp-statistics');
        $context['footer_text'] = __('View Tag Performance', 'wp-statistics');
        $context['footer_link'] = esc_url(UrlBuilder::categoryAnalytics($objectId));

        return $context;
    }

    /**
     * Get context for taxonomy pages.
     *
     * Builds context information specific to custom taxonomy archive pages,
     * including appropriate titles and links to taxonomy analytics.
     *
     * @param array $context Current context array to modify
     * @param int $objectId The WordPress taxonomy term ID
     * @return array Updated context array with taxonomy-specific information
     */
    private function getTaxonomyContext(array $context, int $objectId)
    {
        $context['view_type']   = 'tax';
        $context['view_title']  = __('Taxonomy Views', 'wp-statistics');
        $context['footer_text'] = __('View Taxonomy Performance', 'wp-statistics');
        $context['footer_link'] = esc_url(UrlBuilder::categoryAnalytics($objectId));

        return $context;
    }

    /**
     * Get context for author pages.
     *
     * Builds context information specific to author archive pages,
     * including appropriate titles and links to author analytics.
     *
     * @param array $context Current context array to modify
     * @param int $objectId The WordPress author user ID
     * @return array Updated context array with author-specific information
     */
    private function getAuthorContext(array $context, int $objectId)
    {
        $context['view_type']   = 'author';
        $context['view_title']  = __('Author Views', 'wp-statistics');
        $context['footer_text'] = __('View Author Performance', 'wp-statistics');
        $context['footer_link'] = esc_url(UrlBuilder::authorAnalytics($objectId));

        return $context;
    }

    /**
     * Get default context for other pages.
     *
     * Builds context information for pages that don't fit into specific
     * categories, typically showing total website statistics.
     *
     * @param array $context Current context array to modify
     * @return array Updated context array with default information
     */
    private function getDefaultContext(array $context)
    {
        $stats = $this->getAnalyticsData();

        $context['view_title'] = __('Total Website Views', 'wp-statistics');
        $context['hit_number'] = number_format_i18n($stats['visitors_today']);

        return $context;
    }

    /**
     * Build the admin bar menu title.
     *
     * Constructs the main title text for the admin bar menu item,
     * including view counts and online user information.
     *
     * @param array $context Page context containing view information
     * @return string Formatted HTML menu title
     */
    private function buildMenuTitle(array $context)
    {
        $stats     = $this->getAnalyticsData();
        $menuTitle = '<span class="ab-icon"></span>';

        // If mini-chart add-on is not active, calculate hit numbers manually.
        if (!Addons::isActive('mini-chart') && $context['view_type'] && $context['view_title']) {
            $hitNumber = $this->calculateHitNumber($context);
            $menuTitle .= sprintf('%s: %s', $context['view_title'], number_format($hitNumber));
            $menuTitle .= ' - ';
        }

        $menuTitle .= sprintf('Online: %s', number_format($stats['online_users']));

        return $menuTitle;
    }

    /**
     * Calculate hit number for the current page.
     *
     * Determines the total number of hits/views for the current page
     * by looking up the resource URI and counting associated views.
     *
     * @param array $context Page context containing object information
     * @return int Total hit count for the current page
     */
    private function calculateHitNumber(array $context)
    {
        $resourceUri = $this->getResourceUri($context);

        $resourceUriObj = RecordFactory::resourceUri()->get([
            'uri' => $resourceUri,
        ]);

        if (empty($resourceUriObj->ID)) {
            return 0;
        }

        try {
            $result = $this->queryHandler->handle([
                'sources' => ['views'],
                'filters' => [
                    'resource_uri_id' => $resourceUriObj->ID,
                ],
                'format'  => 'flat',
            ]);

            return $result['data']['views'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get the resource URI for the current context.
     *
     * Builds the resource URI string based on the current page context,
     * handling different types of pages (posts, terms, etc.) appropriately.
     *
     * @param array $context Page context containing view type and object ID
     * @return string The relative resource URI for the current page
     */
    private function getResourceUri(array $context)
    {
        $resourceUri = '';

        if (in_array($context['view_type'], ['category', 'post_tag', 'tax'], true)) {
            $term = get_term($context['object_id']);

            $resourceUri = get_term_link(intval($term->term_id), $term->taxonomy);
            $resourceUri = !is_wp_error($resourceUri) ? $resourceUri : '';
        } else {
            $resourceUri = get_permalink($context['object_id']);
        }

        return wp_make_link_relative($resourceUri);
    }

    /**
     * Build the admin bar menu structure.
     *
     * Creates the complete menu structure for the admin bar, including
     * all menu items, submenus, and their associated properties.
     *
     * @param array $context Page context for building contextual menu items
     * @return array Complete admin bar menu structure
     */
    private function buildAdminBarMenu(array $context)
    {
        $menuTitle = $this->buildMenuTitle($context);

        return [
            'wp-statistic-menu'                   => [
                'title' => $menuTitle,
                'href'  => admin_url('admin.php?page=wp-statistics'),
            ],
            'wp-statistic-menu-global-data'       => [
                'parent' => 'wp-statistic-menu',
                'title'  => __('Global Data', 'wp-statistics'),
                'meta'   => ['class' => 'wp-statistics-global-data'],
            ],
            'wp-statistic-menu-current-page-data' => [
                'parent' => 'wp-statistic-menu',
                'title'  => __('Current Page Data', 'wp-statistics'),
                'meta'   => ['class' => 'wp-statistics-current-page-data disabled'],
            ],
            'wp-statistics-menu-visitors-today'   => [
                'parent' => 'wp-statistic-menu-global-data',
                'title'  => $this->buildVisitorsTodayTitle(),
            ],
            'wp-statistics-menu-views-today'      => [
                'parent' => 'wp-statistic-menu-global-data',
                'title'  => $this->buildViewsTodayTitle(),
            ],
            'wp-statistics-menu-page'             => [
                'parent' => 'wp-statistic-menu-global-data',
                'title'  => $this->buildMiniChartTitle(),
                'href'   => esc_url(admin_url('admin.php?page=wps_plugins_page&type=locked-mini-chart')),
                'meta'   => ['target' => '_blank'],
            ],
            'wp-statistics-footer-page'           => [
                'parent' => 'wp-statistic-menu-global-data',
                'title'  => $this->buildFooterTitle($context['footer_link']),
            ],
        ];
    }

    /**
     * Build the visitors today title HTML.
     *
     * Creates the HTML structure for displaying today's visitor count
     * along with comparison to yesterday's count.
     *
     * @return string Formatted HTML for visitors today section
     */
    private function buildVisitorsTodayTitle()
    {
        $stats = $this->getAnalyticsData();

        return '<div class="wp-statistics-menu-visitors-today__title">' . __('Visitors Today', 'wp-statistics') . '</div>'
            . '<div class="wp-statistics-menu-visitors-today__count">' . number_format($stats['visitors_today']) . '</div>'
            . '<div class="wp-statistics-menu-todayvisits">' . sprintf(__('was %s last day', 'wp-statistics'), number_format($stats['visitors_yesterday'])) . '</div>';
    }

    /**
     * Build the views today title HTML.
     *
     * Creates the HTML structure for displaying today's view count
     * along with comparison to yesterday's count.
     *
     * @return string Formatted HTML for views today section
     */
    private function buildViewsTodayTitle()
    {
        $stats = $this->getAnalyticsData();

        return '<div class="wp-statistics-menu-views-today__title">' . __('Views Today', 'wp-statistics') . '</div>'
            . '<div class="wp-statistics-menu-views-today__count">' . number_format($stats['views_today']) . '</div>'
            . '<div class="wp-statistics-menu-yesterdayvisits">' . sprintf(__('was %s last day', 'wp-statistics'), number_format($stats['views_yesterday'])) . '</div>';
    }

    /**
     * Build the mini chart title HTML.
     *
     * Creates the HTML structure for the mini chart promotional section
     * including lock icon and call-to-action button.
     *
     * @return string Formatted HTML for mini chart section
     */
    private function buildMiniChartTitle()
    {
        return sprintf(
            '<img src="%s"/><div><span class="wps-admin-bar__chart__unlock-button">%s</span><button>%s</button></div>',
            esc_url(WP_STATISTICS_URL . 'public/images/mini-chart-lock.png'),
            __('Unlock the Full Power of WP Statistics', 'wp-statistics'),
            __('Learn More', 'wp-statistics')
        );
    }

    /**
     * Build the footer title HTML.
     *
     * Creates the HTML structure for the footer section with logo
     * and link to detailed statistics.
     *
     * @param string $footerLink The URL to link to in the footer
     * @return string Formatted HTML for footer section
     */
    private function buildFooterTitle(string $footerLink)
    {
        return sprintf(
            '<img src="%s"/><a href="%s" target="_blank"><span class="wps-admin-bar__chart__unlock-button">%s</span></a>',
            esc_url(WP_STATISTICS_URL . 'public/images/mini-chart-logo.svg'),
            esc_url($footerLink),
            __('Explore Details', 'wp-statistics')
        );
    }

    /**
     * Build menu data for the filter hook.
     *
     * Compiles all relevant data into an array that can be used by
     * the wp_statistics_admin_bar filter hook for customization.
     *
     * @param array $context Page context containing basic information
     * @return array Complete menu data array for filter hook
     */
    private function buildMenuData(array $context)
    {
        $stats = $this->getAnalyticsData();

        return [
            'object_id'          => $context['object_id'],
            'view_type'          => $context['view_type'],
            'view_title'         => $context['view_title'],
            'hit_number'         => $context['hit_number'],
            'footer_text'        => $context['footer_text'],
            'footer_link'        => $context['footer_link'],
            'menu_href'          => admin_url('admin.php?page=wp-statistics'),
            'today_visits'       => number_format($stats['views_today']),
            'today_visitors'     => number_format($stats['visitors_today']),
            'yesterday_visits'   => number_format($stats['views_yesterday']),
            'yesterday_visitors' => number_format($stats['visitors_yesterday']),
            'online_users'       => number_format($stats['online_users']),
        ];
    }
}
