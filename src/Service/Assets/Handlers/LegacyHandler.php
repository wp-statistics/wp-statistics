<?php

namespace WP_Statistics\Service\Assets\Handlers;

use WP_Statistics\Abstracts\BaseAssets;
use WP_Statistics\Components\Addons;
use WP_Statistics\Components\Assets;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Components\Menu;
use WP_Statistics\Components\Option;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\Metabox\MetaboxHelper;
use WP_Statistics\Utils\Post;
use WP_Statistics\Utils\PostType;
use WP_Statistics\Utils\Request;
use WP_Statistics\Utils\Route;

/**
 * Legacy Admin Assets Service
 *
 * Handles WordPress admin legacy assets (CSS/JS) in WP Statistics plugin.
 * Manages loading and enqueuing of styles and scripts for the admin interface.
 *
 * @package WP_STATISTICS\Service\Assets
 * @since   15.0.0
 */
class LegacyHandler extends BaseAssets
{
    /**
     * Initialize the assets manager
     *
     * @return void
     */
    public function __construct()
    {
        $this->setContext('legacy', true);
        add_action('admin_enqueue_scripts', [$this, 'styles'], 999);
        add_action('admin_enqueue_scripts', [$this, 'scripts'], 999);
        add_filter('wp_statistics_enqueue_chartjs', [$this, 'shouldEnqueueChartJs']);
    }

    /**
     * Register and enqueue admin styles
     *
     * @return void
     */
    public function styles()
    {
        // Get Current Screen ID
        $screenId = Route::getScreenId();

        // Load Admin Css
        wp_enqueue_style($this->getAssetHandle(), $this->getUrl('css/admin.min.css'), [], $this->getVersion());

        // Load Rtl Version Css
        if (is_rtl()) {
            wp_enqueue_style($this->getAssetHandle('rtl'), $this->getUrl('css/rtl.min.css'), [], $this->getVersion());
        }

        //Load Jquery VMap Css
        if (
            Menu::isOnPage('overview') ||
            Menu::isOnPage('pages') ||
            Menu::isOnPage('geographic') ||
            Menu::isOnPage('visitors') ||
            (in_array($screenId, ['dashboard']) && !Option::getValue('disable_dashboard'))
        ) {
            wp_enqueue_style($this->getAssetHandle('jqvmap'), $this->getUrl('css/jqvmap/jqvmap.min.css'), [], '1.5.1');
        }

        // Load Select2
        if (
            Menu::isOnPage('visitors') ||
            Menu::isOnPage('referrals') ||
            Menu::isOnPage('link_tracker') ||
            Menu::isOnPage('download_tracker') ||
            Menu::isOnPage('pages') ||
            Menu::isOnPage('goals') ||
            Menu::isOnPage('optimization') ||
            Menu::isOnPage('settings')
        ) {
            wp_enqueue_style($this->getAssetHandle('select2'), $this->getUrl('css/select2/select2.min.css'), [], '4.0.9');
        }

        // Load RangeDatePicker
        if (Menu::isInPluginPage() || Menu::isOnPage('pages') || in_array($screenId, ['dashboard'])) {
            wp_enqueue_style($this->getAssetHandle('daterangepicker'), $this->getUrl('css/datepicker/daterangepicker.css'), [], '1.0.0');
            wp_enqueue_style($this->getAssetHandle('customize'), $this->getUrl('css/datepicker/customize.css'), [], '1.0.0');
        }
    }

    /**
     * Register and enqueue admin scripts
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    public function scripts($hook = '')
    {
        // Get Current Screen ID
        $screenId = Route::getScreenId();

        // Load Chart.js library
        if (apply_filters('wp_statistics_enqueue_chartjs', false)) {
            Assets::script('chart.js', 'js/chartjs/chart.umd.min.js', [], [], true, false, null, '4.4.4', '', true, true);
        }

        // Load mini-chart
        if (Route::isAdminBarShowing()) {
            Assets::script('mini-chart', 'js/mini-chart.min.js', [], [], true, false, null, '', '', true);
        }

        if (Menu::isOnPage('author-analytics')) {
            wp_enqueue_script($this->getAssetHandle('chart-matrix'), $this->getUrl('js/chartjs/chart-matrix.min.js'), [], '2.0.8', true);
        }

        // Load Jquery VMap Js Library
        if (Menu::isOnPage('overview') || Menu::isOnPage('pages') || Menu::isOnPage('geographic') || Menu::isOnPage('visitors') || (in_array($screenId, ['dashboard']) && !Option::getValue('disable_dashboard'))) {
            wp_enqueue_script($this->getAssetHandle('jqvmap'), $this->getUrl('js/jqvmap/jquery.vmap.min.js'), ['jquery'], "1.5.1", ['in_footer' => true]);
            wp_enqueue_script($this->getAssetHandle('jqvmap-world'), $this->getUrl('js/jqvmap/jquery.vmap.world.min.js'), ['jquery'], "1.5.1", ['in_footer' => true]);
        }

        // Load Select2
        if (
            Menu::isOnPage('visitors') ||
            Menu::isOnPage('referrals') ||
            Menu::isOnPage('link_tracker') ||
            Menu::isOnPage('download_tracker') ||
            Menu::isOnPage('pages') || 
            Menu::isOnPage('goals') || 
            Menu::isOnPage('optimization') ||
            Menu::isOnPage('settings')
        ) {
            wp_enqueue_script($this->getAssetHandle('select2'), $this->getUrl('js/select2/select2.full.min.js'), ['jquery'], "4.1.0", ['in_footer' => true]);
        }

        // Load WordPress PostBox Script
        if (Menu::isInPluginPage() && Menu::isOnPage('optimization') === false && Menu::isOnPage('settings') === false) {
            wp_enqueue_script('common');
            wp_enqueue_script('wp-lists');
            wp_enqueue_script('postbox');
        }

        if (Menu::isOnPage('settings')) {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
        }

        // Load Admin Js
        if (
            Menu::isInPluginPage() || (in_array($screenId, ['dashboard']) && !Option::getValue('disable_dashboard')) ||
            (in_array($hook, ['post.php', 'edit.php']) && !Option::getValue('disable_editor')) ||
            (in_array($hook, ['post.php', 'edit.php']) && Addons::isActive('data-plus') && Option::getAddonValue('latest_visitors_metabox', 'data_plus', '1') === '1')
        ) {
            wp_enqueue_script($this->getAssetHandle(), $this->getUrl('js/admin.min.js'), ['jquery'], $this->getVersion(), ['in_footer' => true]);
            wp_localize_script($this->getAssetHandle(), 'wps_global', $this->getLocalizedData($hook));
        }

        // Load TinyMCE for Widget Page
        if (in_array($screenId, ['widgets'])) {
            wp_enqueue_script($this->getAssetHandle('button-widget'), $this->getUrl('js/tinymce.min.js'), ['jquery'], "3.2.5", ['in_footer' => true]);
        }

        // Add Thick box
        if (Menu::isOnPage('visitors') || Menu::isOnPage('visitors-report') || Menu::isOnPage('referrals') || Menu::isOnPage('pages')) {
            wp_enqueue_script('thickbox');
            wp_enqueue_style('thickbox');
        }

        // Add RangeDatePicker
        if (Menu::isInPluginPage() || Menu::isOnPage('pages') || in_array($screenId, ['dashboard'])) {
            wp_enqueue_script($this->getAssetHandle('moment'), $this->getUrl('js/datepicker/moment.min.js'), [], "2.30.2", ['in_footer' => true]);
            wp_enqueue_script($this->getAssetHandle('daterangepicker'), $this->getUrl('js/datepicker/daterangepicker.min.js'), [], "1.13.2", ['in_footer' => true]);
        }

        if (Menu::isOnPage('pages')) {
            wp_enqueue_script($this->getAssetHandle('datepicker'), $this->getUrl('js/datepicker/datepicker.js'), [], $this->getVersion(), ['in_footer' => true]);
        }
    }

    /**
     * Get localized data for JavaScript
     *
     * @param string $hook Current admin page hook
     * @return array Localized data for JavaScript
     */
    protected function getLocalizedData($hook)
    {
        $list = parent::getLocalizedData($hook);

        //Global Option
        $list['options'] = array_merge($list['options'], [
            'user_online'    => (Option::getValue('useronline') ? 1 : 0),
            'visitors'       => 1,
            'visits'         => 1,
            'geo_ip'         => 1,
            'geo_city'       => 1,
            'overview_page'  => (Menu::isOnPage('overview') ? 1 : 0),
            'gutenberg'      => (Route::isBlockEditorScreen() ? 1 : 0),
            'more_btn'       => (apply_filters('wp_statistics_meta_box_more_button', true) ? 1 : 0),
            'wp_date_format' => DateTime::getDefaultDateFormat(),
            'track_users'    => Option::getValue('visitors_log') ? 1 : 0,
            'wp_timezone'    => (new DateTime())->getTimezone()->getName()
        ]);

        global $post;

        $list['page'] = array(
            'file' => $hook,
            'ID'   => (isset($post) ? $post->ID : 0)
        );

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

        //Global i18n
        $list['i18n'] = $this->getI18nStrings();

        $list['active_post_type']  = PostType::getName(Request::get('pt', 'post'));
        $list['user_date_range']   = DateRange::get();
        $list['initial_post_date'] = Post::getEarliestByDate();

        if (Request::has('post_id')) {
            $list['post_creation_date'] = get_the_date(DateTime::$defaultDateFormat, Request::get('post_id'));
        } else if (is_singular()) {
            $list['post_creation_date'] = get_the_date(DateTime::$defaultDateFormat);
        }

        // Rest-API Meta Box Url
        $list['stats_report_option'] = Option::getValue('time_report') == '0' ? false : true;
        $list['setting_url']         = Menu::getAdminUrl('settings');
        $list['meta_boxes']          = MetaboxHelper::getScreenMetaboxes();
        $list['admin_url']           = admin_url();
        $list['ajax_url']            = admin_url('admin-ajax.php');
        $list['assets_url']          = $this->getPluginUrl() . '/' . $this->getAssetDir() . '/';
        $list['rest_api_nonce']      = wp_create_nonce('wp_rest');
        $list['meta_box_api']        = admin_url('admin-ajax.php?action=wp_statistics_admin_meta_box');

        $list['wp_debug'] = defined('WP_DEBUG') && WP_DEBUG ? true : false;

        return apply_filters('wp_statistics_admin_localized_data', $list);
    }

    /**
     * Get internationalization strings
     *
     * @return array Array of translated strings
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
            'ip'                           => Option::getValue('hash_ips') == true ? __('Daily Visitor Hash', 'wp-statistics') : __('IP Address', 'wp-statistics'),
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
     */
    public function shouldEnqueueChartJs()
    {
        global $pagenow;

        return (Addons::isActive('mini-chart') && Route::isAdminBarShowing()) ||
            Menu::isInPluginPage() ||
            (in_array(Route::getScreenId(), ['dashboard']) && !Option::getValue('disable_dashboard')) ||
            (in_array($pagenow, ['post.php', 'edit.php']) && !Option::getValue('disable_editor')) ||
            (in_array($pagenow, ['post.php', 'edit.php']) && Addons::isActive('data-plus') && Option::getAddonValue('latest_visitors_metabox', 'data_plus', '1') === '1');
    }
}