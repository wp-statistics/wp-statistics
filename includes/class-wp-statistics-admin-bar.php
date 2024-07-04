<?php

namespace WP_STATISTICS;

use WP_Admin_Bar;

class AdminBar
{
    /**
     * AdminBar constructor.
     */
    public function __construct()
    {

        # Show WordPress Admin Bar
        add_action('admin_bar_menu', array($this, 'admin_bar'), 69);
        // Enqueue JavaScript
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'),99);
    }

    /**
     * Enqueue JavaScript for Admin Bar
     */
    public function enqueue_scripts()
    {
        // Only enqueue script if the admin bar is showing
        if (is_admin_bar_showing()) {
            wp_enqueue_script('wp-statistics-admin-bar', WP_STATISTICS_URL . '/assets/js/mini-chart.js', array('jquery'), '1.0', true);
         }
    }

    /**
     * Check Show WP Statistics Admin Bar
     */
    public static function show_admin_bar()
    {
        /**
         * Show/Hide Wp-Statistics Admin Bar
         *
         * @example add_filter('wp_statistics_show_admin_bar', function(){ return false; });
         */
        return (has_filter('wp_statistics_show_admin_bar')) ? apply_filters('wp_statistics_show_admin_bar', true) : Option::get('menu_bar');
    }


    /**
     * Show WordPress Admin Bar
     * @param WP_Admin_Bar $wp_admin_bar
     */
    public function admin_bar($wp_admin_bar)
    {
        // Check Show WordPress Admin Bar
        if (Helper::isAdminBarShowing()) {

            $menu_title = '<span class="ab-icon"></span>';
            $object_id  = get_queried_object_ID();

            $view_type  = false;
            $view_title = false;

            if ((is_single() or is_page()) and !is_front_page()) {

                $view_type  = Pages::get_post_type($object_id);
                $view_title = __('Page Views', 'wp-statistics');

            } elseif (is_category()) {

                $view_type  = 'category';
                $view_title = __('Category Views', 'wp-statistics');

            } elseif (is_tag()) {

                $view_type  = 'post_tag';
                $view_title = __('Tag Views', 'wp-statistics');

            } elseif (is_author()) {

                $view_type  = 'author';
                $view_title = __('Author Views', 'wp-statistics');

            } else {

                $view_title = __('Total Website Views', 'wp-statistics');
                $hit_number = number_format_i18n(wp_statistics_visit('total'));

            }

            if ($view_type && $view_title) {
                $hit_number = wp_statistics_pages('total', '', $object_id, null, null, $view_type);

                $menu_title .= sprintf('%s: %s', $view_title, number_format($hit_number));
                $menu_title .= ' - ';
            }

            $menu_title .= sprintf('Online: %s', number_format(wp_statistics_useronline()));

            /**
             * List Of Admin Bar WordPress
             *
             * --- Array Arg ---
             * Key : ID of Admin bar
             */
            $admin_bar_list = array(
                'wp-statistics-menu-visitors-today' => array(
                    'parent' => 'wp-statistic-menu',
                    'title'  => '<div class="wp-statistics-menu-visitors-today__title">' . __('Visitors Today', 'wp-statistics') . '</div>'
                        . '<div class="wp-statistics-menu-visitors-today__count">' . wp_statistics_visitor('today') . '</div>'
                        . '<div class="wp-statistics-menu-todayvisits">' . sprintf(__('was %s last day', 'wp-statistics'), wp_statistics_visit('today')) . '</div>'
                ),
                'wp-statistics-menu-views-today'    => array(
                    'parent' => 'wp-statistic-menu',
                    'title'  => '<div class="wp-statistics-menu-views-today__title">' . __('Views Today', 'wp-statistics') . '</div>'
                        . '<div class="wp-statistics-menu-views-today__count">' . wp_statistics_visitor('yesterday') . '</div>'
                        . '<div class="wp-statistics-menu-yesterdayvisits">' . sprintf(__('was %s last day', 'wp-statistics'), wp_statistics_visit('yesterday')) . '</div>'

                ),
                'wp-statistics-menu-page'           => array(
                    'parent' => 'wp-statistic-menu',
                    'title'  => sprintf('<img src="%s"/><div><span class="wps-admin-bar__chart__unlock-button">%s</span><button>%s</button></div>',
                        esc_url(WP_STATISTICS_URL . 'assets/images/mini-chart-lock.png'),
                        __('Unlock full potential of Mini-chart', 'wp-statistics'),
                        __('Upgrade Now', 'wp-statistics')
                    ),
                    'href'   => 'https://wp-statistics.com/product/wp-statistics-mini-chart?utm_source=wp_statistics&utm_medium=display&utm_campaign=wordpress',
                    'meta'   => [
                        'target' => '_blank',
                    ],
                ),
                'wp-statistics-footer-page'         => array(
                    'parent' => 'wp-statistic-menu',
                    'title'  => sprintf('<img src="%s"/>
                        <a href="' . esc_url(admin_url('admin.php?page=wps_overview_page')) . '">
                        <span class="wps-admin-bar__chart__unlock-button">%s</span>
                        </a>'
                        ,
                        WP_STATISTICS_URL . 'assets/images/mini-chart-logo.svg',
                        __('Explore Details', 'wp-statistics')
                    ),

                )
            );

            $data = [
                'object_id'          => $object_id,
                'view_type'          => $view_type,
                'view_title'         => $view_title,
                'hit_number'         => $hit_number,
                'menu_href'          => Menus::admin_url('overview'),
                'today_visits'       => number_format(wp_statistics_visit('today')),
                'today_visitors'     => number_format(wp_statistics_visitor('today')),
                'yesterday_visits'   => number_format(wp_statistics_visit('yesterday')),
                'yesterday_visitors' => number_format(wp_statistics_visitor('yesterday')),
                'online_users'       => number_format(wp_statistics_useronline()),
            ];

            /**
             * WP Statistics Admin Bar List
             */
            $admin_bar_list = apply_filters('wp_statistics_admin_bar', $admin_bar_list, $data, '');

            // Create the main menu
            $wp_admin_bar->add_menu(array(
                'id'    => 'wp-statistic-menu',
                'title' => $menu_title,
                'href'  => Menus::admin_url('overview')
            ));
            // Add Global Data tab
            $wp_admin_bar->add_menu(array(
                'parent' => 'wp-statistic-menu',
                'id'     => 'wp-statistic-menu-global-data',
                'title'  => __('Global Data', 'wp-statistics'),
                'meta'   => array('class' => 'wp-statistics-global-data')
            ));

            // Add items to the Global Data tab
            foreach ($admin_bar_list as $id => $v_admin_bar) {
                $v_admin_bar['parent'] = 'wp-statistic-menu-global-data';
                $wp_admin_bar->add_menu(array_merge(array('id' => $id), $v_admin_bar));
            }

            // Add Current Page Data tab
            $wp_admin_bar->add_menu(array(
                'parent' => 'wp-statistic-menu',
                'id'     => 'wp-statistic-menu-current-page-data',
                'title'  => __('Current Page Data', 'wp-statistics'),
                'meta'   => array('class' => 'wp-statistics-current-page-data ')
                // Add Disable class
                //'meta'   => array('class' => 'wp-statistics-current-page-data disabled')
            ));


            // Add a dummy item to the Current Page Data tab
            $wp_admin_bar->add_menu(array(
                'parent' => 'wp-statistic-menu-current-page-data',
                'id'     => 'wp-statistics-menu-current-page-data-item',
                'title'  => __('Test data', 'wp-statistics')
            ));
        }
    }
}

new AdminBar;