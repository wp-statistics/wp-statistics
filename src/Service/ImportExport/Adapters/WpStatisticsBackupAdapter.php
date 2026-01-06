<?php

namespace WP_Statistics\Service\ImportExport\Adapters;

use WP_Statistics\Service\ImportExport\Managers\IdRemapManager;
use WP_Statistics\Service\ImportExport\Parsers\JsonParser;
use WP_Statistics\Service\ImportExport\Resolvers\CountryResolver;
use WP_Statistics\Service\ImportExport\Resolvers\CityResolver;
use WP_Statistics\Service\ImportExport\Resolvers\DeviceTypeResolver;
use WP_Statistics\Service\ImportExport\Resolvers\DeviceBrowserResolver;
use WP_Statistics\Service\ImportExport\Resolvers\DeviceBrowserVersionResolver;
use WP_Statistics\Service\ImportExport\Resolvers\DeviceOsResolver;
use WP_Statistics\Service\ImportExport\Resolvers\ReferrerResolver;
use WP_Statistics\Service\ImportExport\Resolvers\ResourceResolver;
use WP_Statistics\Service\ImportExport\Resolvers\ResourceUriResolver;
use WP_Statistics\Records\VisitorRecord;
use WP_Statistics\Records\SessionRecord;
use WP_Statistics\Records\ViewRecord;
use WP_Statistics\Records\SummaryRecord;
use WP_Statistics\Records\SummaryTotalRecord;

/**
 * WP Statistics Backup Import Adapter.
 *
 * Imports WP Statistics backup files (JSON format).
 * Handles ID remapping for foreign key references.
 *
 * @since 15.0.0
 */
class WpStatisticsBackupAdapter extends AbstractImportAdapter
{
    /**
     * Adapter identifier.
     *
     * @var string
     */
    protected $name = 'wp_statistics_backup';

    /**
     * Human-readable label.
     *
     * @var string
     */
    protected $label = 'WP Statistics Backup';

    /**
     * Supported file extensions.
     *
     * @var array
     */
    protected $extensions = ['json'];

    /**
     * Target tables for import.
     *
     * @var array
     */
    protected $targetTables = [
        'visitors',
        'sessions',
        'views',
        'resources',
        'resource_uris',
        'summary',
        'summary_totals',
    ];

    /**
     * ID remap manager.
     *
     * @var IdRemapManager
     */
    private $idRemapManager;

    /**
     * Resolvers for lookup tables.
     *
     * @var array
     */
    private $resolvers = [];

    /**
     * Lookup references from backup.
     *
     * @var array
     */
    private $lookupRefs = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->idRemapManager = new IdRemapManager();
        $this->label          = __('WP Statistics Backup', 'wp-statistics');
    }

    /**
     * Get required columns.
     *
     * Backup files have a specific structure, not column-based.
     *
     * @return array
     */
    public function getRequiredColumns(): array
    {
        return ['meta', 'data'];
    }

    /**
     * Get optional columns.
     *
     * @return array
     */
    public function getOptionalColumns(): array
    {
        return ['lookup_refs'];
    }

    /**
     * Get field mapping.
     *
     * Backup format matches target schema, so mapping is 1:1.
     *
     * @return array
     */
    public function getFieldMapping(): array
    {
        return [];
    }

    /**
     * Validate the source file.
     *
     * @param string $filePath Path to file
     * @param array  $headers  Headers (not used for JSON)
     * @return bool
     */
    public function validateSource(string $filePath, array $headers): bool
    {
        $parser = new JsonParser();

        try {
            $parser->open($filePath);

            $metadata = $parser->getMetadata();

            if (empty($metadata)) {
                return false;
            }

            // Check schema version compatibility
            $schemaVersion = $metadata['schema_version'] ?? '';

            if (empty($schemaVersion)) {
                return false;
            }

            // Version 1.x is compatible
            if (!preg_match('/^1\./', $schemaVersion)) {
                return false;
            }

            $parser->close();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Transform a single row from source format to WP Statistics schema.
     *
     * For backup imports, the row is already in the correct format.
     * We just need to remap IDs.
     *
     * @param array $sourceRow   Raw row data from source file
     * @param array $fieldMapping Custom field mapping (not used)
     * @return array Transformed data
     */
    public function transformRow(array $sourceRow, array $fieldMapping = []): array
    {
        return $sourceRow;
    }

    /**
     * Estimate total record count in source file.
     *
     * @param string $filePath Path to the source file
     * @return int Estimated number of records
     */
    public function estimateRecordCount(string $filePath): int
    {
        $parser = new JsonParser();

        try {
            $parser->open($filePath);
            $metadata = $parser->getMetadata();
            $parser->close();

            if (!empty($metadata['record_counts'])) {
                return array_sum($metadata['record_counts']);
            }

            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Import backup from file.
     *
     * Handles the full import process including:
     * 1. Loading lookup references
     * 2. Importing lookup tables first (to get ID mappings)
     * 3. Importing main data tables with remapped IDs
     *
     * @param string   $filePath Path to backup file
     * @param callable $progressCallback Progress callback(processed, total, table)
     * @return array Import results
     */
    public function importFromFile(string $filePath, callable $progressCallback = null): array
    {
        $parser = new JsonParser();
        $parser->open($filePath);

        $metadata   = $parser->getMetadata();
        $this->lookupRefs = $parser->getLookupReferences() ?? [];

        $results = [
            'imported' => [],
            'skipped'  => [],
            'errors'   => [],
        ];

        // Initialize resolvers
        $this->initializeResolvers();

        // Import in dependency order
        $importOrder = [
            'resources',
            'resource_uris',
            'visitors',
            'sessions',
            'views',
            'summary',
            'summary_totals',
        ];

        $totalRecords    = $metadata['record_counts'] ?? [];
        $processedTotal  = 0;
        $totalToProcess  = array_sum($totalRecords);

        foreach ($importOrder as $table) {
            $tableData = $parser->getTableData($table);

            if (empty($tableData)) {
                continue;
            }

            $imported = 0;
            $skipped  = 0;
            $errors   = [];

            foreach ($tableData as $row) {
                try {
                    $newId = $this->insertRow($table, $row);

                    if ($newId !== null) {
                        $imported++;

                        // Store ID mapping
                        if (!empty($row['ID'])) {
                            $this->idRemapManager->addMapping($table, (int)$row['ID'], $newId);
                        }
                    } else {
                        $skipped++;
                    }
                } catch (\Exception $e) {
                    $errors[] = [
                        'row'     => $row['ID'] ?? 'unknown',
                        'message' => $e->getMessage(),
                    ];
                }

                $processedTotal++;

                if ($progressCallback !== null) {
                    $progressCallback($processedTotal, $totalToProcess, $table);
                }
            }

            $results['imported'][$table] = $imported;
            $results['skipped'][$table]  = $skipped;
            $results['errors'][$table]   = $errors;
        }

        $parser->close();

        return $results;
    }

    /**
     * Initialize resolvers for lookup tables.
     *
     * @return void
     */
    private function initializeResolvers(): void
    {
        $this->resolvers = [
            'countries'               => new CountryResolver(),
            'cities'                  => new CityResolver(),
            'device_types'            => new DeviceTypeResolver(),
            'device_browsers'         => new DeviceBrowserResolver(),
            'device_browser_versions' => new DeviceBrowserVersionResolver(),
            'device_oss'              => new DeviceOsResolver(),
            'referrers'               => new ReferrerResolver(),
            'resources'               => new ResourceResolver(),
            'resource_uris'           => new ResourceUriResolver(),
        ];

        // Pre-warm caches
        foreach ($this->resolvers as $resolver) {
            $resolver->warmCache(5000);
        }
    }

    /**
     * Insert a single row into the database.
     *
     * @param string $table Table name
     * @param array  $row   Row data
     * @return int|null New ID or null if skipped
     */
    private function insertRow(string $table, array $row): ?int
    {
        // Remove the original ID (will be auto-generated)
        $originalId = $row['ID'] ?? null;
        unset($row['ID']);

        // Remap foreign keys based on table
        $row = $this->remapForeignKeys($table, $row);

        // Get record instance and insert
        switch ($table) {
            case 'visitors':
                $record = new VisitorRecord();
                break;

            case 'sessions':
                $record = new SessionRecord();
                break;

            case 'views':
                $record = new ViewRecord();
                break;

            case 'resources':
                // Use resolver for find-or-create
                $resourceData = [
                    'resource_type' => $row['resource_type'] ?? '',
                    'resource_id'   => $row['resource_id'] ?? 0,
                    'cached_title'  => $row['cached_title'] ?? null,
                ];
                return $this->resolvers['resources']->resolve($resourceData);

            case 'resource_uris':
                // Use resolver for find-or-create
                $uriData = [
                    'resource_id' => $row['resource_id'] ?? 0,
                    'uri'         => $row['uri'] ?? '',
                ];
                return $this->resolvers['resource_uris']->resolve($uriData);

            case 'summary':
                $record = new SummaryRecord();
                break;

            case 'summary_totals':
                $record = new SummaryTotalRecord();
                break;

            default:
                return null;
        }

        return $record->insert($row);
    }

    /**
     * Remap foreign key references in a row.
     *
     * @param string $table Table name
     * @param array  $row   Row data
     * @return array Row with remapped IDs
     */
    private function remapForeignKeys(string $table, array $row): array
    {
        // Define FK mappings for each table
        $fkMappings = [
            'sessions' => [
                'visitor_id'                => 'visitors',
                'referrer_id'               => 'referrers',
                'country_id'                => 'countries',
                'city_id'                   => 'cities',
                'device_type_id'            => 'device_types',
                'device_os_id'              => 'device_oss',
                'device_browser_id'         => 'device_browsers',
                'device_browser_version_id' => 'device_browser_versions',
            ],
            'views' => [
                'session_id'      => 'sessions',
                'resource_uri_id' => 'resource_uris',
                'resource_id'     => 'resources',
            ],
            'resource_uris' => [
                'resource_id' => 'resources',
            ],
            'summary' => [
                'resource_uri_id' => 'resource_uris',
            ],
        ];

        if (!isset($fkMappings[$table])) {
            return $row;
        }

        // Remap using lookup references and ID remap manager
        foreach ($fkMappings[$table] as $column => $refTable) {
            if (!isset($row[$column]) || empty($row[$column])) {
                continue;
            }

            $oldId = (int)$row[$column];

            // First try ID remap manager (for main data tables)
            $newId = $this->idRemapManager->getNewId($refTable, $oldId);

            if ($newId !== null) {
                $row[$column] = $newId;
                continue;
            }

            // Then try lookup references (for lookup tables)
            if (isset($this->lookupRefs[$refTable][$oldId])) {
                $refData = $this->lookupRefs[$refTable][$oldId];

                // Use resolver to get or create the lookup record
                if (isset($this->resolvers[$refTable])) {
                    $newId = $this->resolvers[$refTable]->resolve($refData);

                    if ($newId !== null) {
                        $row[$column] = $newId;
                    } else {
                        $row[$column] = null;
                    }
                }
            }
        }

        return $row;
    }

    /**
     * Get ID remap manager.
     *
     * @return IdRemapManager
     */
    public function getIdRemapManager(): IdRemapManager
    {
        return $this->idRemapManager;
    }

    /**
     * Clear all cached data.
     *
     * @return void
     */
    public function clearCache(): void
    {
        $this->idRemapManager->clear();
        $this->lookupRefs = [];

        foreach ($this->resolvers as $resolver) {
            $resolver->clearCache();
        }
    }
}
