<?php

namespace WP_Statistics\Service\Cron;

use WP_Statistics\Service\Database\DatabaseSchema;

/**
 * Database Maintenance Manager.
 *
 * Centralizes database maintenance operations for analytics tables.
 * Use this class for:
 * - Data retention cleanup (deleting old views/sessions/visitors)
 * - Orphan record cleanup (removing orphaned sessions, views, visitors)
 * - Table optimization
 *
 * Note: This class uses direct SQL for maintenance operations because
 * these are bulk DELETE operations that don't fit the AnalyticsQuery
 * pattern (which is designed for SELECT queries).
 *
 * @since 15.0.0
 */
class DatabaseMaintenanceManager
{
    /**
     * Delete views older than a specific date.
     *
     * @param string $cutoffDate Date in Y-m-d format. Records before this date are deleted.
     * @param bool   $optimize   Whether to optimize the table after deletion.
     *
     * @return int Number of records deleted.
     */
    public static function deleteViewsOlderThan(string $cutoffDate, bool $optimize = true): int
    {
        global $wpdb;

        $tableName = DatabaseSchema::table('views');

        if (!DatabaseSchema::tableExists($tableName)) {
            return 0;
        }

        $count = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM `{$tableName}` WHERE `viewed_at` < %s",
                $cutoffDate . ' 00:00:00'
            )
        );

        if ($optimize && $count > 0) {
            DatabaseSchema::optimizeTable('views');
        }

        if ($count === false) {
            do_action('wp_statistics_maintenance_error', $wpdb->last_error, 'views');
            return 0;
        }

        return $count;
    }

    /**
     * Delete sessions older than a specific date.
     *
     * @param string $cutoffDate Date in Y-m-d format. Records before this date are deleted.
     * @param bool   $optimize   Whether to optimize the table after deletion.
     *
     * @return int Number of records deleted.
     */
    public static function deleteSessionsOlderThan(string $cutoffDate, bool $optimize = true): int
    {
        global $wpdb;

        $tableName = DatabaseSchema::table('sessions');

        if (!DatabaseSchema::tableExists($tableName)) {
            return 0;
        }

        $count = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM `{$tableName}` WHERE `ended_at` < %s",
                $cutoffDate . ' 00:00:00'
            )
        );

        if ($optimize && $count > 0) {
            DatabaseSchema::optimizeTable('sessions');
        }

        if ($count === false) {
            do_action('wp_statistics_maintenance_error', $wpdb->last_error, 'sessions');
            return 0;
        }

        return $count;
    }

    /**
     * Delete visitors older than a specific date.
     *
     * @param string $cutoffDate Date in Y-m-d format. Records before this date are deleted.
     * @param bool   $optimize   Whether to optimize the table after deletion.
     *
     * @return int Number of records deleted.
     */
    public static function deleteVisitorsOlderThan(string $cutoffDate, bool $optimize = true): int
    {
        global $wpdb;

        $tableName = DatabaseSchema::table('visitors');

        if (!DatabaseSchema::tableExists($tableName)) {
            return 0;
        }

        $count = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM `{$tableName}` WHERE `first_visit_at` < %s",
                $cutoffDate . ' 00:00:00'
            )
        );

        if ($optimize && $count > 0) {
            DatabaseSchema::optimizeTable('visitors');
        }

        if ($count === false) {
            do_action('wp_statistics_maintenance_error', $wpdb->last_error, 'visitors');
            return 0;
        }

        return $count;
    }

    /**
     * Delete orphaned visitors (visitors with no associated sessions).
     *
     * After deleting sessions, some visitors may have no remaining sessions.
     * This method cleans up those orphaned visitor records.
     *
     * @param bool $optimize Whether to optimize the table after deletion.
     *
     * @return int Number of visitors deleted.
     */
    public static function deleteOrphanedVisitors(bool $optimize = true): int
    {
        global $wpdb;

        $visitorsTable = DatabaseSchema::table('visitors');
        $sessionsTable = DatabaseSchema::table('sessions');

        if (!DatabaseSchema::tableExists($visitorsTable) || !DatabaseSchema::tableExists($sessionsTable)) {
            return 0;
        }

        $count = $wpdb->query(
            "DELETE v FROM `{$visitorsTable}` v
             LEFT JOIN `{$sessionsTable}` s ON v.ID = s.visitor_id
             WHERE s.ID IS NULL"
        );

        if ($optimize && $count > 0) {
            DatabaseSchema::optimizeTable('visitors');
        }

        if ($count === false) {
            do_action('wp_statistics_maintenance_error', $wpdb->last_error, 'visitors');
            return 0;
        }

        return $count;
    }

    /**
     * Delete orphaned sessions (sessions with no associated visitor).
     *
     * Sessions may become orphaned if the visitor was deleted without
     * cascade deletion. This method cleans up those orphaned records.
     *
     * @param bool $optimize Whether to optimize the table after deletion.
     *
     * @return int Number of sessions deleted.
     */
    public static function deleteOrphanedSessions(bool $optimize = true): int
    {
        global $wpdb;

        $sessionsTable = DatabaseSchema::table('sessions');
        $visitorsTable = DatabaseSchema::table('visitors');

        if (!DatabaseSchema::tableExists($sessionsTable) || !DatabaseSchema::tableExists($visitorsTable)) {
            return 0;
        }

        $count = $wpdb->query(
            "DELETE s FROM `{$sessionsTable}` s
             LEFT JOIN `{$visitorsTable}` v ON s.visitor_id = v.ID
             WHERE v.ID IS NULL"
        );

        if ($optimize && $count > 0) {
            DatabaseSchema::optimizeTable('sessions');
        }

        if ($count === false) {
            do_action('wp_statistics_maintenance_error', $wpdb->last_error, 'sessions');
            return 0;
        }

        return $count;
    }

    /**
     * Delete orphaned views (views with no associated session).
     *
     * Views may become orphaned if the session was deleted without
     * cascade deletion. This method cleans up those orphaned records.
     *
     * @param bool $optimize Whether to optimize the table after deletion.
     *
     * @return int Number of views deleted.
     */
    public static function deleteOrphanedViews(bool $optimize = true): int
    {
        global $wpdb;

        $viewsTable    = DatabaseSchema::table('views');
        $sessionsTable = DatabaseSchema::table('sessions');

        if (!DatabaseSchema::tableExists($viewsTable) || !DatabaseSchema::tableExists($sessionsTable)) {
            return 0;
        }

        $count = $wpdb->query(
            "DELETE vw FROM `{$viewsTable}` vw
             LEFT JOIN `{$sessionsTable}` s ON vw.session_id = s.ID
             WHERE s.ID IS NULL"
        );

        if ($optimize && $count > 0) {
            DatabaseSchema::optimizeTable('views');
        }

        if ($count === false) {
            do_action('wp_statistics_maintenance_error', $wpdb->last_error, 'views');
            return 0;
        }

        return $count;
    }

    /**
     * Clean up all orphaned records across tables.
     *
     * Deletes orphaned records in the correct order:
     * 1. Orphaned views (no session)
     * 2. Orphaned sessions (no visitor)
     * 3. Orphaned visitors (no sessions) - if requested
     *
     * @param bool $includeVisitors Whether to also clean orphaned visitors.
     * @param bool $optimize        Whether to optimize tables after deletion.
     *
     * @return array Counts of deleted records per type.
     */
    public static function cleanupAllOrphanedRecords(bool $includeVisitors = false, bool $optimize = true): array
    {
        $results = [
            'views'    => self::deleteOrphanedViews(false),
            'sessions' => self::deleteOrphanedSessions(false),
        ];

        if ($includeVisitors) {
            $results['visitors'] = self::deleteOrphanedVisitors(false);
        }

        // Optimize tables if any records were deleted
        if ($optimize && array_sum($results) > 0) {
            foreach (array_keys($results) as $tableKey) {
                if ($results[$tableKey] > 0) {
                    DatabaseSchema::optimizeTable($tableKey);
                }
            }
        }

        return $results;
    }

    /**
     * Delete all analytics data (for GDPR erasure or data reset).
     *
     * WARNING: This permanently deletes all analytics data!
     *
     * @return array Counts of deleted records per table.
     */
    public static function purgeAllData(): array
    {
        global $wpdb;

        $tables = ['views', 'sessions', 'visitors', 'resource_uris', 'resources'];
        $results = [];

        foreach ($tables as $tableKey) {
            $tableName = DatabaseSchema::table($tableKey);

            if (!DatabaseSchema::tableExists($tableName)) {
                $results[$tableKey] = 0;
                continue;
            }

            $count = $wpdb->query("TRUNCATE TABLE `{$tableName}`");
            $results[$tableKey] = $count !== false ? $count : 0;
        }

        return $results;
    }

    /**
     * Get table row counts for maintenance dashboard.
     *
     * @return array Associative array of [tableKey => rowCount].
     */
    public static function getTableStats(): array
    {
        global $wpdb;

        $tables = ['views', 'sessions', 'visitors', 'resources', 'resource_uris'];
        $stats = [];

        foreach ($tables as $tableKey) {
            $tableName = DatabaseSchema::table($tableKey);

            if (!DatabaseSchema::tableExists($tableName)) {
                $stats[$tableKey] = 0;
                continue;
            }

            $count = $wpdb->get_var("SELECT COUNT(*) FROM `{$tableName}`");
            $stats[$tableKey] = $count !== null ? (int) $count : 0;
        }

        return $stats;
    }

    /**
     * Get maintenance page info for the React frontend.
     *
     * @return array {hasUserIds: bool, eventsTableExists: bool, eventNames: string[], tableStats: array}
     */
    public static function getMaintenanceInfo(): array
    {
        global $wpdb;

        $sessionsTable = DatabaseSchema::table('sessions');
        $hasUserIds    = false;

        if (DatabaseSchema::tableExists($sessionsTable)) {
            $hasUserIds = (bool) $wpdb->get_var(
                "SELECT EXISTS (SELECT 1 FROM `{$sessionsTable}` WHERE `user_id` IS NOT NULL)"
            );
        }

        $eventsTable       = DatabaseSchema::table('events');
        $eventsTableExists = DatabaseSchema::tableExists($eventsTable);
        $eventNames        = [];

        if ($eventsTableExists) {
            $eventNames = $wpdb->get_col(
                "SELECT DISTINCT `event_name` FROM `{$eventsTable}` ORDER BY `event_name`"
            );
        }

        return [
            'hasUserIds'        => $hasUserIds,
            'eventsTableExists' => $eventsTableExists,
            'eventNames'        => $eventNames ?: [],
        ];
    }

    /**
     * Remove all user ID associations from session records.
     *
     * Sets user_id to NULL for GDPR/privacy compliance.
     *
     * @return int Number of rows updated.
     */
    public static function removeUserIds(): int
    {
        global $wpdb;

        $tableName = DatabaseSchema::table('sessions');

        if (!DatabaseSchema::tableExists($tableName)) {
            return 0;
        }

        $count = $wpdb->query(
            "UPDATE `{$tableName}` SET `user_id` = NULL WHERE `user_id` IS NOT NULL"
        );

        if ($count === false) {
            do_action('wp_statistics_maintenance_error', $wpdb->last_error, 'sessions');
            return 0;
        }

        return $count;
    }

    /**
     * Delete all event records for a specific event name.
     *
     * @param string $eventName The event name to delete.
     * @param bool   $optimize  Whether to optimize the table after deletion.
     *
     * @return int Number of records deleted.
     */
    public static function deleteEventsByName(string $eventName, bool $optimize = true): int
    {
        global $wpdb;

        $tableName = DatabaseSchema::table('events');

        if (!DatabaseSchema::tableExists($tableName)) {
            return 0;
        }

        $count = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM `{$tableName}` WHERE `event_name` = %s",
                $eventName
            )
        );

        if ($count === false) {
            do_action('wp_statistics_maintenance_error', $wpdb->last_error, 'events');
            return 0;
        }

        if ($optimize && $count > 0) {
            DatabaseSchema::optimizeTable('events');
        }

        return $count;
    }

    /**
     * Delete sessions that exceed a view threshold (bot cleanup).
     *
     * Finds sessions with more views than the threshold, deletes their views,
     * deletes the sessions, and cleans up orphaned visitors.
     *
     * @param int $viewThreshold Minimum view count to consider a session as bot traffic.
     *
     * @return array{sessions: int, views: int, parameters: int, events: int, visitors: int} Counts of deleted records.
     */
    public static function deleteBotSessions(int $viewThreshold): array
    {
        global $wpdb;

        $viewThreshold = max($viewThreshold, 10);
        $viewsTable      = DatabaseSchema::table('views');
        $sessionsTable   = DatabaseSchema::table('sessions');
        $parametersTable = DatabaseSchema::table('parameters');
        $eventsTable     = DatabaseSchema::table('events');

        $results = ['sessions' => 0, 'views' => 0, 'parameters' => 0, 'events' => 0, 'visitors' => 0];

        if (!DatabaseSchema::tableExists($viewsTable) || !DatabaseSchema::tableExists($sessionsTable)) {
            return $results;
        }

        // Find session IDs exceeding the threshold
        $sessionIds = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT `session_id` FROM `{$viewsTable}` GROUP BY `session_id` HAVING COUNT(*) > %d",
                $viewThreshold
            )
        );

        if (empty($sessionIds)) {
            return $results;
        }

        // Process in batches to avoid MySQL query length limits
        $batches = array_chunk($sessionIds, 1000);

        foreach ($batches as $batch) {
            $placeholders = implode(',', array_fill(0, count($batch), '%d'));

            // Delete views for these sessions
            $viewsDeleted = $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM `{$viewsTable}` WHERE `session_id` IN ({$placeholders})",
                    ...$batch
                )
            );

            if ($viewsDeleted !== false) {
                $results['views'] += $viewsDeleted;
            }

            // Delete parameters for these sessions
            if (DatabaseSchema::tableExists($parametersTable)) {
                $paramsDeleted = $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM `{$parametersTable}` WHERE `session_id` IN ({$placeholders})",
                        ...$batch
                    )
                );

                if ($paramsDeleted !== false) {
                    $results['parameters'] += $paramsDeleted;
                }
            }

            // Delete events for these sessions
            if (DatabaseSchema::tableExists($eventsTable)) {
                $eventsDeleted = $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM `{$eventsTable}` WHERE `session_id` IN ({$placeholders})",
                        ...$batch
                    )
                );

                if ($eventsDeleted !== false) {
                    $results['events'] += $eventsDeleted;
                }
            }

            // Delete the sessions themselves
            $sessionsDeleted = $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM `{$sessionsTable}` WHERE `ID` IN ({$placeholders})",
                    ...$batch
                )
            );

            if ($sessionsDeleted !== false) {
                $results['sessions'] += $sessionsDeleted;
            }
        }

        // Clean up orphaned visitors
        $results['visitors'] = self::deleteOrphanedVisitors(false);

        // Optimize affected tables
        if (array_sum($results) > 0) {
            DatabaseSchema::optimizeTable('views');
            DatabaseSchema::optimizeTable('sessions');
            DatabaseSchema::optimizeTable('visitors');

            if ($results['parameters'] > 0) {
                DatabaseSchema::optimizeTable('parameters');
            }

            if ($results['events'] > 0) {
                DatabaseSchema::optimizeTable('events');
            }
        }

        return $results;
    }
}
