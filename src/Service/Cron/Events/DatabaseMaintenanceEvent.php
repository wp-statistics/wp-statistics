<?php

namespace WP_Statistics\Service\Cron\Events;

use WP_Statistics\Service\Database\DatabaseSchema;
use WP_Statistics\Service\Options\OptionManager;

/**
 * Database Maintenance Cron Event.
 *
 * Handles data retention based on configured policy:
 * - forever: No action taken
 * - delete: Permanently removes all data older than cutoff
 * - archive: Creates backup, keeps summary tables, removes raw session/view data
 *
 * @since 15.0.0
 */
class DatabaseMaintenanceEvent extends AbstractCronEvent
{
    /**
     * @var string
     */
    protected $hook = 'wp_statistics_dbmaint_hook';

    /**
     * @var string
     */
    protected $recurrence = 'daily';

    /**
     * @var string
     */
    protected $description = 'Database Maintenance';

    /**
     * Maximum backups to keep.
     *
     * @var int
     */
    private const MAX_BACKUPS = 5;

    /**
     * Check if database maintenance should be scheduled.
     *
     * @return bool
     */
    public function shouldSchedule(): bool
    {
        // Schedule if retention mode is not 'forever'
        $mode = OptionManager::get('data_retention_mode', 'forever');
        return $mode !== 'forever' && (bool) OptionManager::get('schedule_dbmaint');
    }

    /**
     * Execute the database maintenance.
     *
     * @return void
     */
    public function execute(): void
    {
        $mode = OptionManager::get('data_retention_mode', 'forever');
        $days = (int) OptionManager::get('data_retention_days', 180);

        if ($mode === 'forever' || $days <= 0) {
            return; // No action needed
        }

        $cutoffDate = date('Y-m-d', strtotime("-{$days} days"));

        if ($mode === 'delete') {
            $this->deleteOldData($cutoffDate);
        } elseif ($mode === 'archive') {
            // Create automatic backup before archiving
            $this->createBackupBeforeArchive($cutoffDate);
            $this->archiveOldData($cutoffDate);
            // Clean up old backups (keep only last N)
            $this->cleanupOldBackups();
        }

        /**
         * Fires after database maintenance is performed.
         *
         * @param string $mode       Retention mode (delete|archive).
         * @param string $cutoffDate Cutoff date (Y-m-d).
         * @param int    $days       Retention days.
         */
        do_action('wp_statistics_after_maintenance', $mode, $cutoffDate, $days);
    }

    /**
     * Create a backup of data that will be archived.
     *
     * @param string $cutoffDate Cutoff date (Y-m-d format).
     * @return string|false Backup file path or false on failure.
     */
    private function createBackupBeforeArchive(string $cutoffDate)
    {
        global $wpdb;

        $backupDir = $this->getBackupDirectory();
        if (!$backupDir) {
            return false;
        }

        $backupData = [
            'meta' => [
                'version'     => WP_STATISTICS_VERSION,
                'created_at'  => current_time('mysql'),
                'cutoff_date' => $cutoffDate,
                'type'        => 'archive_backup',
                'site_url'    => home_url(),
            ],
            'data' => [],
        ];

        // Export data that will be archived (before cutoff date)
        $tablesToBackup = [
            'views'    => 'viewed_at',
            'sessions' => 'ended_at',
        ];

        foreach ($tablesToBackup as $tableKey => $dateColumn) {
            $tableName = DatabaseSchema::table($tableKey);

            if (!DatabaseSchema::tableExists($tableName)) {
                continue;
            }

            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM `{$tableName}` WHERE `{$dateColumn}` < %s",
                    $cutoffDate . ' 00:00:00'
                ),
                ARRAY_A
            );

            if (!empty($rows)) {
                $backupData['data'][$tableKey] = $rows;
            }
        }

        // Also backup visitors that will be orphaned
        $visitorsTable = DatabaseSchema::table('visitors');
        $sessionsTable = DatabaseSchema::table('sessions');

        if (DatabaseSchema::tableExists($visitorsTable) && DatabaseSchema::tableExists($sessionsTable)) {
            $orphanedVisitors = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT v.* FROM `{$visitorsTable}` v
                     LEFT JOIN `{$sessionsTable}` s ON v.id = s.visitor_id
                     WHERE s.id IS NULL OR s.ended_at < %s",
                    $cutoffDate . ' 00:00:00'
                ),
                ARRAY_A
            );

            if (!empty($orphanedVisitors)) {
                $backupData['data']['visitors'] = $orphanedVisitors;
            }
        }

        // Only create backup if there's data to backup
        if (empty($backupData['data'])) {
            return false;
        }

        // Calculate checksum
        $backupData['checksum'] = hash('sha256', json_encode($backupData['data']));

        // Generate filename
        $fileName = sprintf(
            'wp-statistics-archive-backup-%s-%s.json',
            date('Y-m-d'),
            substr(md5(uniqid()), 0, 8)
        );
        $filePath = $backupDir . '/' . $fileName;

        // Write backup file
        $result = file_put_contents(
            $filePath,
            json_encode($backupData, JSON_PRETTY_PRINT)
        );

        if ($result === false) {
            return false;
        }

        /**
         * Fires after an automatic backup is created before archiving.
         *
         * @param string $filePath   Path to the backup file.
         * @param string $cutoffDate Cutoff date.
         * @param array  $backupData Backup data.
         */
        do_action('wp_statistics_backup_created', $filePath, $cutoffDate, $backupData);

        return $filePath;
    }

    /**
     * Get backup directory, creating it if necessary.
     *
     * @return string|false Directory path or false on failure.
     */
    private function getBackupDirectory()
    {
        $uploadDir = wp_upload_dir();
        $backupDir = $uploadDir['basedir'] . '/wp-statistics-backups';

        if (!file_exists($backupDir)) {
            if (!wp_mkdir_p($backupDir)) {
                return false;
            }

            // Protect directory
            file_put_contents($backupDir . '/.htaccess', 'deny from all');
            file_put_contents($backupDir . '/index.php', '<?php // Silence is golden');
        }

        return $backupDir;
    }

    /**
     * Clean up old backups, keeping only the last N.
     *
     * @return int Number of backups deleted.
     */
    private function cleanupOldBackups(): int
    {
        $backupDir = $this->getBackupDirectory();
        if (!$backupDir) {
            return 0;
        }

        $files = glob($backupDir . '/wp-statistics-archive-backup-*.json');
        if (empty($files)) {
            return 0;
        }

        // Sort by modification time (newest first)
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $deleted = 0;

        // Delete files beyond the max count
        if (count($files) > self::MAX_BACKUPS) {
            $toDelete = array_slice($files, self::MAX_BACKUPS);

            foreach ($toDelete as $file) {
                if (@unlink($file)) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * Delete all data older than cutoff date.
     *
     * @param string $cutoffDate Cutoff date (Y-m-d format).
     * @return array Counts of deleted records per table.
     */
    public function deleteOldData(string $cutoffDate): array
    {
        global $wpdb;

        $results = [];

        // Tables and their date columns for deletion
        $tablesToPurge = [
            'views'    => 'viewed_at',
            'sessions' => 'ended_at',
            'visitors' => 'first_visit_at',
        ];

        foreach ($tablesToPurge as $tableKey => $dateColumn) {
            $tableName = DatabaseSchema::table($tableKey);

            if (!DatabaseSchema::tableExists($tableName)) {
                continue;
            }

            $count = $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM `{$tableName}` WHERE `{$dateColumn}` < %s",
                    $cutoffDate . ' 00:00:00'
                )
            );

            $results[$tableKey] = $count !== false ? $count : 0;
        }

        // Also clean up orphaned records
        $this->cleanupOrphanedRecords();

        // Optimize tables after deletion
        $this->optimizeTables(array_keys($tablesToPurge));

        return $results;
    }

    /**
     * Archive old data by keeping summaries and deleting raw data.
     *
     * Summary tables (summary, summary_totals) are preserved.
     * Raw data tables (views, sessions, visitors) are cleaned.
     *
     * @param string $cutoffDate Cutoff date (Y-m-d format).
     * @return array Counts of archived/deleted records per table.
     */
    public function archiveOldData(string $cutoffDate): array
    {
        global $wpdb;

        $results = [];

        // First ensure summary data exists for the period being archived
        // (The daily aggregation cron should have already done this)

        // Tables to archive (delete raw data, keep summaries)
        // Note: summary and summary_totals are NOT included - they're preserved
        $tablesToArchive = [
            'views'    => 'viewed_at',
            'sessions' => 'ended_at',
        ];

        foreach ($tablesToArchive as $tableKey => $dateColumn) {
            $tableName = DatabaseSchema::table($tableKey);

            if (!DatabaseSchema::tableExists($tableName)) {
                continue;
            }

            $count = $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM `{$tableName}` WHERE `{$dateColumn}` < %s",
                    $cutoffDate . ' 00:00:00'
                )
            );

            $results[$tableKey] = $count !== false ? $count : 0;
        }

        // Clean up orphaned visitors (no sessions left)
        $results['orphaned_visitors'] = $this->cleanupOrphanedVisitors();

        // Optimize tables after deletion
        $this->optimizeTables(array_keys($tablesToArchive));

        return $results;
    }

    /**
     * Clean up orphaned records across tables.
     *
     * @return void
     */
    private function cleanupOrphanedRecords(): void
    {
        global $wpdb;

        // Delete sessions without visitors
        $sessionsTable = DatabaseSchema::table('sessions');
        $visitorsTable = DatabaseSchema::table('visitors');

        if (DatabaseSchema::tableExists($sessionsTable) && DatabaseSchema::tableExists($visitorsTable)) {
            $wpdb->query(
                "DELETE s FROM `{$sessionsTable}` s
                 LEFT JOIN `{$visitorsTable}` v ON s.visitor_id = v.id
                 WHERE v.id IS NULL"
            );
        }

        // Delete views without sessions
        $viewsTable = DatabaseSchema::table('views');

        if (DatabaseSchema::tableExists($viewsTable) && DatabaseSchema::tableExists($sessionsTable)) {
            $wpdb->query(
                "DELETE vw FROM `{$viewsTable}` vw
                 LEFT JOIN `{$sessionsTable}` s ON vw.session_id = s.id
                 WHERE s.id IS NULL"
            );
        }
    }

    /**
     * Clean up orphaned visitors (those with no sessions).
     *
     * @return int Number of visitors deleted.
     */
    private function cleanupOrphanedVisitors(): int
    {
        global $wpdb;

        $visitorsTable = DatabaseSchema::table('visitors');
        $sessionsTable = DatabaseSchema::table('sessions');

        if (!DatabaseSchema::tableExists($visitorsTable) || !DatabaseSchema::tableExists($sessionsTable)) {
            return 0;
        }

        $count = $wpdb->query(
            "DELETE v FROM `{$visitorsTable}` v
             LEFT JOIN `{$sessionsTable}` s ON v.id = s.visitor_id
             WHERE s.id IS NULL"
        );

        return $count !== false ? $count : 0;
    }

    /**
     * Optimize specified tables.
     *
     * @param array $tableKeys Table keys to optimize.
     * @return void
     */
    private function optimizeTables(array $tableKeys): void
    {
        foreach ($tableKeys as $tableKey) {
            DatabaseSchema::optimizeTable($tableKey);
        }
    }

    /**
     * Get list of automatic backups.
     *
     * @return array List of backup files with metadata.
     */
    public static function getBackups(): array
    {
        $uploadDir = wp_upload_dir();
        $backupDir = $uploadDir['basedir'] . '/wp-statistics-backups';

        $backups = [];

        if (!file_exists($backupDir)) {
            return $backups;
        }

        $files = glob($backupDir . '/*.json');

        foreach ($files as $file) {
            $fileName = basename($file);
            $content  = file_get_contents($file);
            $data     = json_decode($content, true);

            $backups[] = [
                'name'        => $fileName,
                'path'        => $file,
                'size'        => filesize($file),
                'size_human'  => size_format(filesize($file)),
                'created_at'  => $data['meta']['created_at'] ?? date('Y-m-d H:i:s', filemtime($file)),
                'cutoff_date' => $data['meta']['cutoff_date'] ?? '',
                'type'        => $data['meta']['type'] ?? 'manual',
            ];
        }

        // Sort by date descending
        usort($backups, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $backups;
    }
}
