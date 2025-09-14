<?php

namespace WP_Statistics\Service\Database\Managers;

use WP_Statistics\Service\Database\DatabaseFactory;
use WP_Statistics\Service\Database\Schema\Manager;
use WP_Statistics;

/**
 * Class SchemaMaintainer
 *
 * Responsible for maintaining the integrity of the database schema by providing
 * automated inspection and repair capabilities. This class ensures that all tables
 * have the correct structure by:
 * - Detecting missing columns across all plugin tables
 * - Detecting missing or incorrect indexes
 * - Providing repair functionality to add missing columns and indexes
 * - Handling errors gracefully during both inspection and repair operations
 *
 * The maintainer works in conjunction with the Schema Manager to compare actual
 * database structure against expected schemas and fix any discrepancies.
 */
class SchemaMaintainer
{
    /**
     * Cache key used to store the result of a successful schema check.
     *
     * @var string
     */
    private const SCHEMA_CHECK_SUCCESS = 'wp_statistics_schema_check_success';

    /**
     * Inspects database tables and returns any structural issues found
     *
     * @return array Schema inspection results
     */
    public static function check()
    {
        $results = [
            'status' => 'success',
            'issues' => [],
            'errors' => []
        ];

        if (get_transient(self::SCHEMA_CHECK_SUCCESS)) {
            return $results;
        }

        try {
            $tableNames = Manager::getAllTableNames();

            foreach ($tableNames as $tableName) {
                $inspect = DatabaseFactory::table('inspect')
                    ->setName($tableName)
                    ->execute();

                $schema = Manager::getSchemaForTable($tableName);

                if (!$schema) {
                    continue;
                }

                if (!$inspect->getResult()) {
                    $results['issues'][] = [
                        'type'   => 'table_missing',
                        'table'  => $tableName,
                        'schema' => $schema,
                    ];
                    continue;
                }

                try {
                    // Check columns
                    $columns = DatabaseFactory::table('inspect_columns')
                        ->setName($tableName)
                        ->execute()
                        ->getResult();

                    $existingColumns = array_column($columns, 'Type', 'Field');

                    foreach ($schema['columns'] as $columnName => $definition) {
                        if (isset($existingColumns[$columnName])) {
                            continue;
                        }

                        $indexDefinition = '';
                        if (!empty($schema['constraints'][$columnName])) {
                            $indexDefinition = $schema['constraints'][$columnName];
                        }

                        $results['issues'][] = [
                            'type'             => 'missing_column',
                            'table'            => $tableName,
                            'column'           => $columnName,
                            'columnDefinition' => $definition,
                            'indexDefinition'  => $indexDefinition
                        ];
                    }

                } catch (\RuntimeException $e) {
                    if (strpos($e->getMessage(), 'does not exist') !== false) {
                        $results['errors'][] = [
                            'type'  => 'failed',
                            'table' => $tableName
                        ];
                        WP_Statistics::log($e->getMessage());
                        continue;
                    }
                    WP_Statistics::log($e->getMessage());
                }
            }

            if (!empty($results['errors'])) {
                $results['status'] = count($results['issues']) > 0 ? 'warning' : 'error';
            } elseif (!empty($results['issues'])) {
                $results['status'] = 'warning';
            }

        } catch (\Exception $e) {
            $results['status']   = 'error';
            $results['errors'][] = [
                'type'    => 'system_error',
                'message' => $e->getMessage()
            ];
            WP_Statistics::log($e->getMessage());
        }

        if ($results['status'] === 'success') {
            set_transient(self::SCHEMA_CHECK_SUCCESS, 1, 24 * HOUR_IN_SECONDS);
        }

        return $results;
    }

    /**
     * Repairs any identified schema issues in the database tables.
     *
     * @param bool $clearCache Clear the cached schema-check transient before repairing.
     * @return array Repair operation results.
     */
    public static function repair($clearCache = false)
    {
        if ($clearCache) {
            delete_transient(self::SCHEMA_CHECK_SUCCESS);
        }

        $results = [
            'status' => 'success',
            'fixed'  => [],
            'failed' => []
        ];

        try {
            $checkResults = self::check();

            if (empty($checkResults['issues'])) {
                return $results;
            }

            foreach ($checkResults['issues'] as $issue) {
                try {
                    if ($issue['type'] === 'missing_column') {
                        DatabaseFactory::table('repair')
                            ->setName($issue['table'])
                            ->setArgs([
                                'column'          => $issue['column'],
                                'definition'      => $issue['columnDefinition'],
                                'indexDefinition' => $issue['indexDefinition']
                            ])
                            ->execute();

                        DatabaseFactory::table('inspect_columns')
                            ->setName($issue['table'])
                            ->updateCache();
                    }

                    if ($issue['type'] === 'table_missing') {
                        TableHandler::createTable($issue['table'], $issue['schema']);
                        DatabaseFactory::table('inspect')
                            ->setName($issue['table'])
                            ->updateCache();
                    }

                    $results['fixed'][] = $issue;
                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'issue'   => $issue,
                        'type'    => 'repair_failed',
                        'message' => $e->getMessage()
                    ];
                    WP_Statistics::log($e->getMessage());
                }
            }

            if (!empty($results['failed'])) {
                $results['status'] = 'partial';
            }
        } catch (\Exception $e) {
            $results['status']   = 'error';
            $results['errors'][] = [
                'type'    => 'system_error',
                'message' => $e->getMessage()
            ];
            WP_Statistics::log($e->getMessage());
        }

        return $results;
    }
}