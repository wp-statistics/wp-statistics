<?php

namespace WP_STATISTICS;

class Admin_Assets
{
    /**
     * Prefix Of Load Css/Js in WordPress Admin
     *
     * @var string
     */
    public static $prefix = 'wp-statistics-admin';

    /**
     * Suffix Of Minify File in Assets
     *
     * @var string
     */
    public static $suffix_min = '.min';

    /**
     * Assets Folder name in Plugin
     *
     * @var string
     */
    public static $asset_dir = 'assets';

    /**
     * Basic Of Plugin Url in Wordpress
     *
     * @var string
     * @example http://site.com/wp-content/plugins/my-plugin/
     */
    public static $plugin_url = WP_STATISTICS_URL;

    /**
     * Current Asset Version for this plugin
     *
     * @var string
     */
    public static $asset_version = WP_STATISTICS_VERSION;

    /**
     * Admin_Assets constructor.
     */
    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'admin_styles'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));

        $this->initFeedback();
    }

    /**
     * Get Version of File
     *
     * @param $ver
     * @return bool
     */
    public static function version($ver = false)
    {
        if ($ver) {
            return $ver;
        } else {
            if (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) {
                return time();
            } else {
                return self::$asset_version;
            }
        }
    }

    /**
     * Get Asset Url
     *
     * @param $file_name
     * @return string
     */
    public static function url($file_name)
    {

        // Get file Extension Type
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        if ($ext != "js" and $ext != "css") {
            $ext = 'images';
        }

        // Prepare File Path
        $path = self::$asset_dir . '/' . $ext . '/';

        // Prepare Full Url
        $url = self::$plugin_url . $path;

        // Return Url
        return $url . $file_name;
    }

    /**
     * Enqueue dashboard page styles.
     */

    public function dashboard_styles()
    {
        // Load Dashboard Css
        wp_enqueue_style(self::$prefix . '-dashboard', self::url('dashboard.min.css'), array(), self::version());
    }

    /**
     * Enqueue styles.
     */
    public function admin_styles()
    {

        // Get Current Screen ID
        $screen_id = Helper::get_screen_id();

        // Load Admin Css
        wp_enqueue_style(self::$prefix, self::url('admin.min.css'), array(), self::version());

        // Load Rtl Version Css
        if (is_rtl()) {
            wp_enqueue_style(self::$prefix . '-rtl', self::url('rtl.min.css'), array(), self::version());
        }

        //Load Jquery VMap Css
        if (!Option::get('disable_map') and (Menus::in_page('overview') || Menus::in_page('pages') || (in_array($screen_id, array('dashboard')) and !Option::get('disable_dashboard')))) {
            wp_enqueue_style(self::$prefix . '-jqvmap', self::url('jqvmap/jqvmap.min.css'), array(), '1.5.1');
        }

        // Load Jquery-ui theme
        //        if (Menus::in_plugin_page() and Menus::in_page('optimization') === false and Menus::in_page('settings') === false) {
        //            wp_enqueue_style(self::$prefix . '-jquery-datepicker', self::url('datepicker.min.css'), array(), '1.11.4');
        //        }

        // Load Select2
        if (Menus::in_page('visitors') || Menus::in_page('link_tracker') || Menus::in_page('download_tracker') || (Menus::in_page('pages') and isset($_GET['ID']))) {
            wp_enqueue_style(self::$prefix . '-select2', self::url('select2/select2.min.css'), array(), '4.0.9');
        }

        // Load RangeDatePicker
        if (Menus::in_plugin_page() || Menus::in_page('pages') || in_array($screen_id, array('dashboard'))) {
            wp_enqueue_style(self::$prefix . '-daterangepicker', self::url('datepicker/daterangepicker.css'), array(), '1.0.0');
            wp_enqueue_style(self::$prefix . '-customize', self::url('datepicker/customize.css'), array(), '1.0.0');
        }
    }

    /**
     * Enqueue scripts.
     *
     * @param $hook [ Page Now ]
     */
    public function admin_scripts($hook)
    {

        // Get Current Screen ID
        $screen_id = Helper::get_screen_id();

        // Load Chart Js Library [ Load in <head> Tag ]
        if (Menus::in_plugin_page() || (in_array($screen_id, array('dashboard')) and !Option::get('disable_dashboard')) || (in_array($hook, array('post.php', 'edit.php', 'post-new.php')) and !Option::get('disable_editor'))) {
            wp_enqueue_script(self::$prefix . '-chart.js', self::url('chartjs/chart.umd.min.js'), [], '4.4.2', false);
            wp_enqueue_script(self::$prefix . '-hammer.js', self::url('chartjs/hammer.min.js'), [], '2.0.8', false);
            wp_enqueue_script(self::$prefix . '-chartjs-plugin-zoom.js', self::url('chartjs/chartjs-plugin-zoom.min.js'), [self::$prefix . '-hammer.js'], '2.0.1', false);
        }

        // Load Jquery VMap Js Library
        if (!Option::get('disable_map') and (Menus::in_page('overview') || Menus::in_page('pages') || (in_array($screen_id, array('dashboard')) and !Option::get('disable_dashboard')))) {
            wp_enqueue_script(self::$prefix . '-jqvmap', self::url('jqvmap/jquery.vmap.min.js'), array('jquery'), "1.5.1", ['in_footer' => true]);
            wp_enqueue_script(self::$prefix . '-jqvmap-world', self::url('jqvmap/jquery.vmap.world.min.js'), array('jquery'), "1.5.1", ['in_footer' => true]);
        }


        // Load Jquery UI
        //        if (Menus::in_plugin_page() and Menus::in_page('optimization') === false and Menus::in_page('settings') === false) {
        //            wp_enqueue_script('jquery-ui-datepicker');
        //            wp_localize_script('jquery-ui-datepicker', 'wps_i18n_jquery_datepicker', self::localize_jquery_datepicker());
        //        }

        // Load Select2
        if (Menus::in_page('visitors') || Menus::in_page('link_tracker') || Menus::in_page('download_tracker') || (Menus::in_page('pages') and isset($_GET['ID']))) {
            wp_enqueue_script(self::$prefix . '-select2', self::url('select2/select2.full.min.js'), array('jquery'), "4.1.0", ['in_footer' => true]);
        }

        // Load WordPress PostBox Script
        if (Menus::in_plugin_page() and Menus::in_page('optimization') === false and Menus::in_page('settings') === false) {
            wp_enqueue_script('common');
            wp_enqueue_script('wp-lists');
            wp_enqueue_script('postbox');
        }

        if (Menus::in_page('settings')) {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
        }

        // Load Admin Js
        if (Menus::in_plugin_page() || (in_array($screen_id, array('dashboard')) and !Option::get('disable_dashboard')) || (in_array($hook, array('post.php', 'edit.php', 'post-new.php')) and !Option::get('disable_editor'))) {
            wp_enqueue_script(self::$prefix, self::url('admin.min.js'), array('jquery'), self::version(), ['in_footer' => true]);
            wp_localize_script(self::$prefix, 'wps_global', self::wps_global($hook));
        }

        // Load TinyMCE for Widget Page
        if (in_array($screen_id, array('widgets'))) {
            wp_enqueue_script(self::$prefix . '-button-widget', self::url('tinymce.min.js'), array('jquery'), "3.2.5", ['in_footer' => true]);
        }

        // Add Thick box
        if (Menus::in_page('visitors')) {
            wp_enqueue_script('thickbox');
            wp_enqueue_style('thickbox');
        }

        // Add RangeDatePicker
        if (Menus::in_plugin_page() || Menus::in_page('pages') || in_array($screen_id, array('dashboard'))) {
            wp_enqueue_script(self::$prefix . '-moment', self::url('datepicker/moment.min.js'), array(), "2.18.1", ['in_footer' => true]);
            wp_enqueue_script(self::$prefix . '-daterangepicker', self::url('datepicker/daterangepicker.min.js'), array(), "1.13.2", ['in_footer' => true]);
        }

        if (Menus::in_page('pages')) {
            wp_enqueue_script(self::$prefix . '-datepicker', self::url('datepicker/datepicker.js'), array(), self::version(), ['in_footer' => true]);
        }
    }

    /**
     * Prepare global WP Statistics data for use Admin Js
     *
     * @param $hook
     * @return mixed
     */
    public static function wps_global($hook)
    {
        global $post;

        //Global Option
        $list['options'] = array(
            'rtl'           => (is_rtl() ? 1 : 0),
            'user_online'   => (Option::get('useronline') ? 1 : 0),
            'visitors'      => (Option::get('visitors') ? 1 : 0),
            'visits'        => (Option::get('visits') ? 1 : 0),
            'geo_ip'        => (GeoIP::active() ? 1 : 0),
            'geo_city'      => (GeoIP::active('city') ? 1 : 0),
            'overview_page' => (Menus::in_page('overview') ? 1 : 0),
            'gutenberg'     => (Helper::is_gutenberg() ? 1 : 0),
            'more_btn'      => (apply_filters('wp_statistics_meta_box_more_button', true) ? 1 : 0),
            'overview_ads'  => (apply_filters('wp_statistics_ads_overview_page_show', true) ? 1 : 0)
        );

        // WordPress Current Page
        $list['page'] = array(
            'file' => $hook,
            'ID'   => (isset($post) ? $post->ID : 0)
        );

        // WordPress Admin Page request Params
        if (isset($_GET)) {
            foreach ($_GET as $key => $value) {
                if ($key == "page") {
                    $slug  = Menus::getPageKeyFromSlug(esc_html($value));
                    $value = $slug[0];
                }
                $list['request_params'][esc_html($key)] = esc_html($value);
            }
        }

        // Global Lang
        $list['i18n'] = array(
            'more_detail'           => __('View Details', 'wp-statistics'),
            'reload'                => __('Reload', 'wp-statistics'),
            'online_users'          => __('Online Users', 'wp-statistics'),
            'visitors'              => __('Visitors', 'wp-statistics'),
            'visits'                => __('Views', 'wp-statistics'),
            'today'                 => __('Today', 'wp-statistics'),
            'yesterday'             => __('Yesterday', 'wp-statistics'),
            'last-week'             => __('Last week', 'wp-statistics'),
            'week'                  => __('Last 7 days', 'wp-statistics'),
            'month'                 => __('Last 30 days', 'wp-statistics'),
            '60days'                => __('Last 60 days', 'wp-statistics'),
            '90days'                => __('Last 90 days', 'wp-statistics'),
            'year'                  => __('Last 12 months', 'wp-statistics'),
            'this-year'             => __('This year (Jan-Today)', 'wp-statistics'),
            'last-year'             => __('Last year', 'wp-statistics'),
            'total'                 => __('Total', 'wp-statistics'),
            'daily_total'           => __('Daily Total', 'wp-statistics'),
            'date'                  => __('Date', 'wp-statistics'),
            'time'                  => __('Time', 'wp-statistics'),
            'browsers'              => __('Browsers', 'wp-statistics'),
            'rank'                  => __('#', 'wp-statistics'),
            'flag'                  => __('Country Flag', 'wp-statistics'),
            'country'               => __('Country', 'wp-statistics'),
            'visitor_count'         => __('Visitors', 'wp-statistics'),
            'id'                    => __('ID', 'wp-statistics'),
            'title'                 => __('Page Title', 'wp-statistics'),
            'link'                  => __('Page Link', 'wp-statistics'),
            'address'               => __('Domain Address', 'wp-statistics'),
            'word'                  => __('Search Term', 'wp-statistics'),
            'browser'               => __('Visitor\'s Browser', 'wp-statistics'),
            'city'                  => __('Visitor\'s City', 'wp-statistics'),
            'ip'                    => Option::get('hash_ips') == true ? __('Daily Visitor Hash', 'wp-statistics') : __('IP Address', 'wp-statistics'),
            'referrer'              => __('Referring Site', 'wp-statistics'),
            'hits'                  => __('Views', 'wp-statistics'),
            'agent'                 => __('User Agent', 'wp-statistics'),
            'platform'              => __('Operating System', 'wp-statistics'),
            'version'               => __('Browser/OS Version', 'wp-statistics'),
            'page'                  => __('Visited Page', 'wp-statistics'),
            'str_today'             => __('Today', 'wp-statistics'),
            'str_yesterday'         => __('Yesterday', 'wp-statistics'),
            'str_7days'             => __('Last 7 days', 'wp-statistics'),
            'str_14days'            => __('Last 14 days', 'wp-statistics'),
            'str_30days'            => __('Last 30 days', 'wp-statistics'),
            'str_last_month'        => __('Last Month', 'wp-statistics'),
            'str_60days'            => __('Last 60 days', 'wp-statistics'),
            'str_90days'            => __('Last 90 days', 'wp-statistics'),
            'str_120days'           => __('Last 120 days', 'wp-statistics'),
            'str_6months'           => __('Last 6 months', 'wp-statistics'),
            'str_year'              => __('This year', 'wp-statistics'),
            'str_back'              => __('Go Back', 'wp-statistics'),
            'str_custom'            => __('Select Custom Range...', 'wp-statistics'),
            'str_more'              => __('Additional Date Ranges', 'wp-statistics'),
            'custom'                => __('Custom Date Range', 'wp-statistics'),
            'to'                    => __('To (End Date)', 'wp-statistics'),
            'from'                  => __('From (Start Date)', 'wp-statistics'),
            'go'                    => __('Apply Range', 'wp-statistics'),
            'no_data'               => __('Sorry, there\'s no data available for this selection.', 'wp-statistics'),
            'count'                 => __('Total Number', 'wp-statistics'),
            'percentage'            => __('Percent Share', 'wp-statistics'),
            'version_list'          => __('Version', 'wp-statistics'),
            'filter'                => __('Apply Filters', 'wp-statistics'),
            'all'                   => __('All Entries', 'wp-statistics'),
            'er_datepicker'         => __('Select Desired Time Range', 'wp-statistics'),
            'er_valid_ip'           => __('Enter a Valid IP Address', 'wp-statistics'),
            'please_wait'           => __('Loading, Please Wait...', 'wp-statistics'),
            'user'                  => __('User Information', 'wp-statistics'),
            'rest_connect'          => __('Error connecting to WordPress REST API. Disable ad-blocker for this page or unblock /wp-json/wp-statistics/v2/metabox in the ad-blocker configuration.', 'wp-statistics'),
            'privacy_compliant'     => __('Your WP Statistics settings are privacy-compliant.', 'wp-statistics'),
            'non_privacy_compliant' => __('Your WP Statistics settings are not privacy-compliant. Please update your settings.', 'wp-statistics'),
            'privacy_resolve_alert' => __('By manually resolving this item, please ensure your website’s privacy policy is updated to accurately reflect this setting. This is essential for maintaining compliance and transparency with your users.', 'wp-statistics'),
        );

        // Rest-API Meta Box Url
        $list['admin_url']      = admin_url();
        $list['assets_url']     = self::$plugin_url . self::$asset_dir;
        $list['rest_api_nonce'] = wp_create_nonce('wp_rest');
        $list['meta_box_api']   = admin_url('admin-ajax.php?action=wp_statistics_admin_meta_box');

        // Meta Box List
        $meta_boxes_list    = Meta_Box::getList();
        $list['meta_boxes'] = array();

        foreach ($meta_boxes_list as $meta_box => $value) {

            // Convert Page Url
            if (isset($value['page_url'])) {
                $value['page_url'] = Menus::get_page_slug($value['page_url']);
            }

            // Add Post ID Params To Post Widget Link
            if ($meta_box == "post" and isset($post) and isset($post->ID) and in_array($post->post_status, array("publish", "private"))) {

                $value['page_url'] = add_query_arg(array(
                    'ID'   => $post->ID,
                    'type' => Pages::get_post_type($post->ID),
                ), $value['page_url']);

                /**
                 * Convert ? to & because ? is appending in the prefix of page_url out side of functionality.
                 * @note Annoying architecture...
                 * @since 13.0.7
                 */
                $value['page_url'] = str_replace('?', '&', $value['page_url']);
            }

            // Remove unnecessary params
            foreach (array('show_on_dashboard', 'hidden', 'place', 'require', 'js', 'disable_overview') as $param) {
                unset($value[$param]);
            }

            // Add Meta Box Lang
            $class = Meta_Box::getMetaBoxClass($meta_box);
            if (method_exists($class, 'lang')) {
                $value['lang'] = $class::lang();
            }

            //Push to List
            $list['meta_boxes'][$meta_box] = $value;
        }

        // Ads For Overview Pages
        if (Menus::in_page('overview')) {
            $overview_ads = get_option('wp_statistics_overview_page_ads', false);
            if ($overview_ads != false and is_array($overview_ads) and $overview_ads['ads']['ID'] != $overview_ads['view'] and $overview_ads['ads']['status'] == "yes") {

                if ($overview_ads['ads']['link']) {
                    $overview_ads['ads']['link'] = add_query_arg(array(
                        'utm_source'   => 'wp-statistics',
                        'utm_medium'   => 'plugin',
                        'utm_campaign' => 'overview-page',
                        'referrer'     => get_bloginfo('url'),
                    ), $overview_ads['ads']['link']);
                }

                $list['overview']['ads'] = $overview_ads['ads'];
            }
        }

        // Return Data JSON
        return $list;
    }

    /**
     * Localize jquery datepicker
     *
     * @see https://gist.github.com/mehrshaddarzi/7f661baeb5d801961deb8b821157e820
     */
    public static function localize_jquery_datepicker()
    {
        global $wp_locale;

        return array(
            'closeText'       => __('Action Completed', 'wp-statistics'),
            'currentText'     => __('Today', 'wp-statistics'),
            'monthNames'      => Helper::strip_array_indices($wp_locale->month),
            'monthNamesShort' => Helper::strip_array_indices($wp_locale->month_abbrev),
            'monthStatus'     => __('Display Other Month', 'wp-statistics'),
            'dayNames'        => Helper::strip_array_indices($wp_locale->weekday),
            'dayNamesShort'   => Helper::strip_array_indices($wp_locale->weekday_abbrev),
            'dayNamesMin'     => Helper::strip_array_indices($wp_locale->weekday_initial),
            'dateFormat'      => 'yy-mm-dd', // Format time for Jquery UI
            'firstDay'        => get_option('start_of_week'),
            'isRTL'           => $wp_locale->is_rtl(),
        );
    }

    /**
     * Init FeedbackBird widget a third-party service to get feedbacks from users
     *
     * @url https://feedbackbird.io
     *
     * @return void
     */
    private function initFeedback()
    {
        add_action('admin_enqueue_scripts', function () {
            $screen = get_current_screen();

            if (stristr($screen->id, 'wps_')) {
                wp_enqueue_script('feedbackbird-widget', 'https://cdn.jsdelivr.net/gh/feedbackbird/assets@master/wp/app.js?uid=01H34YMWXSA9XPS61M4S11RV6Z', [], self::version(), false);
                wp_add_inline_script('feedbackbird-widget', sprintf('var feedbackBirdObject = %s;', wp_json_encode([
                    'user_email' => function_exists('wp_get_current_user') ? wp_get_current_user()->user_email : '',
                    'platform'   => 'wordpress-admin',
                    'config'     => [
                        'color'         => '#2831bc',
                        'button'        => __('Feedback', 'wp-sms'),
                        'subtitle'      => __('Feel free to share your thoughts!', 'wp-sms'),
                        'opening_style' => 'modal',
                    ],
                    'meta'       => [
                        'php_version'    => PHP_VERSION,
                        'active_plugins' => array_map(function ($plugin, $pluginPath) {
                            return [
                                'name'    => $plugin['Name'],
                                'version' => $plugin['Version'],
                                'status'  => is_plugin_active($pluginPath) ? 'active' : 'deactivate',
                            ];
                        }, get_plugins(), array_keys(get_plugins())),
                    ]
                ])));

                add_filter('script_loader_tag', function ($tag, $handle, $src) {
                    if ('feedbackbird-widget' === $handle) {
                        return preg_replace('/^<script /i', '<script type="module" crossorigin="crossorigin" ', $tag);
                    }
                    return $tag;
                }, 10, 3);
            }
        });
    }
}

new Admin_Assets;
