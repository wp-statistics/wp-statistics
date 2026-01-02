<?php

namespace WP_Statistics;

use WP_Statistics\BackgroundProcess\AjaxBackgroundProcess\AjaxBackgroundProcessManager;
use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\BackgroundProcessFactory;
use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs\CalculateDailySummary;
use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs\CalculateDailySummaryTotal;
use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs\CalculatePostWordsCount;
use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs\GeolocationDatabaseDownloadProcess;
use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs\IncompleteGeoIpUpdater;
use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs\SourceChannelUpdater;
use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs\TableOperationProcess;
use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs\UpdateResourceCacheFields;
use WP_Statistics\Service\Admin\AnonymizedUsageData\AnonymizedUsageDataManager;
use WP_Statistics\Service\Admin\AuthorAnalytics\AuthorAnalyticsManager;
use WP_Statistics\Service\Admin\CategoryAnalytics\CategoryAnalyticsManager;
use WP_Statistics\Service\Admin\ContentAnalytics\ContentAnalyticsManager;
use WP_Statistics\Service\Admin\DashboardBootstrap\DashboardManager;
use WP_Statistics\Service\Admin\Devices\DevicesManager;
use WP_Statistics\Service\Admin\Exclusions\ExclusionsManager;
use WP_Statistics\Service\Admin\FilterHandler\FilterManager;
use WP_Statistics\Service\Admin\Geographic\GeographicManager;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseManagementManager;
use WP_Statistics\Service\Admin\Metabox\MetaboxManager;
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
use WP_Statistics\Service\Database\Managers\MigrationHandler;
use WP_Statistics\Service\HooksManager;
use WP_Statistics\Service\Resources\Core\ResourceSynchronizer;
use WP_Statistics\Service\Integrations\IntegrationsManager;
use WP_Statistics\Service\Tracking\TrackerControllerFactory;
use WP_Statistics\Service\Admin\AdminBar;
use WP_Statistics\Service\CronEventManager;
use WP_Statistics\Service\CustomEvent\CustomEventManager;
use WP_Statistics\Service\Admin\AdminManager;
use WP_Statistics\Service\Admin\Settings\SettingsManager;

defined('ABSPATH') || exit;

/**
 * Bootstrap class for WP Statistics.
 *
 * This class handles initialization and decides whether to load
 * v14 (legacy) or v15 (new) architecture based on migration status.
 *
 * @since 15.0.0
 */
class Bootstrap
{
    /**
     * Background process instances.
     *
     * @var array
     */
    private static $backgroundProcess = [];

    /**
     * Whether v15 mode is active.
     *
     * @var bool
     */
    private static $isV15 = false;

    /**
     * Main entry point - decides v14 or v15 loading.
     *
     * @return void
     */
    public static function init()
    {
        // Load Option class first to check migration status
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-option.php';

        /**
         * Check if migration is complete to determine which architecture to load.
         * TODO: Remove '|| true' when v15 is stable and ready for production.
         *
         * @since 15.0.0
         */
        $migrationComplete = \WP_STATISTICS\Option::getOptionGroup('db', 'migrated', false);

        if ($migrationComplete || true) { // TODO: Remove '|| true' when v15 is stable
            self::$isV15 = true;
            self::initV15();
        } else {
            self::$isV15 = false;
            self::initV14();
        }
    }

    /**
     * Check if v15 mode is active.
     *
     * @return bool
     */
    public static function isV15()
    {
        return self::$isV15;
    }

    /**
     * Initialize v15 architecture.
     *
     * @return void
     */
    private static function initV15()
    {
        // Load core utilities from legacy (still needed in v15)
        self::loadCoreUtilities();

        // Create upload directory
        self::createUploadDirectory();

        // Initialize services
        self::initServices();

        // Initialize background processes
        self::initBackgroundProcesses();

        // Initialize admin-specific services
        if (is_admin()) {
            self::initAdmin();
        }

        // Initialize frontend-specific services
        if (!is_admin()) {
            self::initFrontend();
        }

        // Initialize WP-CLI if available
        if (defined('WP_CLI') && WP_CLI) {
            self::initCli();
        }

        // Load template functions
        self::loadTemplateFunctions();
    }

    /**
     * Initialize v14 legacy architecture.
     *
     * This loads the old includes() method content for users who haven't migrated.
     *
     * @return void
     */
    private static function initV14()
    {
        // Load all legacy includes
        self::loadV14Includes();

        // Initialize background processes
        self::initBackgroundProcesses();
    }

    /**
     * Load v14 legacy includes.
     *
     * @return void
     */
    private static function loadV14Includes()
    {
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-helper.php';

        // Create upload directory
        self::createUploadDirectory();

        require_once WP_STATISTICS_DIR . 'includes/libraries/wp-background-processing/wp-async-request.php';
        require_once WP_STATISTICS_DIR . 'includes/libraries/wp-background-processing/wp-background-process.php';

        // Utility classes
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-db.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-timezone.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-user.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-mail.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-menus.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-meta-box.php';
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
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-referred.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-search-engine.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-exclusion.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-privacy-exporter.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-privacy-erasers.php';

        // Ajax area
        require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-template.php';

        // Initialize v15 services that are also used in v14
        new Service\Admin\Referrals\ReferralsManager();
        new Service\Admin\AnonymizedUsageData\AnonymizedUsageDataManager();
        new Service\Admin\Notification\NotificationManager();
        new Service\Admin\MarketingCampaign\MarketingCampaignManager();
        Service\Tracking\TrackerControllerFactory::createController();

        // Admin bar
        new Service\Admin\AdminBar();

        // Admin classes
        if (is_admin()) {
            new Service\Admin\DashboardBootstrap\DashboardManager();
            new Service\Admin\AdminManager();
            new Service\Admin\ContentAnalytics\ContentAnalyticsManager();

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

            new Service\Admin\AuthorAnalytics\AuthorAnalyticsManager();
            new Service\Admin\PrivacyAudit\PrivacyAuditManager();
            new Service\Admin\HelpCenter\HelpCenterManager();
            new Service\Admin\Geographic\GeographicManager();
            new Service\Admin\Devices\DevicesManager();
            new Service\Admin\CategoryAnalytics\CategoryAnalyticsManager();
            new Service\Admin\PageInsights\PageInsightsManager();
            new Service\Admin\VisitorInsights\VisitorInsightsManager();
            new Service\Integrations\IntegrationsManager();
            new Service\Admin\LicenseManagement\LicenseManagementManager();
            new Service\Admin\TrackerDebugger\TrackerDebuggerManager();
            new Service\Admin\Overview\OverviewManager();
            new Service\Admin\Metabox\MetaboxManager();
            new Service\Admin\Exclusions\ExclusionsManager();
            new Service\Admin\FilterHandler\FilterManager();
            new Service\Resources\Core\ResourceSynchronizer();
            new BackgroundProcess\AjaxBackgroundProcess\AjaxBackgroundProcessManager();
        }

        new Service\HooksManager();
        new Service\CustomEvent\CustomEventManager();
        new Service\CronEventManager();

        // WordPress ShortCode and Widget
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-shortcode.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-widget.php';

        // Rest-Api
        require_once WP_STATISTICS_DIR . 'includes/api/v2/class-wp-statistics-api-hit.php';
        require_once WP_STATISTICS_DIR . 'includes/api/v2/class-wp-statistics-api-meta-box.php';
        require_once WP_STATISTICS_DIR . 'includes/api/v2/class-wp-statistics-api-online.php';

        // WordPress Cron
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-schedule.php';

        // Front Class
        if (!is_admin()) {
            require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-frontend.php';
        }

        // WP-CLI Class
        if (defined('WP_CLI') && WP_CLI) {
            require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-cli.php';
        }

        // Template functions
        include WP_STATISTICS_DIR . 'includes/template-functions.php';

        // Include functions
        require_once WP_STATISTICS_DIR . 'functions.php';

        // Initialize posts manager on init
        add_action('init', function () {
            new Service\Admin\Posts\PostsManager();
        });
    }

    /**
     * Load core utility classes from legacy /includes/ directory.
     *
     * These are utility classes with no UI that are still needed in v15.
     * TODO: Eventually migrate these to /src/ with proper namespacing.
     *
     * @return void
     */
    private static function loadCoreUtilities()
    {
        // Background processing libraries
        require_once WP_STATISTICS_DIR . 'includes/libraries/wp-background-processing/wp-async-request.php';
        require_once WP_STATISTICS_DIR . 'includes/libraries/wp-background-processing/wp-background-process.php';

        // Core utility classes (no UI, widely used)
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-helper.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-db.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-option.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-timezone.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-user.php';

        // Data utility classes
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-country.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-ip.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-geoip.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-user-agent.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-user-online.php';

        // Analytics utilities
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-pages.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-visitor.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-historical.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-referred.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-search-engine.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-exclusion.php';

        // Privacy
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-privacy-exporter.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-privacy-erasers.php';

        // Mail utility
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-mail.php';

        // Menus (still needed by legacy settings pages)
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-menus.php';

        // Meta box (still needed by some legacy code)
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-meta-box.php';

        // WordPress shortcode and widget
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-shortcode.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-widget.php';

        // Data purging
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-purge.php';

        // REST API base class (required before API endpoints)
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-rest-api.php';

        // REST API endpoints (v2)
        require_once WP_STATISTICS_DIR . 'includes/api/v2/class-wp-statistics-api-hit.php';
        require_once WP_STATISTICS_DIR . 'includes/api/v2/class-wp-statistics-api-meta-box.php';
        require_once WP_STATISTICS_DIR . 'includes/api/v2/class-wp-statistics-api-online.php';

        // Legacy schedule (TODO: Replace with v15 EmailReportScheduler)
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-schedule.php';

        // Admin template helper (needed by legacy pages, loaded outside is_admin check like original)
        require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-template.php';

        // Global functions
        require_once WP_STATISTICS_DIR . 'functions.php';
    }

    /**
     * Initialize v15 services.
     *
     * @return void
     */
    private static function initServices()
    {
        // Initialize posts manager on 'init' hook
        add_action('init', function () {
            new PostsManager();
        });

        // Core services (always loaded)
        new ReferralsManager();
        new AnonymizedUsageDataManager();
        new NotificationManager();
        new MarketingCampaignManager();

        // Tracking
        TrackerControllerFactory::createController();

        // Admin bar (works on both admin and frontend)
        new AdminBar();

        // Hooks and events
        new HooksManager();
        new CustomEventManager();
        new CronEventManager();
    }

    /**
     * Initialize admin-specific services.
     *
     * @return void
     */
    private static function initAdmin()
    {
        // Install/upgrade handler
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-install.php';

        // Admin AJAX handlers (legacy)
        require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-ajax.php';

        // Dashboard and network
        require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-dashboard.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-network.php';

        // Export and assets
        require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-export.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-assets.php';

        // User and privacy
        require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-user.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-privacy.php';

        // TinyMCE integration
        require_once WP_STATISTICS_DIR . 'includes/admin/TinyMCE/class-wp-statistics-tinymce.php';

        // Legacy admin pages (TODO: Replace with v15 React settings)
        require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-settings.php';
        require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-optimization.php';

        // v15 Admin Managers
        new DashboardManager();
        new AdminManager();
        new ContentAnalyticsManager();
        new AuthorAnalyticsManager();
        new PrivacyAuditManager();
        new HelpCenterManager();
        new GeographicManager();
        new DevicesManager();
        new CategoryAnalyticsManager();
        new PageInsightsManager();
        new VisitorInsightsManager();
        new IntegrationsManager();
        new LicenseManagementManager();
        new TrackerDebuggerManager();
        new OverviewManager();
        new MetaboxManager();
        new ExclusionsManager();
        new FilterManager();
        new ResourceSynchronizer();
        new AjaxBackgroundProcessManager();

        // v15 Settings Manager (React-based settings page)
        new SettingsManager();
    }

    /**
     * Initialize frontend-specific services.
     *
     * @return void
     */
    private static function initFrontend()
    {
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-frontend.php';
    }

    /**
     * Initialize WP-CLI commands.
     *
     * @return void
     */
    private static function initCli()
    {
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-cli.php';
    }

    /**
     * Load template functions.
     *
     * @return void
     */
    private static function loadTemplateFunctions()
    {
        include WP_STATISTICS_DIR . 'includes/template-functions.php';
    }

    /**
     * Initialize background processes.
     *
     * @return void
     */
    private static function initBackgroundProcesses()
    {
        self::registerBackgroundProcess(CalculatePostWordsCount::class, 'calculate_post_words_count');
        self::registerBackgroundProcess(IncompleteGeoIpUpdater::class, 'update_unknown_visitor_geoip');
        self::registerBackgroundProcess(GeolocationDatabaseDownloadProcess::class, 'geolocation_database_download');
        self::registerBackgroundProcess(SourceChannelUpdater::class, 'update_visitors_source_channel');
        self::registerBackgroundProcess(TableOperationProcess::class, 'table_operations_process');
        self::registerBackgroundProcess(CalculateDailySummary::class, 'calculate_daily_summary');
        self::registerBackgroundProcess(CalculateDailySummaryTotal::class, 'calculate_daily_summary_total');
        self::registerBackgroundProcess(UpdateResourceCacheFields::class, 'update_resouce_cache_fields');
    }

    /**
     * Register a background process.
     *
     * @param string $className  The background process class name.
     * @param string $processKey The key to store the process.
     * @return void
     */
    private static function registerBackgroundProcess($className, $processKey)
    {
        if (class_exists($className)) {
            self::$backgroundProcess[$processKey] = new $className();
        }
    }

    /**
     * Get a registered background process.
     *
     * @param string $processKey The process key.
     * @return \WP_Background_Process|null
     */
    public static function getBackgroundProcess($processKey)
    {
        return self::$backgroundProcess[$processKey] ?? null;
    }

    /**
     * Create the plugin upload directory.
     *
     * @return void
     */
    private static function createUploadDirectory()
    {
        $upload_dir      = wp_upload_dir();
        $upload_dir_name = $upload_dir['basedir'] . '/' . WP_STATISTICS_UPLOADS_DIR;

        $result = wp_mkdir_p($upload_dir_name);

        // Check if the directory creation failed.
        if (!$result) {
            $errorMessage = sprintf(
                /* translators: %s: Upload directory path */
                __('Unable to create the required upload directory at <code>%s</code>. Please check that the web server has write permissions for the parent directory.', 'wp-statistics'),
                esc_html($upload_dir_name)
            );
            \WP_Statistics\Service\Admin\NoticeHandler\Notice::addNotice($errorMessage, 'create_upload_directory', 'warning', false);
        }

        // Create .htaccess to avoid public access.
        // phpcs:disable
        if (apply_filters('wp_statistics_enable_htaccess_protection', true) && is_dir($upload_dir_name) && is_writable($upload_dir_name)) {
            $htaccess_file = path_join($upload_dir_name, '.htaccess');

            if (!file_exists($htaccess_file) && $handle = @fopen($htaccess_file, 'w')) {
                fwrite($handle, "Deny from all\n");
                fclose($handle);
            }
        }
        // phpcs:enable
    }
}