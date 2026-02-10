<?php

namespace WP_Statistics\Service\Installation;

use WP_Statistics\Service\Admin\AccessControl\AccessLevel;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Options\OptionManager;
use WP_Statistics\Components\Event;
use WP_Statistics\Service\Integrations\IntegrationHelper;
use WP_Statistics\Utils\Query;

/**
 * Installation Manager for WP Statistics.
 *
 * Handles plugin activation, deactivation, and upgrades.
 *
 * @since 15.0.0
 */
class InstallManager
{
    /**
     * Initialize installation hooks.
     *
     * @return void
     */
    public static function init(): void
    {
        // Multi-site blog creation/deletion
        add_action('wpmu_new_blog', [__CLASS__, 'onBlogCreate'], 10, 1);
        add_filter('wpmu_drop_tables', [__CLASS__, 'onBlogDelete']);

        // Plugin meta links
        add_filter('plugin_row_meta', [__CLASS__, 'addMetaLinks'], 10, 2);

        // Plugin upgrades
        add_action('init', [__CLASS__, 'runUpgrades']);
    }

    /**
     * Plugin activation handler.
     *
     * @param bool $networkWide Whether activating network-wide.
     * @return void
     */
    public static function activate(bool $networkWide = false): void
    {
        global $wpdb;

        if (is_multisite() && $networkWide) {
            $blogIds = $wpdb->get_col("SELECT `blog_id` FROM $wpdb->blogs");
            foreach ($blogIds as $blogId) {
                switch_to_blog($blogId);
                self::installSingleSite();
                restore_current_blog();
            }
        } else {
            self::installSingleSite();
        }

        // Mark background processes as initiated for fresh installs
        self::markBackgroundProcessAsInitiated();

        // Create default options
        self::createDefaultOptions();

        // Store version
        update_option('wp_statistics_plugin_version', WP_STATISTICS_VERSION);
    }

    /**
     * Install for a single site.
     *
     * @return void
     */
    private static function installSingleSite(): void
    {
        self::checkIsFresh();
        TableHandler::createAllTables();
    }

    /**
     * Check if this is a fresh installation.
     *
     * @return void
     */
    private static function checkIsFresh(): void
    {
        $version = get_option('wp_statistics_plugin_version');

        update_option('wp_statistics_is_fresh', empty($version));

        $installationTime = get_option('wp_statistics_installation_time');
        if (empty($installationTime)) {
            update_option('wp_statistics_installation_time', time());
        }
    }

    /**
     * Check if the plugin is freshly installed.
     *
     * @return bool
     */
    public static function isFresh(): bool
    {
        return (bool) get_option('wp_statistics_is_fresh', false);
    }

    /**
     * Mark background processes as initiated for fresh installs.
     *
     * @return void
     */
    private static function markBackgroundProcessAsInitiated(): void
    {
        OptionManager::deleteGroup('jobs', 'data_migration_process_started');

        if (!self::isFresh()) {
            return;
        }

        $processes = [
            'update_source_channel_process_initiated',
            'update_geoip_process_initiated',
            'schema_migration_process_started',
            'table_operations_process_initiated',
            'update_resouce_cache_fields_initiated',
        ];

        foreach ($processes as $process) {
            OptionManager::setGroup('jobs', $process, true);
        }
    }

    /**
     * Create default plugin options.
     *
     * @return void
     */
    public static function createDefaultOptions(): void
    {
        $existingOptions = get_option(OptionManager::OPTION_NAME);

        if ($existingOptions === false || !is_array($existingOptions)) {
            update_option(OptionManager::OPTION_NAME, OptionManager::getDefaults());
        }
    }

    /**
     * Plugin deactivation handler.
     *
     * @return void
     */
    public static function deactivate(): void
    {
        Uninstaller::deactivate();
    }

    /**
     * Handle new blog creation in multisite.
     *
     * @param int $blogId The new blog ID.
     * @return void
     */
    public static function onBlogCreate(int $blogId): void
    {
        if (is_plugin_active_for_network(plugin_basename(WP_STATISTICS_MAIN_FILE))) {
            $options = get_option(OptionManager::OPTION_NAME);
            switch_to_blog($blogId);
            TableHandler::createAllTables();
            update_option(OptionManager::OPTION_NAME, $options);
            restore_current_blog();
        }
    }

    /**
     * Handle blog deletion in multisite.
     *
     * @param array $tables Tables to drop.
     * @return array
     */
    public static function onBlogDelete(array $tables): array
    {
        return array_merge($tables, self::getAllTableNames());
    }

    /**
     * Get all plugin table WP_Statistics_names.
     *
     * @return array
     */
    private static function getAllTableNames(): array
    {
        global $wpdb;

        return [
            $wpdb->prefix . 'statistics_visitor',
            $wpdb->prefix . 'statistics_visitor_relationships',
            $wpdb->prefix . 'statistics_pages',
            $wpdb->prefix . 'statistics_visit',
            $wpdb->prefix . 'statistics_historical',
            $wpdb->prefix . 'statistics_exclusions',
            $wpdb->prefix . 'statistics_useronline',
            $wpdb->prefix . 'statistics_events',
        ];
    }

    /**
     * Add plugin meta links.
     *
     * @param array  $links Existing links.
     * @param string $file  Plugin file.
     * @return array
     */
    public static function addMetaLinks(array $links, string $file): array
    {
        if ($file === plugin_basename(WP_STATISTICS_MAIN_FILE)) {
            $links[] = sprintf(
                '<a href="%s" target="_blank" title="%s">%s</a>',
                'https://wordpress.org/plugins/wp-statistics/',
                __('Click here to visit the plugin on WordPress.org', 'wp-statistics'),
                __('Visit WordPress.org page', 'wp-statistics')
            );

            $links[] = sprintf(
                '<a href="%s" target="_blank" title="%s">%s</a>',
                'https://wordpress.org/support/plugin/wp-statistics/reviews/?rate=5#new-post',
                __('Click here to rate and review this plugin on WordPress.org', 'wp-statistics'),
                __('Rate this plugin', 'wp-statistics')
            );
        }

        return $links;
    }

    /**
     * Run plugin upgrades.
     *
     * @return void
     */
    public static function runUpgrades(): void
    {
        $installedVersion = get_option('wp_statistics_plugin_version');
        $latestVersion = WP_STATISTICS_VERSION;

        if ($installedVersion === $latestVersion) {
            return;
        }

        self::checkIsFresh();

        // Create/update tables
        TableHandler::createAllTables();

        // Run version-specific migrations
        self::runMigrations($installedVersion, $latestVersion);

        // Update version
        update_option('wp_statistics_plugin_version', $latestVersion);
    }

    /**
     * Run database migrations between versions.
     *
     * @param string|false $fromVersion Previous version.
     * @param string       $toVersion   New version.
     * @return void
     */
    private static function runMigrations($fromVersion, string $toVersion): void
    {
        global $wpdb;

        // Skip if no previous version (fresh install)
        if (empty($fromVersion)) {
            return;
        }

        // Update GeoIP schedule from daily to monthly (14.11)
        if (OptionManager::get('schedule_geoip') && version_compare($fromVersion, '14.11', '<')) {
            Event::reschedule('wp_statistics_geoip_hook', 'monthly');
        }

        // Remove deprecated cron hooks (14.15)
        if (version_compare($toVersion, '14.15', '>=')) {
            Event::unschedule('wp_statistics_marketing_campaign_hook');
            Event::unschedule('wp_statistics_notification_hook');
            Event::unschedule('wp_statistics_add_visit_hook');
        }

        // Clear unused transients (14.15.1)
        if (version_compare($toVersion, '14.15.1', '>=')) {
            Query::delete('options')
                ->where('option_name', 'LIKE', '%wp_statistics_cache%')
                ->execute();
        }

        // Update consent integration for backward compatibility
        self::migrateConsentSettings();

        // Update privacy audit option (14.7)
        if (OptionManager::get('privacy_audit') === false && version_compare($toVersion, '14.7', '>=')) {
            OptionManager::set('privacy_audit', true);
        }

        // Update notification options (14.12)
        if (version_compare($toVersion, '14.12', '>')) {
            if (OptionManager::get('share_anonymous_data') === false) {
                OptionManager::set('share_anonymous_data', false);
            }
            if (OptionManager::get('display_notifications') === false) {
                OptionManager::set('display_notifications', true);
            }
            if (OptionManager::get('show_privacy_issues_in_report') === false) {
                OptionManager::set('show_privacy_issues_in_report', false);
            }
        }

        // Migrate legacy access settings to tier-based access levels (15.1)
        self::migrateAccessLevels();

        // Clear unused scheduled hooks
        wp_clear_scheduled_hook('wp_statistics_dbmaint_visitor_hook');
        wp_clear_scheduled_hook('wp_statistics_referrals_db_hook');
    }

    /**
     * Migrate legacy read_capability/manage_capability to the new access_levels format.
     *
     * Only runs if access_levels is not already set.
     *
     * @return void
     */
    private static function migrateAccessLevels(): void
    {
        $existing = OptionManager::get('access_levels');
        if (!empty($existing) && is_array($existing)) {
            return;
        }

        AccessLevel::migrateFromLegacy();
    }

    /**
     * Migrate consent settings for backward compatibility.
     *
     * @return void
     */
    private static function migrateConsentSettings(): void
    {
        $integration = OptionManager::get('consent_integration');
        $consentLevel = OptionManager::get('consent_level_integration', 'disabled');

        if (class_exists(IntegrationHelper::class)) {
            $isWpConsentApiActive = IntegrationHelper::getIntegration('wp_consent_api')->isActive();

            if ($isWpConsentApiActive && empty($integration) && $consentLevel !== 'disabled') {
                OptionManager::set('consent_integration', 'wp_consent_api');
            }
        }
    }
}
