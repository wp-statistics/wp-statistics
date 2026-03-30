<?php

namespace WP_Statistics\Service\Admin\Tools;

use WP_Statistics\Service\Database\DatabaseSchema;

/**
 * Provides system information about the plugin environment.
 *
 * Aggregates database table metadata and plugin/server version info.
 *
 * @since 15.0.0
 */
class SystemInfoService
{
    /**
     * Get database table information.
     *
     * @return array[] Each entry: key, name, description, records, size, engine, isLegacy, isAddon, addonName.
     */
    public function getTables(): array
    {
        $tables = [];

        foreach (DatabaseSchema::getAllTables(true) as $key => $tableName) {
            $tableInfo = DatabaseSchema::getTableInfo($key);

            $tables[] = [
                'key'         => $key,
                'name'        => $tableName,
                'description' => DatabaseSchema::getTableDescription($key),
                'records'     => DatabaseSchema::getRowCount($key),
                'size'        => isset($tableInfo['Data_length'])
                    ? size_format($tableInfo['Data_length'] + ($tableInfo['Index_length'] ?? 0))
                    : '-',
                'engine'      => $tableInfo['Engine'] ?? '-',
                'isLegacy'    => DatabaseSchema::isLegacyTable($key),
                'isAddon'     => DatabaseSchema::isAddonTable($key),
                'addonName'   => DatabaseSchema::getAddonName($key),
            ];
        }

        return $tables;
    }

    /**
     * Get plugin environment info (versions).
     *
     * @return array{version: string, db_version: string, php: string, mysql: string, wp: string}
     */
    public function getPluginInfo(): array
    {
        return [
            'version'    => WP_STATISTICS_VERSION,
            'db_version' => get_option('wp_statistics_db_version', '-'),
            'php'        => PHP_VERSION,
            'mysql'      => $GLOBALS['wpdb']->db_version(),
            'wp'         => get_bloginfo('version'),
        ];
    }
}
