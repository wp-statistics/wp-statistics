<?php

namespace WP_STATISTICS;

use WP_Statistics\Utils\Request;
use WP_Statistics\Components\Assets;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Service\Admin\Metabox\MetaboxHelper;

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
     * Basic Of Plugin Url in WordPress
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
        add_action('admin_enqueue_scripts', array($this, 'admin_styles'), 999);
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'), 999);
        add_filter('wp_statistics_enqueue_chartjs', [$this, 'shouldEnqueueChartJs']);
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
     * Enqueue dashboard page styles.
     */

    public function dashboard_styles()
    {
        // Load Dashboard Css
        wp_enqueue_style(self::$prefix . '-dashboard', self::url('dashboard.min.css'), array(), self::version());
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
        if (Menus::in_page('overview') || Menus::in_page('pages') || Menus::in_page('geographic') || Menus::in_page('visitors') || (in_array($screen_id, array('dashboard')) and !Option::get('disable_dashboard'))) {
            wp_enqueue_style(self::$prefix . '-jqvmap', self::url('jqvmap/jqvmap.min.css'), array(), '1.5.1');
        }

        // Load Jquery-ui theme
        //        if (Menus::in_plugin_page() and Menus::in_page('optimization') === false and Menus::in_page('settings') === false) {
        //            wp_enqueue_style(self::$prefix . '-jquery-datepicker', self::url('datepicker.min.css'), array(), '1.11.4');
        //        }

        // Load Select2
        if (Menus::in_page('visitors') || Menus::in_page('referrals') || Menus::in_page('link_tracker') || Menus::in_page('download_tracker') || Menus::in_page('pages') || Menus::in_page('settings') || Menus::in_page('optimization')  || Menus::in_page('goals')) {
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

        // Load Chart.js library
        if (apply_filters('wp_statistics_enqueue_chartjs', false)) {
            Assets::script('chart.js', 'js/chartjs/chart.umd.min.js', [], [], true, false, null, '4.4.4');
        }

        // Load mini-chart
        if (Helper::isAdminBarShowing()) {
            Assets::script('mini-chart', 'js/mini-chart.js', [], [], true);
        }

        if (Menus::in_page('author-analytics')) {
            wp_enqueue_script(self::$prefix . '-chart-matrix.js', self::url('chartjs/chart-matrix.min.js'), [], '2.0.8', true);
        }

        // Load Jquery VMap Js Library
        if (Menus::in_page('overview') || Menus::in_page('pages') || Menus::in_page('geographic') || Menus::in_page('visitors') || (in_array($screen_id, array('dashboard')) and !Option::get('disable_dashboard'))) {
            wp_enqueue_script(self::$prefix . '-jqvmap', self::url('jqvmap/jquery.vmap.min.js'), array('jquery'), "1.5.1", ['in_footer' => true]);
            wp_enqueue_script(self::$prefix . '-jqvmap-world', self::url('jqvmap/jquery.vmap.world.min.js'), array('jquery'), "1.5.1", ['in_footer' => true]);
        }


        // Load Jquery UI
        //        if (Menus::in_plugin_page() and Menus::in_page('optimization') === false and Menus::in_page('settings') === false) {
        //            wp_enqueue_script('jquery-ui-datepicker');
        //            wp_localize_script('jquery-ui-datepicker', 'wps_i18n_jquery_datepicker', self::localize_jquery_datepicker());
        //        }

        // Load Select2
        if (Menus::in_page('visitors') || Menus::in_page('referrals') || Menus::in_page('link_tracker') || Menus::in_page('download_tracker') || Menus::in_page('pages') || Menus::in_page('settings') || Menus::in_page('optimization') || Menus::in_page('goals')) {
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
        if (
            Menus::in_plugin_page() || (in_array($screen_id, ['dashboard']) && !Option::get('disable_dashboard')) ||
            (in_array($hook, ['post.php', 'edit.php']) && !Option::get('disable_editor')) ||
            (in_array($hook, ['post.php', 'edit.php']) && Helper::isAddOnActive('data-plus') && Option::getByAddon('latest_visitors_metabox', 'data_plus', '1') === '1')
        ) {
            wp_enqueue_script(self::$prefix, self::url('admin.min.js'), array('jquery'), self::version(), ['in_footer' => true]);
            wp_localize_script(self::$prefix, 'wps_global', self::wps_global($hook));
        }

        // Load TinyMCE for Widget Page
        if (in_array($screen_id, array('widgets'))) {
            wp_enqueue_script(self::$prefix . '-button-widget', self::url('tinymce.min.js'), array('jquery'), "3.2.5", ['in_footer' => true]);
        }

        // Add Thick box
        if (Menus::in_page('visitors') || Menus::in_page('visitors-report') || Menus::in_page('referrals') || Menus::in_page('pages')) {
            wp_enqueue_script('thickbox');
            wp_enqueue_style('thickbox');
        }

        // Add RangeDatePicker
        if (Menus::in_plugin_page() || Menus::in_page('pages') || in_array($screen_id, array('dashboard'))) {
            wp_enqueue_script(self::$prefix . '-moment', self::url('datepicker/moment.min.js'), array(), "2.30.2", ['in_footer' => true]);
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
            'rtl'            => (is_rtl() ? 1 : 0),
            'user_online'    => (Option::get('useronline') ? 1 : 0),
            'visitors'       => 1,
            'visits'         => 1,
            'geo_ip'         => 1,
            'geo_city'       => 1,
            'overview_page'  => (Menus::in_page('overview') ? 1 : 0),
            'gutenberg'      => (Helper::is_gutenberg() ? 1 : 0),
            'more_btn'       => (apply_filters('wp_statistics_meta_box_more_button', true) ? 1 : 0),
            'wp_date_format' => Helper::getDefaultDateFormat(),
            'track_users'    => Option::get('visitors_log') ? 1 : 0,
            'wp_timezone'    => DateTime::getTimezone()->getName()
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
                if (!is_array($value)) {
                    $list['request_params'][esc_html($key)] = esc_html($value);
                } else {
                    // Ensure each value in the array is escaped properly
                    $value = array_map('esc_html', $value);
                    // Assign the entire escaped array to the request_params array
                    $list['request_params'][esc_html($key)] = $value;
                }
            }
        }

        // Global Lang
        $list['i18n'] = array(
            'more_detail'                  => __('View Details', 'wp-statistics'),
            'reload'                       => __('Reload', 'wp-statistics'),
            'online_users'                 => __('Online Visitors', 'wp-statistics'),
            'Realtime'                     => __('Realtime', 'wp-statistics'),
            'visitors'                     => __('Visitors', 'wp-statistics'),
            'visits'                       => __('Views', 'wp-statistics'),
            'today'                        => __('Today', 'wp-statistics'),
            'yesterday'                    => __('Yesterday', 'wp-statistics'),
            'week'                         => __('Last 7 days', 'wp-statistics'),
            'this-week'                    => __('This week', 'wp-statistics'),
            'last-week'                    => __('Last week', 'wp-statistics'),
            'month'                        => __('Last 30 days', 'wp-statistics'),
            'this-month'                   => __('This month', 'wp-statistics'),
            'last-month'                   => __('Last month', 'wp-statistics'),
            '7days'                        => __('Last 7 days', 'wp-statistics'),
            '30days'                       => __('Last 30 days', 'wp-statistics'),
            '60days'                       => __('Last 60 days', 'wp-statistics'),
            '90days'                       => __('Last 90 days', 'wp-statistics'),
            '6months'                      => __('Last 6 months', 'wp-statistics'),
            'year'                         => __('Last 12 months', 'wp-statistics'),
            'this-year'                    => __('This year (Jan-Today)', 'wp-statistics'),
            'last-year'                    => __('Last year', 'wp-statistics'),
            'total'                        => __('Total', 'wp-statistics'),
            'daily_total'                  => __('Daily Total', 'wp-statistics'),
            'date'                         => __('Date', 'wp-statistics'),
            'time'                         => __('Time', 'wp-statistics'),
            'browsers'                     => __('Browsers', 'wp-statistics'),
            'rank'                         => __('#', 'wp-statistics'),
            'flag'                         => __('Country Flag', 'wp-statistics'),
            'country'                      => __('Country', 'wp-statistics'),
            'visitor_count'                => __('Visitors', 'wp-statistics'),
            'id'                           => __('ID', 'wp-statistics'),
            'title'                        => __('Page', 'wp-statistics'),
            'link'                         => __('Page Link', 'wp-statistics'),
            'address'                      => __('Domain Address', 'wp-statistics'),
            'word'                         => __('Search Term', 'wp-statistics'),
            'browser'                      => __('Visitor\'s Browser', 'wp-statistics'),
            'city'                         => __('Visitor\'s City', 'wp-statistics'),
            'ip'                           => Option::get('hash_ips') == true ? __('Daily Visitor Hash', 'wp-statistics') : __('IP Address', 'wp-statistics'),
            'ip_hash'                      => __('IP Address/Hash', 'wp-statistics'),
            'ip_hash_placeholder'          => __('Enter IP (e.g., 192.168.1.1) or hash (#...)', 'wp-statistics'),
            'referring_site'               => __('Referring Site', 'wp-statistics'),
            'hits'                         => __('Views', 'wp-statistics'),
            'agent'                        => __('User Agent', 'wp-statistics'),
            'platform'                     => __('Operating System', 'wp-statistics'),
            'version'                      => __('Browser/OS Version', 'wp-statistics'),
            'page'                         => __('Visited Page', 'wp-statistics'),
            'str_today'                    => __('Today', 'wp-statistics'),
            'str_yesterday'                => __('Yesterday', 'wp-statistics'),
            'str_this_week'                => __('This week', 'wp-statistics'),
            'str_last_week'                => __('Last week', 'wp-statistics'),
            'str_this_month'               => __('This month', 'wp-statistics'),
            'str_last_month'               => __('Last month', 'wp-statistics'),
            'str_7days'                    => __('Last 7 days', 'wp-statistics'),
            'str_28days'                   => __('Last 28 days', 'wp-statistics'),
            'str_30days'                   => __('Last 30 days', 'wp-statistics'),
            'str_90days'                   => __('Last 90 days', 'wp-statistics'),
            'str_6months'                  => __('Last 6 months', 'wp-statistics'),
            'str_year'                     => __('This year', 'wp-statistics'),
            'str_this_year'                => __('This year', 'wp-statistics'),
            'str_last_year'                => __('Last year', 'wp-statistics'),
            'str_back'                     => __('Go Back', 'wp-statistics'),
            'str_custom'                   => __('Select Custom Range...', 'wp-statistics'),
            'custom_range'                 => __('Custom Range', 'wp-statistics'),
            'all_time'                     => __('All time', 'wp-statistics'),
            'str_more'                     => __('Additional Date Ranges', 'wp-statistics'),
            'custom'                       => __('Custom Date Range', 'wp-statistics'),
            'to'                           => __('To (End Date)', 'wp-statistics'),
            'from'                         => __('From (Start Date)', 'wp-statistics'),
            'go'                           => __('Apply Range', 'wp-statistics'),
            'no_data'                      => __('Sorry, there\'s no data available for this selection.', 'wp-statistics'),
            'count'                        => __('Total Number', 'wp-statistics'),
            'percentage'                   => __('Percent Share', 'wp-statistics'),
            'version_list'                 => __('Version', 'wp-statistics'),
            'filter'                       => __('Apply Filters', 'wp-statistics'),
            'filters'                      => __('Filters', 'wp-statistics'),
            'all'                          => __('All', 'wp-statistics'),
            'er_datepicker'                => __('Select Desired Time Range', 'wp-statistics'),
            'er_valid_ip'                  => __('Please enter a valid IP (e.g., 192.168.1.1) or hash (starting with #)', 'wp-statistics'),
            'please_wait'                  => __('Loading, Please Wait...', 'wp-statistics'),
            'user'                         => __('User', 'wp-statistics'),
            'rest_connect'                 => __('Failed to retrieve data. Please check the browser console and the XHR request under Network → XHR for details.', 'wp-statistics'),
            'privacy_compliant'            => __('Your WP Statistics settings are privacy-compliant.', 'wp-statistics'),
            'non_privacy_compliant'        => __('Your WP Statistics settings are not privacy-compliant. Please update your settings.', 'wp-statistics'),
            'no_result'                    => __('No recent data available.', 'wp-statistics'),
            'published'                    => __('Published', 'wp-statistics'),
            'author'                       => __('Author', 'wp-statistics'),
            'view_detailed_analytics'      => __('View Detailed Analytics', 'wp-statistics'),
            'enable_now'                   => __('Enable Now', 'wp-statistics'),
            'receive_weekly_email_reports' => __('Receive Weekly Email Reports', 'wp-statistics'),
            'close'                        => __('Close', 'wp-statistics'),
            'previous_period'              => __('Previous period', 'wp-statistics'),
            'view_content'                 => __('View Content', 'wp-statistics'),
            'downloading'                  => __('Downloading', 'wp-statistics'),
            'activated'                    => __('Activated', 'wp-statistics'),
            'active'                       => __('Active', 'wp-statistics'),
            'activating'                   => __('Activating', 'wp-statistics'),
            'already_installed'            => __('Already installed', 'wp-statistics'),
            'failed'                       => __('Failed', 'wp-statistics'),
            'retry'                        => __('Retry', 'wp-statistics'),
            'redirecting'                  => __('Redirecting... Please wait', 'wp-statistics'),
            'last_view'                    => __('Last View', 'wp-statistics'),
            'visitor_info'                 => __('Visitor Info', 'wp-statistics'),
            'location'                     => __('Location', 'wp-statistics'),
            'name'                         => __('Name', 'wp-statistics'),
            'email'                        => __('Email', 'wp-statistics'),
            'role'                         => __('Role', 'wp-statistics'),
            'latest_page'                  => __('Latest Page', 'wp-statistics'),
            'referrer'                     => __('Referrer', 'wp-statistics'),
            'source_channel'               => __('Source Category', 'wp-statistics'),
            'online_for'                   => __('Online For', 'wp-statistics'),
            'views'                        => __('Views', 'wp-statistics'),
            'view'                         => __('View', 'wp-statistics'),
            'waiting'                      => __('Waiting', 'wp-statistics'),
            'apply'                        => __('Apply', 'wp-statistics'),
            'reset'                        => __('Reset', 'wp-statistics'),
            'loading'                      => __('Loading', 'wp-statistics'),
            'go_to_overview'               => __('Go to Overview', 'wp-statistics'),
            'continue_to_next_step'        => __('Continue to Next Step', 'wp-statistics'),
            'action_required'              => __('Action Required', 'wp-statistics'),
            'show_less'                    => __('Show less', 'wp-statistics'),
            'show_more'                    => __('Show more', 'wp-statistics'),
            'pending'                      => __('Pending', 'wp-statistics'),
            'copied'                       => __('Copied!', 'wp-statistics'),
            'settings'                     => __('SETTINGS', 'wp-statistics'),
            'premium_addons'               => __('PREMIUM ADD-ONS', 'wp-statistics'),
            'clicks'                       => __('Clicks', 'wp-statistics'),
            'impressions'                  => __('Impressions', 'wp-statistics'),
            'prev'                         => __('Prev', 'wp-statistics'),
            'next'                         => __('Next', 'wp-statistics'),
            'loading_error'                => __('Oops, something went wrong while loading statistics.', 'wp-statistics'),
            'last_updated'                 => __('Last updated:', 'wp-statistics'),
            'unassigned'                   => __('Unassigned', 'wp-statistics'),
            'select_page'                  => __('Select page', 'wp-statistics'),
            'required_error'               => __('This field is required', 'wp-statistics'),
            'validate_error'               => __('Must not contain spaces, #, or .', 'wp-statistics'),
            'machine_validate_error'       => __('Please use lowercase letters, numbers, underscores, or dashes only. No spaces allowed.', 'wp-statistics'),
            'start_of_week'                => get_option('start_of_week', 0)
        );

        $list['active_post_type'] = Helper::getPostTypeName(Request::get('pt', 'post'));
        $list['user_date_range']  = DateRange::get();

        $list['initial_post_date'] = Helper::getInitialPostDate();

        if (Request::has('post_id')) {
            $list['post_creation_date'] = get_post_time(DateTime::$defaultDateFormat, false, Request::get('post_id'), false);
        } else if (is_singular()) {
            $list['post_creation_date'] = get_post_time(DateTime::$defaultDateFormat, false, null, false);
        }

        // Rest-API Meta Box Url
        $list['stats_report_option'] = Option::get('time_report') == '0' ? false : true;
        $list['setting_url']         = Menus::admin_url('settings');
        $list['admin_url']           = admin_url();
        $list['ajax_url']            = admin_url('admin-ajax.php');
        $list['assets_url']          = self::$plugin_url . self::$asset_dir;
        $list['rest_api_nonce']      = wp_create_nonce('wp_rest');
        $list['meta_box_api']        = admin_url('admin-ajax.php?action=wp_statistics_admin_meta_box');

        // For developers: WordPress debugging mode.
        $list['wp_debug'] = defined('WP_DEBUG') && WP_DEBUG ? true : false;

        $list['meta_boxes'] = MetaboxHelper::getScreenMetaboxes();

        /**
         * Filter: wp_statistics_admin_assets
         *
         * @since 14.9.4
         */
        return apply_filters('wp_statistics_admin_assets', $list);
    }

    /**
     * Checks if any of the conditions for enqueuing Chart.js library are met.
     *
     * Conditions are:
     * - Mini Chart add-on is enabled and admin bar button is showing.
     * - User is currently viewing the WP Statistics admin pages (e.g. Settings, Overview, Optimization, etc.).
     * - User is currently viewing WP dashboard and `disable_dashboard` option is not disabled.
     * - User is currently in edit post page and `disable_editor` is disabled.
     * - User is currently in edit post page and `latest_visitors_metabox` is enabled.
     *
     * @return  bool
     *
     * @hooked  filter: `wp_statistics_enqueue_chartjs` - 10
     */
    public function shouldEnqueueChartJs()
    {
        global $pagenow;

        return (Helper::isAddOnActive('mini-chart') && Helper::isAdminBarShowing()) || Menus::in_plugin_page() ||
            (in_array(Helper::get_screen_id(), ['dashboard']) && !Option::get('disable_dashboard')) ||
            (in_array($pagenow, ['post.php', 'edit.php']) && !Option::get('disable_editor')) ||
            (in_array($pagenow, ['post.php', 'edit.php']) && Helper::isAddOnActive('data-plus') && Option::getByAddon('latest_visitors_metabox', 'data_plus', '1') === '1');
    }
}

new Admin_Assets;
