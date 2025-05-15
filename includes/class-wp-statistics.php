<?php

use WP_Statistics\BackgroundProcess\AjaxBackgroundProcess\AjaxBackgroundProcessManager;
use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs\CalculatePostWordsCount;
use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs\DataMigrationProcess;
use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs\GeolocationDatabaseDownloadProcess;
use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs\IncompleteGeoIpUpdater;
use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs\SchemaMigrationProcess;
use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs\SourceChannelUpdater;
use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs\TableOperationProcess;
use WP_Statistics\Service\Admin\AnonymizedUsageData\AnonymizedUsageDataManager;
use WP_Statistics\Service\Admin\AuthorAnalytics\AuthorAnalyticsManager;
use WP_Statistics\Service\Admin\CategoryAnalytics\CategoryAnalyticsManager;
use WP_Statistics\Service\Admin\ContentAnalytics\ContentAnalyticsManager;
use WP_Statistics\Service\Admin\Devices\DevicesManager;
use WP_Statistics\Service\Admin\Exclusions\ExclusionsManager;
use WP_Statistics\Service\Admin\FilterHandler\FilterManager;
use WP_Statistics\Service\Admin\Geographic\GeographicManager;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseManagementManager;
use WP_Statistics\Service\Admin\Metabox\MetaboxManager;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\Notification\NotificationManager;
use WP_Statistics\Service\Admin\MarketingCampaign\MarketingCampaignManager;
use WP_Statistics\Service\Admin\Overview\OverviewManager;
use WP_Statistics\Service\Admin\PageInsights\PageInsightsManager;
use WP_Statistics\Service\Admin\Posts\PostsManager;
use WP_Statistics\Service\Admin\PrivacyAudit\PrivacyAuditManager;
use WP_Statistics\Service\Admin\HelpCenter\HelpCenterManager;
use WP_Statistics\Service\Admin\Referrals\ReferralsManager;
use WP_Statistics\Service\Admin\TrackerDebugger\TrackerDebuggerManager;
use WP_Statistics\Service\Admin\VisitorInsights\VisitorInsightsManager;
use WP_Statistics\Service\Analytics\AnalyticsManager;
use WP_Statistics\Service\Database\Managers\MigrationHandler;
use WP_Statistics\Service\HooksManager;
use WP_Statistics\Service\CronEventManager;
use WP_Statistics\Service\Integrations\IntegrationsManager;

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
         * Remove plugin data
         */
        register_uninstall_hook(WP_STATISTICS_MAIN_FILE, ['WP_Statistics', 'uninstall']);

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
             * Initialize classes during WordPress initialization.
             */
            add_action('init', function () {
                $postsManager = new PostsManager();
            });

            /**
             * Setup background process.
             */
            $this->initializeBackgroundProcess();
            MigrationHandler::init();

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

        $referrals                  = new ReferralsManager();
        $userOnline                 = new \WP_STATISTICS\UserOnline();
        $anonymizedUsageDataManager = new AnonymizedUsageDataManager();
        $notificationManager        = new NotificationManager();
        $MarketingCampaignManager   = new MarketingCampaignManager();

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

            $analytics           = new AnalyticsManager();
            $authorAnalytics     = new AuthorAnalyticsManager();
            $privacyAudit        = new PrivacyAuditManager();
            $helpCenter          = new HelpCenterManager();
            $geographic          = new GeographicManager();
            $devices             = new DevicesManager();
            $categoryAnalytics   = new CategoryAnalyticsManager();
            $pageInsights        = new PageInsightsManager();
            $visitorInsights     = new VisitorInsightsManager();
            $integrationsManager = new IntegrationsManager();
            $licenseManager      = new LicenseManagementManager();
            $trackerDebugger     = new TrackerDebuggerManager();
            $overviewManager     = new OverviewManager();
            $metaboxManager      = new MetaboxManager();
            $exclusionsManager   = new ExclusionsManager();
            new FilterManager();
            new AjaxBackgroundProcessManager();
        }

        $hooksManager       = new HooksManager();
        $cronEventManager   = new CronEventManager();

        // WordPress ShortCode and Widget
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-shortcode.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-widget.php';

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
        $this->registerBackgroundProcess(GeolocationDatabaseDownloadProcess::class, 'geolocation_database_download');
        $this->registerBackgroundProcess(SourceChannelUpdater::class, 'update_visitors_source_channel');
        $this->registerBackgroundProcess(DataMigrationProcess::class, 'data_migration_process');
        $this->registerBackgroundProcess(SchemaMigrationProcess::class, 'schema_migration_process');
        $this->registerBackgroundProcess(TableOperationProcess::class, 'table_operations_process');
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
        if (apply_filters('wp_statistics_enable_htaccess_protection', true) && is_dir($upload_dir_name) && is_writable($upload_dir_name)) {
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

            unload_textdomain('wp-statistics', true);
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


        // Log when debug is enabled
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf('[WP STATISTICS] [%s]: %s', $log_level, $message));
        }
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
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-option.php';
        require_once WP_STATISTICS_DIR . 'src/Components/AssetNameObfuscator.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-uninstall.php';

        new \WP_STATISTICS\Uninstall();
    }
}