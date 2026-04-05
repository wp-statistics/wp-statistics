<?php

namespace WP_Statistics\Service\ImportExport;

use WP_Statistics\Service\Database\DatabaseSchema;
use WP_Statistics\Utils\FileSystem;
use RuntimeException;

/**
 * Service for creating, listing, deleting, and restoring backups.
 *
 * Centralizes backup business logic that was previously embedded in ImportExportEndpoints.
 * Third-party plugins can extend the backup table list via the
 * `wp_statistics_backup_tables` filter.
 *
 * @since 15.0.0
 */
class BackupService
{
    /**
     * @var ImportExportManager
     */
    private $manager;

    /**
     * @param ImportExportManager $manager
     */
    public function __construct(ImportExportManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * List all backups with metadata.
     *
     * @return array[] Each entry: name, size, created_at — sorted by date desc.
     */
    public function list(): array
    {
        $backupDir = FileSystem::getBackupsDir();
        $backups   = [];

        if (!file_exists($backupDir)) {
            return $backups;
        }

        $files = glob(trailingslashit($backupDir) . '*.json');

        if (!is_array($files)) {
            return $backups;
        }

        foreach ($files as $file) {
            $backups[] = [
                'name'       => basename($file),
                'size'       => size_format(filesize($file)),
                'created_at' => date('Y-m-d H:i:s', filemtime($file)),
            ];
        }

        // Sort by date descending
        usort($backups, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $backups;
    }

    /**
     * Create a new backup.
     *
     * @return array{name: string, size: string}
     * @throws RuntimeException On write failure or no data.
     */
    public function create(): array
    {
        global $wpdb;

        $backupDir = FileSystem::getBackupsDir();

        if (!FileSystem::ensureBackupsDir()) {
            throw new RuntimeException(__('Failed to create backup directory.', 'wp-statistics'));
        }

        $backupData = [
            'meta' => [
                'version'    => WP_STATISTICS_VERSION,
                'created_at' => current_time('mysql'),
                'type'       => 'manual',
                'site_url'   => home_url(),
            ],
            'data' => [],
        ];

        $tablesToBackup = $this->getBackupTables();

        foreach ($tablesToBackup as $tableKey => $tableName) {
            if (!DatabaseSchema::tableExists($tableName)) {
                continue;
            }

            $rows = $wpdb->get_results("SELECT * FROM `{$tableName}`", ARRAY_A);

            if (!empty($rows)) {
                $backupData['data'][$tableKey] = $rows;
            }
        }

        if (empty($backupData['data'])) {
            throw new RuntimeException(__('No data to backup.', 'wp-statistics'));
        }

        // Calculate checksum
        $backupData['checksum'] = hash('sha256', json_encode($backupData['data']));

        // Generate filename
        $fileName = sprintf(
            'wp-statistics-manual-backup-%s-%s.json',
            date('Y-m-d'),
            substr(md5(uniqid()), 0, 8)
        );
        $filePath = trailingslashit($backupDir) . $fileName;

        $result = file_put_contents(
            $filePath,
            json_encode($backupData, JSON_PRETTY_PRINT)
        );

        if ($result === false) {
            throw new RuntimeException(__('Failed to write backup file.', 'wp-statistics'));
        }

        return [
            'name' => $fileName,
            'size' => size_format(filesize($filePath)),
        ];
    }

    /**
     * Delete a backup by filename.
     *
     * @param string $filename Backup filename (no path traversal allowed).
     * @return void
     * @throws RuntimeException If file not found or deletion fails.
     */
    public function delete(string $filename): void
    {
        $filename = sanitize_file_name($filename);
        $filePath = trailingslashit(FileSystem::getBackupsDir()) . $filename;

        if (!file_exists($filePath)) {
            throw new RuntimeException(__('Backup file not found.', 'wp-statistics'));
        }

        if (!@unlink($filePath)) {
            throw new RuntimeException(__('Failed to delete backup file.', 'wp-statistics'));
        }
    }

    /**
     * Restore a backup by filename.
     *
     * Reads the backup file, verifies its checksum, then delegates to the
     * WpStatisticsBackupAdapter for actual import.
     *
     * @param string $filename Backup filename.
     * @return array{imported: int, skipped: int, errors: array}
     * @throws RuntimeException On checksum mismatch, parse failure, or missing adapter.
     */
    public function restore(string $filename): array
    {
        $filename = sanitize_file_name($filename);
        $filePath = trailingslashit(FileSystem::getBackupsDir()) . $filename;

        if (!file_exists($filePath)) {
            throw new RuntimeException(__('Backup file not found.', 'wp-statistics'));
        }

        $content = file_get_contents($filePath);
        $backup  = json_decode($content, true);

        if (!$backup || !isset($backup['data'])) {
            throw new RuntimeException(__('Invalid backup file format.', 'wp-statistics'));
        }

        // Verify checksum if present
        if (isset($backup['checksum'])) {
            $calculatedChecksum = hash('sha256', json_encode($backup['data']));
            if ($calculatedChecksum !== $backup['checksum']) {
                throw new RuntimeException(__('Backup file checksum mismatch. The file may be corrupted.', 'wp-statistics'));
            }
        }

        // Use the WpStatisticsBackupAdapter to restore
        $adapter = $this->manager->getAdapter('wp_statistics_backup');

        // Write backup to temp file for import
        $tempDir = FileSystem::getImportsDir();
        FileSystem::ensureImportsDir();

        $tempFile = trailingslashit($tempDir) . 'restore-' . wp_generate_uuid4() . '.json';
        file_put_contents($tempFile, $content);

        try {
            $results = $adapter->processImport($tempFile);
        } finally {
            // Always clean up temp file
            @unlink($tempFile);
        }

        return $results;
    }

    /**
     * Get the full path to a backup file.
     *
     * @param string $filename Backup filename.
     * @return string Absolute path.
     * @throws RuntimeException If file not found.
     */
    public function getFilePath(string $filename): string
    {
        $filename = sanitize_file_name($filename);
        $filePath = trailingslashit(FileSystem::getBackupsDir()) . $filename;

        if (!file_exists($filePath)) {
            throw new RuntimeException(__('Backup file not found.', 'wp-statistics'));
        }

        return $filePath;
    }

    /**
     * Get tables to include in the backup.
     *
     * @return array<string, string> Table key => full table name.
     */
    private function getBackupTables(): array
    {
        $tables = [
            'visitors' => DatabaseSchema::table('visitors'),
            'sessions' => DatabaseSchema::table('sessions'),
            'views'    => DatabaseSchema::table('views'),
        ];

        /**
         * Filter the tables included in backups.
         *
         * Allows premium plugins to add additional tables to the backup.
         *
         * @param array<string, string> $tables Table key => full table name.
         */
        return apply_filters('wp_statistics_backup_tables', $tables);
    }
}
