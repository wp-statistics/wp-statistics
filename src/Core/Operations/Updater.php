<?php

namespace WP_Statistics\Core\Operations;

use WP_Statistics\Core\AbstractCore;
use WP_Statistics\Components\AssetNameObfuscator;
use WP_Statistics\Components\Event;
use WP_Statistics\Components\SystemCleaner;
use WP_STATISTICS\DB;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Database\Managers\SchemaMaintainer;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Migrations\Schema\SchemaManager;
use WP_Statistics\Service\Integrations\IntegrationHelper;

/**
 * Handles update-time migrations and cleanup.
 *
 * Runs on init when a version change is detected; ensures tables exist, executes
 * legacy migrations, updates the stored version, and bootstraps the schema manager.
 * Also adjusts schedules, options, and cached data as needed.
 *
 * @package WP_Statistics\Core\Operations
 */
class Updater extends AbstractCore
{
    /**
     * Updater constructor.
     *
     * @return void
     */
    public function __construct($networkWide = false)
    {
        parent::__construct($networkWide);
        add_action('init', [$this, 'execute']);
    }

    /**
     * Execute the core function.
     *
     * @return void
     */
    public function execute()
    {
        if (is_multisite()) {
            $this->initializeDefaultOptions();
        }

        if (!$this->isUpdated()) {
            return;
        }

        $this->checkIsFresh();
        TableHandler::createAllTables();
        $this->legacyMigrations();
        $this->updateVersion();

        SchemaManager::init();
        SchemaMaintainer::repair(true);
    }

    /**
     * Execute the legacy migrations.
     *
     * @return void
     */
    private function legacyMigrations()
    {
        $userOnlineTable = DB::table('useronline');
        $pagesTable      = DB::table('pages');
        $visitorTable    = DB::table('visitor');
        $historicalTable = DB::table('historical');
        $searchTable     = DB::getTableName('search');

        /**
         * Add source channel column to visitors table
         *
         * @version 14.11
         */
        $result = $this->wpdb->query("SHOW COLUMNS FROM {$visitorTable} LIKE 'source_channel'");
        if ($result == 0) {
            $this->wpdb->query("ALTER TABLE {$visitorTable} ADD `source_channel` VARCHAR(50) NULL;");
        }

        /**
         * Add source name column to visitors table
         *
         * @version 14.11
         */
        $result = $this->wpdb->query("SHOW COLUMNS FROM {$visitorTable} LIKE 'source_name'");
        if ($result == 0) {
            $this->wpdb->query("ALTER TABLE {$visitorTable} ADD `source_name` VARCHAR(100) NULL;");
        }

        /**
         * Add visitor id column to user online table
         *
         * @version 14.11
         */
        $result = $this->wpdb->query("SHOW COLUMNS FROM {$userOnlineTable} LIKE 'visitor_id'");
        if ($result == 0) {
            $this->wpdb->query("ALTER TABLE {$userOnlineTable} ADD `visitor_id` bigint(20) NOT NULL;");
        }

        /**
         * Add visitor city
         *
         * @version 14.5.2
         */
        $result = $this->wpdb->query("SHOW COLUMNS FROM {$visitorTable} LIKE 'city'");
        if ($result == 0) {
            $this->wpdb->query("ALTER TABLE {$visitorTable} ADD `city` VARCHAR(100) NULL;");
        }

        /**
         * Add visitor region
         *
         * @version 14.7.0
         */
        $result = $this->wpdb->query("SHOW COLUMNS FROM {$visitorTable} LIKE 'region'");
        if ($result == 0) {
            $this->wpdb->query("ALTER TABLE {$visitorTable} ADD `region` VARCHAR(100) NULL;");
        }

        /**
         * Add visitor continent
         *
         * @version 14.7.0
         */
        $result = $this->wpdb->query("SHOW COLUMNS FROM {$visitorTable} LIKE 'continent'");
        if ($result == 0) {
            $this->wpdb->query("ALTER TABLE {$visitorTable} ADD `continent` VARCHAR(50) NULL;");
        }

        /**
         * Add visitor device type
         *
         * @version 13.2.4
         */
        $result = $this->wpdb->query("SHOW COLUMNS FROM {$visitorTable} LIKE 'device'");
        if ($result == 0) {
            $this->wpdb->query("ALTER TABLE {$visitorTable} ADD `device` VARCHAR(180) NULL AFTER `version`, ADD INDEX `device` (`device`);");
        }

        /**
         * Add visitor device model
         *
         * @version 13.2.4
         */
        $result = $this->wpdb->query("SHOW COLUMNS FROM {$visitorTable} LIKE 'model'");
        if ($result == 0) {
            $this->wpdb->query("ALTER TABLE {$visitorTable} ADD `model` VARCHAR(180) NULL AFTER `device`, ADD INDEX `model` (`model`);");
        }

        /**
         * Set to BigINT Fields (AUTO_INCREMENT)
         *
         * @version 13.0.0
         */
        /*
         * MySQL since version 8.0.19 doesn't honot  display width specification
         * so we have to handle accept BIGINT(20) and BIGINT.
         *
         * see: https://dev.mysql.com/doc/relnotes/mysql/8.0/en/news-8-0-19.html
         * - section Deprecation and Removal Notes
         */
        if (!DB::isColumnType('visitor', 'ID', 'bigint(20)') && !DB::isColumnType('visitor', 'ID', 'bigint')) {
            $this->wpdb->query("ALTER TABLE {$visitorTable} CHANGE `ID` `ID` BIGINT(20) NOT NULL AUTO_INCREMENT;");
        }

        if (!DB::isColumnType('exclusions', 'ID', 'bigint(20)') && !DB::isColumnType('exclusions', 'ID', 'bigint')) {

            $this->wpdb->query("ALTER TABLE `" . DB::table('exclusions') . "` CHANGE `ID` `ID` BIGINT(20) NOT NULL AUTO_INCREMENT;");
        }

        if (!DB::isColumnType('useronline', 'ID', 'bigint(20)') && !DB::isColumnType('useronline', 'ID', 'bigint')) {
            $this->wpdb->query("ALTER TABLE {$userOnlineTable} CHANGE `ID` `ID` BIGINT(20) NOT NULL AUTO_INCREMENT;");
        }

        /**
         * Change Charset All Table To New WordPress Collate
         * Reset Overview Order Meta Box View
         * Added User_id column in wp_statistics_visitor Table
         *
         * @see https://developer.wordpress.org/reference/classes/wpdb/has_cap/
         * @version 13.0.0
         */
        $list_table = DB::table('all');
        foreach ($list_table as $k => $name) {
            $tbl_info = DB::getTableInformation($name);

            if (!empty($tbl_info['Collation']) && !empty($this->wpdb->collate) && $tbl_info['Collation'] != $this->wpdb->collate) {
                $this->wpdb->query(
                    $this->wpdb->prepare("ALTER TABLE `" . $name . "` DEFAULT CHARSET=%s COLLATE %s ROW_FORMAT = COMPACT;", $this->wpdb->charset, $this->wpdb->collate)
                );
            }
        }

        if (version_compare($this->currentVersion, '13.0', '<=')) {
            $this->wpdb->query("DELETE FROM `" . $this->wpdb->usermeta . "` WHERE `meta_key` = 'meta-box-order_toplevel_page_wps_overview_page'");
        }

        $result = $this->wpdb->query("SHOW COLUMNS FROM {$visitorTable} LIKE 'user_id'");
        if ($result == 0) {
            $this->wpdb->query("ALTER TABLE `" . $visitorTable . "` ADD `user_id` BIGINT(48) NOT NULL AFTER `location`");
        }

        if (DB::ExistTable($searchTable)) {
            $this->wpdb->query("DROP TABLE `$searchTable`");
        }

        /**
         * Added new Fields to user_online Table
         *
         * @version 12.6.1
         */
        if (DB::ExistTable($userOnlineTable)) {
            // Add index ip.
            $result = $this->wpdb->query("SHOW INDEX FROM `" . $userOnlineTable . "` WHERE Key_name = 'ip'");
            if (!$result) {
                $this->wpdb->query("ALTER TABLE `" . $userOnlineTable . "` ADD index (ip)");
            }
        }

        /**
         * Historical
         *
         * @version 14.4
         *
         */
        if (DB::ExistTable($historicalTable)) {
            $result = $this->wpdb->query("SHOW INDEX FROM `" . $historicalTable . "` WHERE Key_name = 'page_id'");

            // Remove index
            if ($result) {
                $this->wpdb->query("DROP INDEX `page_id` ON " . $historicalTable);
            }
        }

        /**
         * Added page_id column in statistics_pages
         *
         * @version 12.5.3
         */
        if (DB::ExistTable($pagesTable)) {
            $result = $this->wpdb->query("SHOW COLUMNS FROM `" . $pagesTable . "` LIKE 'page_id'");
            if ($result == 0) {
                $this->wpdb->query("ALTER TABLE `" . $pagesTable . "` ADD `page_id` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`page_id`);");
            }
        }

        /**
         * Removed date_ip from visitor table
         * Drop the 'AString' column from visitors if it exists.
         *
         * @version 6.0
         */
        if (DB::ExistTable($visitorTable)) {
            $result = $this->wpdb->query("SHOW INDEX FROM `" . $visitorTable . "` WHERE Key_name = 'date_ip'");
            if ($result > 1) {
                $this->wpdb->query("DROP INDEX `date_ip` ON " . $visitorTable);
            }

            $result = $this->wpdb->query("SHOW COLUMNS FROM `" . $visitorTable . "` LIKE 'AString'");
            if ($result > 0) {
                $this->wpdb->query("ALTER TABLE `" . $visitorTable . "` DROP `AString`");
            }

            // Add index ip
            $result = $this->wpdb->query("SHOW INDEX FROM `" . $visitorTable . "` WHERE Key_name = 'ip'");
            if (!$result) {
                $this->wpdb->query("ALTER TABLE `" . $visitorTable . "` ADD index (ip)");
            }
        }

        /**
         * Update options
         */
        if (Option::get('privacy_audit') === false && version_compare($this->latestVersion, '14.7', '>=')) {
            Option::update('privacy_audit', true);
        }

        if (Option::get('share_anonymous_data') === false && version_compare($this->latestVersion, '14.12', '>')) {
            Option::update('share_anonymous_data', false);
        }

        if (Option::get('display_notifications') === false && version_compare($this->latestVersion, '14.12', '>')) {
            Option::update('display_notifications', true);
        }

        if (Option::get('show_privacy_issues_in_report') === false && version_compare($this->latestVersion, '14.12', '>')) {
            Option::update('show_privacy_issues_in_report', false);
        }

        /**
         * Update GeoIP schedule from daily to monthly
         */
        if (Option::get('schedule_geoip') && version_compare($this->currentVersion, '14.11', '<')) {
            Event::reschedule('wp_statistics_geoip_hook', 'monthly');
        }

        /**
         * Remove wp_statistics_marketing_campaign_hook, wp_statistics_notification_hook from schedule
         */
        if (version_compare($this->latestVersion, '14.15', '>=')) {
            Event::unschedule('wp_statistics_marketing_campaign_hook');
            Event::unschedule('wp_statistics_notification_hook');
        }

        /**
         * Remove wp_statistics_add_visit_hook from schedule
         */
        if (version_compare($this->latestVersion, '14.15', '>=')) {
            Event::unschedule('wp_statistics_add_visit_hook');
        }

        /**
         * Remove all wp statistics transients
         */
        if (version_compare($this->latestVersion, '14.15.1', '>=')) {
            SystemCleaner::clearAllTransients();
        }

        /**
         * Update consent integration to WP Consent API for backward compatibility
         */
        $integration          = Option::get('consent_integration');
        $consentLevel         = Option::get('consent_level_integration', 'disabled');
        $isWpConsentApiActive = IntegrationHelper::getIntegration('wp_consent_api')->isActive();

        if ($isWpConsentApiActive && empty($integration) && $consentLevel !== 'disabled') {
            Option::update('consent_integration', 'wp_consent_api');
        }

        /**
         * Removes duplicate entries from the visitor_relationships table.
         *
         * @version 14.4
         */
        //self::delete_duplicate_data(); // todo to move in background cronjob

        /**
         * Remove old hash format assets
         *
         * @version 14.8.1
         */
        if (Option::get('bypass_ad_blockers', false) && $this->currentVersion == '14.8' && class_exists('WP_Statistics\Components\AssetNameObfuscator')) {
            $assetNameObfuscator = new AssetNameObfuscator();
            $assetNameObfuscator->deleteAllHashedFiles();
            $assetNameObfuscator->deleteDatabaseOption();
        }

        // Enable Top Metrics in Advanced Reporting Add-on By Default
        $advancedReportingOptions = Option::getAddonOptions('advanced_reporting');
        if ($advancedReportingOptions !== false && Option::getByAddon('email_top_metrics', 'advanced_reporting') === false) {
            Option::saveByAddon(array_merge(['email_top_metrics' => 1], $advancedReportingOptions), 'advanced_reporting');
        }

        /**
         * Update old DataPlus options.
         *
         * @version 14.10
         */
        if (version_compare($this->currentVersion, '14.10', '<') && (Option::get('link_tracker') || Option::get('download_tracker'))) {
            Option::saveByAddon([
                'link_tracker'            => Option::get('link_tracker'),
                'download_tracker'        => Option::get('download_tracker'),
                'latest_visitors_metabox' => '1',
            ], 'data_plus');
        }

        // Clear not used scheduled.
        if (function_exists('wp_clear_scheduled_hook')) {
            // Remove unused cron job for purging high hit count visitors daily
            wp_clear_scheduled_hook('wp_statistics_dbmaint_visitor_hook');

            // Remove referral db update cron
            wp_clear_scheduled_hook('wp_statistics_referrals_db_hook');
        }

        /**
         * Update old excluded URLs to the new structure with explicit wildcards.
         *
         * @version 14.10.3
         */
        if (version_compare($this->currentVersion, '14.10.3', '<') && Option::get('excluded_urls')) {
            $updatedExcludedUrls = $this->updateOldExcludedUrls();
            if (!empty($updatedExcludedUrls)) {
                Option::update('excluded_urls', implode("\n", $updatedExcludedUrls));
            }
        }
    }

    /**
     * Updates old excluded URLs to the new structure with explicit wildcards.
     *
     * @return array updated URLs.
     */
    private function updateOldExcludedUrls()
    {
        $updatedUrls = [];

        foreach (explode("\n", Option::get('excluded_urls')) as $url) {
            $url = wp_make_link_relative($url);
            $url = trim($url);

            // If the URL contains a query string, strip it
            $url = explode('?', $url)[0];

            // Trim leading/trailing slashes
            $url = trim($url, '/\\');

            // If the URL doesn't end with an asterisk (*), add one and make it a wildcard
            if (substr($url, -1) !== '*') {
                $url .= '*';
            }

            // Add the URL to the new list if it's not similar to others
            if (!in_array($url, $updatedUrls)) {
                $updatedUrls[] = $url;
            }
        }

        return $updatedUrls;
    }
}