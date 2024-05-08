<?php

namespace WP_STATISTICS;

class Meta_Box
{
    /**
     * Meta Box Class namespace
     *
     * @var string
     */
    public static $namespace = "\\WP_Statistics\\MetaBox\\";

    /**
     * Meta Box Setup Key
     *
     * @param $key
     * @return string
     */
    public static function getMetaBoxKey($key)
    {
        return 'wp-statistics-' . $key . '-widget';
    }

    /**
     * Load WordPress Meta Box
     */
    public static function includes()
    {
        require_once WP_STATISTICS_DIR . 'includes/admin/meta-box/wp-statistics-meta-box-abstract.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/meta-box/wp-statistics-meta-box-quickstats.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/meta-box/wp-statistics-meta-box-summary.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/meta-box/wp-statistics-meta-box-browsers.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/meta-box/wp-statistics-meta-box-platforms.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/meta-box/wp-statistics-meta-box-devices.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/meta-box/wp-statistics-meta-box-models.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/meta-box/wp-statistics-meta-box-countries.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/meta-box/wp-statistics-meta-box-hits.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/meta-box/wp-statistics-meta-box-pages.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/meta-box/wp-statistics-meta-box-referring.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/meta-box/wp-statistics-meta-box-search.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/meta-box/wp-statistics-meta-box-top-visitors.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/meta-box/wp-statistics-meta-box-recent.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/meta-box/wp-statistics-meta-box-hitsmap.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/meta-box/wp-statistics-meta-box-useronline.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/meta-box/wp-statistics-meta-box-about.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/meta-box/wp-statistics-meta-box-post.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/meta-box/wp-statistics-meta-box-top-pages-chart.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/meta-box/wp-statistics-meta-box-pages-chart.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/meta-box/wp-statistics-meta-box-exclusions.php';
    }

    /**
     * Get Admin Meta Box List
     *
     * @param bool $meta_box
     * @return array|mixed
     */
    public static function getList($meta_box = false)
    {
        /**
         * List of WP Statistics Admin Meta Box
         *
         * --- Array Arg -----
         * page_url          : link of Widget Page @see WP_Statistics::$page
         * name              : Name Of Widget Box
         * require           : the Condition From Wp-statistics Option if == true
         * show_on_dashboard : Show Meta Box in WordPress Dashboard
         * hidden            : if set true , Default Hidden Dashboard in Wordpress Admin
         * js                : if set false, Load without RestAPI Request.
         * place             : Meta Box Place in Overview Page [ normal | side ]
         * disable_overview  : Disable MetaBox From Overview Page [ default : false ]
         * hidden_overview   : if set true , Default Hidden Meta Box in OverView Page
         *
         */
        $list = array(
            'quickstats'      => array(
                'page_url'          => 'overview',
                'name'              => __('Quick Stats', 'wp-statistics'),
                'show_on_dashboard' => true,
                'hidden'            => false,
                'place'             => 'side',
                'disable_overview'  => true
            ),
            'summary'         => array(
                'name'              => __('Traffic Summary', 'wp-statistics'),
                'description'       => __('A quick overview of your website\'s visitor statistics.', 'wp-statistics'),
                'hidden'            => true,
                'show_on_dashboard' => true,
                'place'             => 'side'
            ),
            'browsers'        => array(
                'page_url'          => 'browser',
                'name'              => __('Browser Usage', 'wp-statistics'),
                'description'       => __('Distribution of visitors based on the browsers they use.', 'wp-statistics'),
                'require'           => array('visitors' => true),
                'hidden'            => true,
                'show_on_dashboard' => true,
                'place'             => 'side',
                'footer_options'    => [
                    'filter_by_date'      => true,
                    'default_date_filter' => User::getDefaultDateFilter('browsers', 'filter|30days'),
                    'display_more_link'   => true,
                    'more_link_title'     => __('View Browser Usage'),
                ]
            ),
            'platforms'       => array(
                'page_url'          => 'platform',
                'name'              => __('Most Used Operating Systems', 'wp-statistics'),
                'description'       => __('Identify the operating systems most commonly used by your website visitors.', 'wp-statistics'),
                'require'           => array('visitors' => true),
                'hidden'            => true,
                'show_on_dashboard' => true,
                'place'             => 'side',
                'footer_options'    => [
                    'filter_by_date'      => true,
                    'default_date_filter' => User::getDefaultDateFilter('platforms', 'filter|30days'),
                    'display_more_link'   => true,
                    'more_link_title'     => __('View Most Used Operating Systems'),
                ]
            ),
            'devices'         => array(
                'name'              => __('Device Usage Breakdown', 'wp-statistics'),
                'description'       => __('Distribution of visitors based on the devices they use to access your site.', 'wp-statistics'),
                'require'           => array('visitors' => true),
                'hidden'            => true,
                'show_on_dashboard' => true,
                'place'             => 'side'
            ),
            'models'          => array(
                'name'              => __('Top Device Models', 'wp-statistics'),
                'require'           => array('visitors' => true),
                'hidden'            => true,
                'show_on_dashboard' => true,
                'place'             => 'side'
            ),
            'countries'       => array(
                'page_url'          => 'countries',
                'name'              => __('Top Countries', 'wp-statistics'),
                'require'           => array('geoip' => true, 'visitors' => true),
                'hidden'            => true,
                'show_on_dashboard' => true,
                'place'             => 'side',
                'footer_options'    => [
                    'filter_by_date'      => true,
                    'default_date_filter' => User::getDefaultDateFilter('countries', 'filter|30days'),
                    'display_more_link'   => true,
                    'more_link_title'     => __('View Top Countries'),
                ]
            ),
            'referring'       => array(
                'page_url'          => 'referrers',
                'name'              => __('Top Referring', 'wp-statistics'),
                'require'           => array('visitors' => true),
                'hidden'            => true,
                'show_on_dashboard' => true,
                'place'             => 'side',
                'footer_options'    => [
                    'filter_by_date'      => true,
                    'default_date_filter' => User::getDefaultDateFilter('referring', 'filter|30days'),
                    'display_more_link'   => true,
                    'more_link_title'     => __('View Top Referring'),
                ]
            ),
            'hits'            => array(
                'page_url'          => 'hits',
                'name'              => __('Daily Traffic Trend', 'wp-statistics'),
                'description'       => __('Day-by-day breakdown of views and page views over the selected period.', 'wp-statistics'),
                'require'           => array('visits' => true),
                'hidden'            => true,
                'show_on_dashboard' => true,
                'place'             => 'normal',
                'footer_options'    => [
                    'filter_by_date'      => true,
                    'default_date_filter' => User::getDefaultDateFilter('hits', 'filter|7days'),
                    'display_more_link'   => true,
                    'more_link_title'     => __('Daily Traffic Trend Report'),
                ]
            ),
            'search'          => array(
                'page_url'          => 'searches',
                'name'              => __('Referrals from Search Engines', 'wp-statistics'),
                'description'       => __('A breakdown of views from different search engines over time.', 'wp-statistics'),
                'require'           => array('visitors' => true),
                'hidden'            => true,
                'show_on_dashboard' => true,
                'place'             => 'normal',
                'footer_options'    => [
                    'filter_by_date'      => true,
                    'default_date_filter' => User::getDefaultDateFilter('search', 'filter|7days'),
                    'display_more_link'   => true,
                    'more_link_title'     => __('View Referrals from Search Engines'),
                ]
            ),
            'pages'           => array(
                'page_url'          => 'pages',
                'name'              => __('Most Visited Pages', 'wp-statistics'),
                'description'       => __('Pages on your website with the highest number of views in the selected time frame.', 'wp-statistics'),
                'require'           => array('pages' => true),
                'hidden'            => true,
                'show_on_dashboard' => true,
                'place'             => 'normal',
                'footer_options'    => [
                    'filter_by_date'      => true,
                    'default_date_filter' => User::getDefaultDateFilter('pages', 'filter|30days'),
                    'display_more_link'   => true,
                    'more_link_title'     => __('View Most Visited Pages'),
                ]
            ),
            'top-visitors'    => array(
                'page_url'          => 'top-visitors',
                'name'              => __('Most Active Visitors', 'wp-statistics'),
                'description'       => __('Visitors with the highest number of views, including their country, city, IP address, and browser.', 'wp-statistics'),
                'require'           => array('visitors' => true),
                'hidden'            => true,
                'show_on_dashboard' => true,
                'place'             => 'normal',
                'footer_options'    => [
                    'filter_by_date'      => false,
                    'default_date_filter' => false,
                    'display_more_link'   => true,
                    'more_link_title'     => __('View Most Active Visitors', 'wp-statistics'),
                ]
            ),
            'recent'          => array(
                'page_url'          => 'visitors',
                'name'              => __('Latest Visitor Breakdown', 'wp-statistics'),
                'description'       => __('Details of the most recent visitors to your site.', 'wp-statistics'),
                'require'           => array('visitors' => true),
                'hidden'            => true,
                'show_on_dashboard' => true,
                'place'             => 'normal'
            ),
            'hitsmap'         => array(
                'name'              => __('Global Visitor Distribution', 'wp-statistics'),
                'description'       => __('Geographical representation of where your site\'s visitors come from.', 'wp-statistics'),
                'require'           => array('geoip' => true, 'visitors' => true, 'disable_map' => false),
                'hidden'            => true,
                'show_on_dashboard' => true,
                'place'             => 'normal',
                'footer_options'    => [
                    'filter_by_date'      => true,
                    'default_date_filter' => User::getDefaultDateFilter('hitsmap', 'filter|today'),
                    'display_more_link'   => false,
                    'more_link_title'     => '',
                ]
            ),
            'useronline'      => array(
                'name'              => __('Currently Online', 'wp-statistics'),
                'page_url'          => 'online',
                'require'           => array('useronline' => true),
                'hidden'            => true,
                'show_on_dashboard' => true,
                'place'             => 'normal'
            ),
            'about'           => array(
                'name'              => apply_filters('wp_statistics_about_widget_title', __('WP Statistics', 'wp-statistics')),
                'description'       => __('Information about the current version of WP Statistics and related resources.', 'wp-statistics'),
                'show_on_dashboard' => false,
                'js'                => false,
                'place'             => 'side',
                'disable_overview'  => apply_filters('wp_statistics_disable_about_widget_overview', false),
            ),
            'post'            => array(
                'name'              => __('Daily Traffic Trend', 'wp-statistics'),
                'page_url'          => 'pages',
                'show_on_dashboard' => false,
                'disable_overview'  => true
            ),
            'top-pages-chart' => array(
                'name'              => __('Top 5 Trending Pages', 'wp-statistics'),
                'show_on_dashboard' => false,
                'disable_overview'  => true
            ),
            'pages-chart'     => array(
                'name'              => __('Pages Views', 'wp-statistics'),
                'show_on_dashboard' => false,
                'disable_overview'  => true
            ),
            'exclusions'      => array(
                'name'              => __('Exclusions', 'wp-statistics'),
                'show_on_dashboard' => false,
                'disable_overview'  => true
            ),
        );

        /**
         * Filter the list of metaboxes list
         * @since 14.0
         */
        $list = apply_filters('wp_statistics_overview_meta_box_list', $list);

        //Print List of Meta Box
        if ($meta_box === false) {
            return $list;
        } else {
            if (array_key_exists($meta_box, $list)) {
                return $list[$meta_box];
            }
        }

        return array();
    }

    /**
     * Get Meta Box Class name
     *
     * @param $meta_box
     * @return string
     */
    public static function getMetaBoxClass($meta_box)
    {
        return apply_filters('wp_statistics_meta_box_class', self::$namespace . str_replace("-", "_", $meta_box), $meta_box);
    }

    /**
     * Check Exist Meta Box Class
     *
     * @param $meta_box
     * @return bool
     */
    public static function metaBoxClassExist($meta_box)
    {
        return class_exists(self::getMetaBoxClass($meta_box));
    }

    /**
     * Get Meta Box Key By ClassName
     *
     * @param $className
     * @return string
     */
    public static function getMetaBoxKeyByClassName($className)
    {
        $className = str_replace("WP_STATISTICS\\MetaBox\\", '', $className);
        return str_replace('_', '-', $className);
    }

    /**
     * Load MetaBox
     *
     * @param $key
     * @return null
     */
    public static function LoadMetaBox($key)
    {

        // Get MetaBox by Key
        $metaBox = self::getList($key);
        if (count($metaBox) > 0) {
            // Check Load Rest-API or Manually
            if (isset($metaBox['js']) and $metaBox['js'] === false && self::metaBoxClassExist($key)) {
                $class = self::getMetaBoxClass($key);
                return array($class, 'get');
            }
        }

        return function () {
            return null;
        };
    }

}