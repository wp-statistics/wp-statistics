<?php

namespace WP_Statistics\Service\Admin\Assets\Handlers;

use WP_Statistics\Abstracts\AdminAssets;
use WP_STATISTICS\Option;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Components\Assets;
use WP_STATISTICS\Components\DateRange;
use WP_STATISTICS\Components\DateTime;
use WP_STATISTICS\Service\Admin\Metabox\MetaboxHelper;
use WP_STATISTICS\Utils\Request;

/**
 * Legacy Admin Assets Service
 *
 * Handles WordPress admin legacy assets (CSS/JS) in WP Statistics plugin.
 * Manages loading and enqueuing of styles and scripts for the admin interface.
 *
 * @package WP_STATISTICS\Service\Admin\Assets
 * @since   15.0.0
 */
class LegacyHandler extends AdminAssets
{
    /**
     * Initialize the assets manager
     *
     * @return void
     * @since 15.0.0
     */
    public function __construct()
    {
        $this->setContext('legacy');
        add_action('admin_enqueue_scripts', [$this, 'adminStyles'], 999);
        add_action('admin_enqueue_scripts', [$this, 'adminScripts'], 999);
        add_filter('wp_statistics_enqueue_chartjs', [$this, 'shouldEnqueueChartJs']);
    }

    /**
     * Register and enqueue admin styles
     *
     * @return void
     * @since 15.0.0
     */
    public function adminStyles()
    {
        // Get Current Screen ID
        $screenId = Helper::get_screen_id();

        // Load Admin Css
        wp_enqueue_style($this->getAssetHandle(), $this->getUrl('admin.min.css'), [], $this->getVersion());

        // Load Rtl Version Css
        if (is_rtl()) {
            wp_enqueue_style($this->getAssetHandle('rtl'), $this->getUrl('rtl.min.css'), [], $this->getVersion());
        }

        //Load Jquery VMap Css
        if (Menus::in_page('overview') || Menus::in_page('pages') || (in_array($screenId, ['dashboard']) && !Option::get('disable_dashboard'))) {
            wp_enqueue_style($this->getAssetHandle('jqvmap'), $this->getUrl('jqvmap/jqvmap.min.css'), [], '1.5.1');
        }

        // Load Select2
        if (Menus::in_page('visitors') || Menus::in_page('referrals') || Menus::in_page('link_tracker') || Menus::in_page('download_tracker') || Menus::in_page('pages')) {
            wp_enqueue_style($this->getAssetHandle('select2'), $this->getUrl('select2/select2.min.css'), [], '4.0.9');
        }

        // Load RangeDatePicker
        if (Menus::in_plugin_page() || Menus::in_page('pages') || in_array($screenId, ['dashboard'])) {
            wp_enqueue_style($this->getAssetHandle('daterangepicker'), $this->getUrl('datepicker/daterangepicker.css'), [], '1.0.0');
            wp_enqueue_style($this->getAssetHandle('customize'), $this->getUrl('datepicker/customize.css'), [], '1.0.0');
        }
    }

    /**
     * Register and enqueue admin scripts
     *
     * @param string $hook Current admin page hook
     * @return void
     * @since 15.0.0
     */
    public function adminScripts($hook)
    {
        // Get Current Screen ID
        $screenId = Helper::get_screen_id();

        // Load Chart.js library
        if (apply_filters('wp_statistics_enqueue_chartjs', false)) {
            Assets::script('chart.js', 'js/chartjs/chart.umd.min.js', [], [], true, false, null, '4.4.4');
        }

        // Load mini-chart
        if (Helper::isAdminBarShowing()) {
            Assets::script('mini-chart', 'js/mini-chart.js', [], [], true);
        }

        if (Menus::in_page('author-analytics')) {
            wp_enqueue_script($this->getAssetHandle('chart-matrix'), $this->getUrl('chartjs/chart-matrix.min.js'), [], '2.0.8', true);
        }

        // Load Jquery VMap Js Library
        if (Menus::in_page('overview') || Menus::in_page('pages') || (in_array($screenId, ['dashboard']) && !Option::get('disable_dashboard'))) {
            wp_enqueue_script($this->getAssetHandle('jqvmap'), $this->getUrl('jqvmap/jquery.vmap.min.js'), ['jquery'], "1.5.1", true);
            wp_enqueue_script($this->getAssetHandle('jqvmap-world'), $this->getUrl('jqvmap/jquery.vmap.world.min.js'), ['jquery'], "1.5.1", true);
        }

        // Load Select2
        if (Menus::in_page('visitors') || Menus::in_page('referrals') || Menus::in_page('link_tracker') || Menus::in_page('download_tracker') || Menus::in_page('pages')) {
            wp_enqueue_script($this->getAssetHandle('select2'), $this->getUrl('select2/select2.full.min.js'), ['jquery'], "4.1.0", true);
        }

        // Load WordPress PostBox Script
        if (Menus::in_plugin_page() && Menus::in_page('optimization') === false && Menus::in_page('settings') === false) {
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
            Menus::in_plugin_page() || (in_array($screenId, ['dashboard']) && !Option::get('disable_dashboard')) ||
            (in_array($hook, ['post.php', 'edit.php']) && !Option::get('disable_editor')) ||
            (in_array($hook, ['post.php', 'edit.php']) && Helper::isAddOnActive('data-plus') && Option::getByAddon('latest_visitors_metabox', 'data_plus', '1') === '1')
        ) {
            wp_enqueue_script($this->getAssetHandle(), $this->getUrl('admin.min.js'), ['jquery'], $this->getVersion(), true);
            wp_localize_script($this->getAssetHandle(), 'wps_global', $this->getLocalizedData($hook));
        }

        // Load TinyMCE for Widget Page
        if (in_array($screenId, ['widgets'])) {
            wp_enqueue_script($this->getAssetHandle('button-widget'), $this->getUrl('tinymce.min.js'), ['jquery'], "3.2.5", true);
        }

        // Add Thick box
        if (Menus::in_page('visitors') || Menus::in_page('visitors-report') || Menus::in_page('referrals') || Menus::in_page('pages')) {
            wp_enqueue_script('thickbox');
            wp_enqueue_style('thickbox');
        }

        // Add RangeDatePicker
        if (Menus::in_plugin_page() || Menus::in_page('pages') || in_array($screenId, ['dashboard'])) {
            wp_enqueue_script($this->getAssetHandle('moment'), $this->getUrl('datepicker/moment.min.js'), [], "2.30.2", true);
            wp_enqueue_script($this->getAssetHandle('daterangepicker'), $this->getUrl('datepicker/daterangepicker.min.js'), [], "1.13.2", true);
        }

        if (Menus::in_page('pages')) {
            wp_enqueue_script($this->getAssetHandle('datepicker'), $this->getUrl('datepicker/datepicker.js'), [], $this->getVersion(), true);
        }
    }

    /**
     * Get localized data for JavaScript
     *
     * @param string $hook Current admin page hook
     * @return array Localized data for JavaScript
     * @since 15.0.0
     */
    protected function getLocalizedData($hook)
    {
        $list = parent::getLocalizedData($hook);

        //Global Option
        $list['options'] = array_merge($list['options'], [
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
            'wp_timezone'    => (new DateTime())->getTimezone()->getName()
        ]);

        //Global i18n
        $list['i18n'] = $this->getI18nStrings();

        $list['active_post_type']  = Helper::getPostTypeName(Request::get('pt', 'post'));
        $list['user_date_range']   = DateRange::get();
        $list['initial_post_date'] = Helper::getInitialPostDate();

        if (Request::has('post_id')) {
            $list['post_creation_date'] = get_the_date(DateTime::$defaultDateFormat, Request::get('post_id'));
        } else if (is_singular()) {
            $list['post_creation_date'] = get_the_date(DateTime::$defaultDateFormat);
        }

        // Rest-API Meta Box Url
        $list['stats_report_option'] = Option::get('time_report') == '0' ? false : true;
        $list['setting_url']         = Menus::admin_url('settings');
        $list['meta_boxes']          = MetaboxHelper::getScreenMetaboxes();

        return apply_filters('wp_statistics_admin_assets', $list);
    }

    /**
     * Get internationalization strings
     *
     * @return array Array of translated strings
     * @since 15.0.0
     */
    protected function getI18nStrings()
    {
        return [
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
            'online_for'                   => __('Online For', 'wp-statistics'),
            'views'                        => __('Views', 'wp-statistics'),
            'view'                         => __('View', 'wp-statistics'),
            'waiting'                      => __('Waiting', 'wp-statistics'),
            'apply'                        => __('Apply'),
            'reset'                        => __('Reset'),
            'loading'                      => __('Loading'),
            'go_to_overview'               => __('Go to Overview'),
            'continue_to_next_step'        => __('Continue to Next Step', 'wp-statistics'),
            'action_required'              => __('Action Required', 'wp-statistics'),
            'show_less'                    => __('Show less', 'wp-statistics'),
            'show_more'                    => __('Show more', 'wp-statistics'),
            'pending'                      => __('Pending', 'wp-statistics'),
            'start_of_week'                => get_option('start_of_week', 0)
        ];
    }

    /**
     * Checks if Chart.js library should be enqueued
     *
     * @return bool Whether Chart.js should be enqueued
     * @since 15.0.0
     */
    public function shouldEnqueueChartJs()
    {
        global $pagenow;

        return (Helper::isAddOnActive('mini-chart') && Helper::isAdminBarShowing()) ||
            Menus::in_plugin_page() ||
            (in_array(Helper::get_screen_id(), ['dashboard']) && !Option::get('disable_dashboard')) ||
            (in_array($pagenow, ['post.php', 'edit.php']) && !Option::get('disable_editor')) ||
            (in_array($pagenow, ['post.php', 'edit.php']) && Helper::isAddOnActive('data-plus') && Option::getByAddon('latest_visitors_metabox', 'data_plus', '1') === '1');
    }
}