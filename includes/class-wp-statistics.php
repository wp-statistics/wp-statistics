<?php

use WP_Statistics\Async\CalculatePostWordsCount;
use WP_Statistics\Async\GeoIPDatabaseDownloadProcess;
use WP_Statistics\Async\IncompleteGeoIpUpdater;
use WP_Statistics\Service\Admin\AuthorAnalytics\AuthorAnalyticsManager;
use WP_Statistics\Service\Admin\ContentAnalytics\ContentAnalyticsManager;
use WP_Statistics\Service\Admin\Geographic\GeographicManager;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\Posts\PostsManager;
use WP_Statistics\Service\Admin\PrivacyAudit\PrivacyAuditManager;
use WP_Statistics\Service\Admin\CategoryAnalytics\CategoryAnalyticsManager;
use WP_Statistics\Service\Analytics\AnalyticsManager;
use WP_Statistics\Service\Integrations\IntegrationsManager;
use WP_Statistics\Service\Integrations\WpConsentApi;
use WP_Statistics\Service\Admin\Devices\DevicesManager;
use WP_Statistics\Service\Admin\VisitorInsights\VisitorInsightsManager;
use WP_Statistics\Service\Admin\PageInsights\PageInsightsManager;

defined('ABSPATH') || exit;

/**
 * Main bootstrap class for WP Statistics
 *
 * @package WP Statistics
 */
final class WP_Statistics
{
    /**
     * The single instance of the class.
     *
     * @var WP_Statistics
     */
    protected static $_instance = null;

    /**
     * @var $backgroundProcess
     */
    private $backgroundProcess;

    /**
     * Main WP Statistics Instance.
     * Ensures only one instance of WP Statistics is loaded or can be loaded.
     *
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * WP_Statistics constructor.
     */
    public function __construct()
    {
        /**
         * Plugin Loaded Action
         */
        add_action('plugins_loaded', array($this, 'plugin_setup'), 10);

        /**
         * Install And Upgrade plugin
         */
        register_activation_hook(WP_STATISTICS_MAIN_FILE, array('WP_Statistics', 'install'));

        /**
         * wp-statistics loaded
         */
        do_action('wp_statistics_loaded');
    }

    /**
     * Constructors plugin Setup
     *
     * @throws Exception
     */
    public function plugin_setup()
    {
        /**
         * Load text domain
         */
        add_action('init', array($this, 'load_textdomain'));

        try {

            /**
             * Include require file
             */
            $this->includes();

            /**
             * Setup background process
             */
            $this->initializeBackgroundProcess();

        } catch (Exception $e) {
            self::log($e->getMessage());
        }
    }

    /**
     * Includes plugin files
     */
    public function includes()
    {
        // third-party Libraries
        require_once WP_STATISTICS_DIR . 'vendor/autoload.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-helper.php';

        // Create the plugin upload directory in advance.
        $this->create_upload_directory();

        require_once WP_STATISTICS_DIR . 'includes/libraries/wp-background-processing/wp-async-request.php';
        require_once WP_STATISTICS_DIR . 'includes/libraries/wp-background-processing/wp-background-process.php';

        // Utility classes.
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-db.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-timezone.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-option.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-user.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-mail.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-menus.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-meta-box.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-admin-bar.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-rest-api.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-purge.php';

        // Hits Class
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-country.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-user-online.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-user-agent.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-ip.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-geoip.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-pages.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-visitor.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-historical.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-visit.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-referred.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-search-engine.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-exclusion.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-hits.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-privacy-exporter.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-privacy-erasers.php';

        // Ajax area
        require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-template.php';

        $postsManager = new PostsManager();
        $userOnline   = new \WP_STATISTICS\UserOnline();

        // Admin classes
        if (is_admin()) {

            $adminManager     = new \WP_Statistics\Service\Admin\AdminManager();
            $contentAnalytics = new ContentAnalyticsManager();

            require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-install.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-ajax.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-dashboard.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-export.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-network.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-assets.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-user.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-privacy.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/TinyMCE/class-wp-statistics-tinymce.php';

            // Admin Pages List
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-settings.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-optimization.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-plugins.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-overview.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-refer.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-searches.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-exclusions.php';

            $analytics           = new AnalyticsManager();
            $authorAnalytics     = new AuthorAnalyticsManager();
            $privacyAudit        = new PrivacyAuditManager();
            $geographic          = new GeographicManager();
            $devices             = new DevicesManager();
            $categoryAnalytics   = new CategoryAnalyticsManager();
            $pageInsights        = new PageInsightsManager();
            $visitorInsights     = new VisitorInsightsManager();
            $integrationsManager = new IntegrationsManager();
        }

        // WordPress ShortCode and Widget
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-shortcode.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-widget.php';

        // Meta Box List
        \WP_STATISTICS\Meta_Box::includes();

        // Rest-Api
        require_once WP_STATISTICS_DIR . 'includes/api/v2/class-wp-statistics-api-hit.php';
        require_once WP_STATISTICS_DIR . 'includes/api/v2/class-wp-statistics-api-meta-box.php';
        require_once WP_STATISTICS_DIR . 'includes/api/v2/class-wp-statistics-api-online.php';

        // WordPress Cron
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-schedule.php';

        // Front Class.
        if (!is_admin()) {
            require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-frontend.php';
        }

        // WP-CLI Class.
        if (defined('WP_CLI') && WP_CLI) {
            require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-cli.php';
        }

        // Template functions.
        include WP_STATISTICS_DIR . 'includes/template-functions.php';
    }

    /**
     * Set up background processes.
     */
    private function initializeBackgroundProcess()
    {
        $this->registerBackgroundProcess(CalculatePostWordsCount::class, 'calculate_post_words_count');
        $this->registerBackgroundProcess(IncompleteGeoIpUpdater::class, 'update_unknown_visitor_geoip');
        $this->registerBackgroundProcess(GeoIPDatabaseDownloadProcess::class, 'geoip_database_download');
    }

    /**
     * Initialize a background process if the class exists.
     *
     * @param string $className The name of the background process class.
     * @param string $processKey The key to store the background process in the array.
     */
    private function registerBackgroundProcess($className, $processKey)
    {
        if (class_exists($className)) {
            $this->backgroundProcess[$processKey] = new $className();
        }
    }

    /**
     * Get the registered background processes.
     *
     * @return WP_Background_Process
     */
    public function getBackgroundProcess($processKey)
    {
        return $this->backgroundProcess[$processKey];
    }

    private function create_upload_directory()
    {
        $upload_dir      = wp_upload_dir();
        $upload_dir_name = $upload_dir['basedir'] . '/' . WP_STATISTICS_UPLOADS_DIR;

        $result = wp_mkdir_p($upload_dir_name);

        // Check if the directory creation failed.
        if (!$result) {
            $errorMessage = sprintf(__('Unable to create the required upload directory at <code>%s</code>. Please check that the web server has write permissions for the parent directory. Alternatively, you can manually create the directory yourself. Please keep in mind that the GeoIP database may not work correctly if the directory structure is not properly set up.', 'wp-statistics'), esc_html($upload_dir_name));
            Notice::addNotice($errorMessage, 'create_upload_directory', 'warning', false);
        }

        /**
         * Create .htaccess to avoid public access.
         */
        // phpcs:disable
        if (is_dir($upload_dir_name) and is_writable($upload_dir_name)) {
            $htaccess_file = path_join($upload_dir_name, '.htaccess');

            if (!file_exists($htaccess_file)
                and $handle = @fopen($htaccess_file, 'w')) {
                fwrite($handle, "Deny from all\n");
                fclose($handle);
            }
        }
        // phpcs:enable

    }

    /**
     * Loads the load plugin text domain code.
     */
    public function load_textdomain()
    {
        // Compatibility with WordPress < 5.0
        if (function_exists('determine_locale')) {
            $locale = apply_filters('plugin_locale', determine_locale(), 'wp-statistics');

            unload_textdomain('wp-statistics');
            load_textdomain('wp-statistics', WP_LANG_DIR . '/wp-statistics-' . $locale . '.mo');
        }

        load_plugin_textdomain('wp-statistics', false, basename(WP_STATISTICS_DIR) . '/languages');
    }

    /**
     * The main logging function
     *
     * @param string $message The message to be logged.
     * @param string $level The log level (e.g., 'info', 'warning', 'error'). Default is 'info'.
     * @uses error_log
     */
    public static function log($message, $level = 'info')
    {
        if (is_array($message)) {
            $message = wp_json_encode($message);
        }

        $log_level = strtoupper($level);
        error_log(sprintf('[WP STATISTICS] [%s]: %s', $log_level, $message));
    }

    /**
     * Create tables on plugin activation
     *
     * @param object $network_wide
     */
    public static function install($network_wide)
    {
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-db.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-install.php';
        $installer = new \WP_STATISTICS\Install();
        $installer->install($network_wide);
    }

    /**
     * Manage task on plugin deactivation
     *
     * @return void
     */
    public static function uninstall()
    {
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-db.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-uninstall.php';
        new \WP_STATISTICS\Uninstall();
    }
}