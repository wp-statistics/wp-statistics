<?php

namespace WP_Statistics\Service\ImportExport\Adapters;

use WP_Statistics\Service\Database\DatabaseSchema;
use WP_Statistics\Service\ImportExport\Managers\IdRemapManager;
use WP_Statistics\Service\ImportExport\Resolvers\CountryResolver;
use WP_Statistics\Service\ImportExport\Resolvers\CityResolver;
use WP_Statistics\Service\ImportExport\Resolvers\DeviceTypeResolver;
use WP_Statistics\Service\ImportExport\Resolvers\DeviceBrowserResolver;
use WP_Statistics\Service\ImportExport\Resolvers\DeviceBrowserVersionResolver;
use WP_Statistics\Service\ImportExport\Resolvers\DeviceOsResolver;
use WP_Statistics\Service\ImportExport\Resolvers\ReferrerResolver;
use WP_Statistics\Service\ImportExport\Resolvers\ResourceResolver;
use WP_Statistics\Service\ImportExport\Resolvers\ResourceUriResolver;
use WP_Statistics\Utils\Query;

/**
 * Legacy V14 Migration Adapter.
 *
 * Migrates data from WP Statistics v14 tables to v15 schema.
 * Handles transformation of denormalized v14 data to normalized v15 format.
 *
 * Source Tables (v14):
 * - wp_statistics_visitor → visitors + sessions
 * - wp_statistics_pages → resources + resource_uris
 * - wp_statistics_visitor_relationships → views
 * - wp_statistics_historical → summary tables
 *
 * @since 15.0.0
 */
class LegacyV14Adapter extends AbstractImportAdapter
{
    /**
     * Adapter identifier.
     *
     * @var string
     */
    protected $name = 'legacy_v14';

    /**
     * Human-readable label.
     *
     * @var string
     */
    protected $label = 'WP Statistics v14 Migration';

    /**
     * Supported file extensions.
     *
     * Not file-based, migrates from database tables.
     *
     * @var array
     */
    protected $extensions = [];

    /**
     * Target tables for migration.
     *
     * @var array
     */
    protected $targetTables = [
        'visitors',
        'sessions',
        'views',
        'resources',
        'resource_uris',
    ];

    /**
     * Source tables (v14).
     *
     * @var array
     */
    private $sourceTables = [
        'visitor',
        'pages',
        'visitor_relationships',
        'historical',
    ];

    /**
     * ID remap manager.
     *
     * @var IdRemapManager
     */
    private $idRemapManager;

    /**
     * Resolvers.
     *
     * @var array
     */
    private $resolvers = [];

    /**
     * Batch size for processing.
     */
    private const BATCH_SIZE = 100;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->idRemapManager = new IdRemapManager();
        $this->label          = __('WP Statistics v14 Migration', 'wp-statistics');
    }

    /**
     * Get required columns.
     *
     * @return array
     */
    public function getRequiredColumns(): array
    {
        return [];
    }

    /**
     * Get optional columns.
     *
     * @return array
     */
    public function getOptionalColumns(): array
    {
        return [];
    }

    /**
     * Get field mapping.
     *
     * @return array
     */
    public function getFieldMapping(): array
    {
        return [
            // visitor → visitor + session
            'ip'            => 'ip',
            'last_counter'  => 'created_at',
            'location'      => 'country_code',
            'city'          => 'city_name',
            'browser'       => 'browser_name',
            'version'       => 'browser_version',
            'platform'      => 'os_name',
            'device'        => 'device_type',
            'model'         => 'device_model',
            'user_id'       => 'user_id',
            'hits'          => 'total_views',
            'referred'      => 'referrer_url',
        ];
    }

    /**
     * Validate source.
     *
     * Checks if v14 tables exist and have data.
     *
     * @param string $filePath Not used for database migration
     * @param array  $headers  Not used for database migration
     * @return bool
     */
    public function validateSource(string $filePath, array $headers): bool
    {
        // Check if legacy tables exist
        foreach ($this->sourceTables as $table) {
            $fullTableName = DatabaseSchema::table($table);

            if (!DatabaseSchema::tableExists($fullTableName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Transform a v14 visitor row to v15 format.
     *
     * @param array $sourceRow   V14 visitor row
     * @param array $fieldMapping Custom field mapping
     * @return array Transformed data for v15
     */
    public function transformRow(array $sourceRow, array $fieldMapping = []): array
    {
        return $this->transformVisitorRow($sourceRow);
    }

    /**
     * Estimate record count for migration.
     *
     * @param string $filePath Not used
     * @return int Total records to migrate
     */
    public function estimateRecordCount(string $filePath): int
    {
        $total = 0;

        foreach ($this->sourceTables as $table) {
            $total += DatabaseSchema::getRowCount($table);
        }

        return $total;
    }

    /**
     * Run the full migration process.
     *
     * @param callable|null $progressCallback Progress callback(processed, total, stage)
     * @return array Migration results
     */
    public function migrate(?callable $progressCallback = null): array
    {
        $results = [
            'visitors_created'       => 0,
            'sessions_created'       => 0,
            'views_created'          => 0,
            'resources_created'      => 0,
            'resource_uris_created'  => 0,
            'skipped'                => 0,
            'errors'                 => [],
        ];

        // Initialize resolvers
        $this->initializeResolvers();

        $totalRecords    = $this->estimateRecordCount('');
        $processedTotal  = 0;

        // Step 1: Migrate pages to resources + resource_uris
        $pageResults = $this->migratePages(function ($processed, $total, $stage) use (&$processedTotal, $totalRecords, $progressCallback) {
            $processedTotal++;
            if ($progressCallback) {
                $progressCallback($processedTotal, $totalRecords, 'pages');
            }
        });

        $results['resources_created']     = $pageResults['resources'];
        $results['resource_uris_created'] = $pageResults['resource_uris'];

        // Step 2: Migrate visitors to visitors + sessions
        $visitorResults = $this->migrateVisitors(function ($processed, $total, $stage) use (&$processedTotal, $totalRecords, $progressCallback) {
            $processedTotal++;
            if ($progressCallback) {
                $progressCallback($processedTotal, $totalRecords, 'visitors');
            }
        });

        $results['visitors_created'] = $visitorResults['visitors'];
        $results['sessions_created'] = $visitorResults['sessions'];

        // Step 3: Migrate visitor_relationships to views
        $viewResults = $this->migrateViews(function ($processed, $total, $stage) use (&$processedTotal, $totalRecords, $progressCallback) {
            $processedTotal++;
            if ($progressCallback) {
                $progressCallback($processedTotal, $totalRecords, 'views');
            }
        });

        $results['views_created'] = $viewResults['views'];

        return $results;
    }

    /**
     * Initialize resolvers.
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
     * Migrate pages table to resources + resource_uris.
     *
     * @param callable|null $progressCallback Progress callback
     * @return array Results
     */
    private function migratePages(?callable $progressCallback = null): array
    {
        $results = [
            'resources'     => 0,
            'resource_uris' => 0,
        ];

        $offset = 0;

        while (true) {
            $pages = Query::select('*')
                ->from('pages')
                ->limit(self::BATCH_SIZE)
                ->offset($offset)
                ->getAll();

            if (empty($pages)) {
                break;
            }

            foreach ($pages as $page) {
                // Resolve or create resource
                $resourceData = [
                    'resource_type' => $page->type ?? 'post',
                    'resource_id'   => $page->id ?? 0,
                ];

                $resourceId = $this->resolvers['resources']->resolve($resourceData);

                if ($resourceId) {
                    $results['resources']++;

                    // Store mapping: pages.id → resources.ID
                    $this->idRemapManager->addMapping('pages', (int)$page->page_id, $resourceId);

                    // Create resource_uri
                    $uriData = [
                        'resource_id' => $resourceId,
                        'uri'         => $page->uri ?? '/',
                    ];

                    $uriId = $this->resolvers['resource_uris']->resolve($uriData);

                    if ($uriId) {
                        $results['resource_uris']++;

                        // Store mapping for later use
                        $this->idRemapManager->addMapping('resource_uris', (int)$page->page_id, $uriId);
                    }
                }

                if ($progressCallback) {
                    $progressCallback(1, 1, 'pages');
                }
            }

            if (count($pages) < self::BATCH_SIZE) {
                break;
            }

            $offset += self::BATCH_SIZE;
        }

        return $results;
    }

    /**
     * Migrate visitors table to visitors + sessions.
     *
     * @param callable|null $progressCallback Progress callback
     * @return array Results
     */
    private function migrateVisitors(?callable $progressCallback = null): array
    {
        $results = [
            'visitors' => 0,
            'sessions' => 0,
        ];

        $offset = 0;

        while (true) {
            $visitors = Query::select('*')
                ->from('visitor')
                ->limit(self::BATCH_SIZE)
                ->offset($offset)
                ->getAll();

            if (empty($visitors)) {
                break;
            }

            foreach ($visitors as $v14Visitor) {
                $transformed = $this->transformVisitorRow((array)$v14Visitor);

                // Create visitor record
                $visitorData = [
                    'hash'       => $transformed['hash'] ?? '',
                    'created_at' => $transformed['created_at'] ?? current_time('mysql'),
                ];

                global $wpdb;
                $wpdb->insert(
                    DatabaseSchema::table('visitors'),
                    $visitorData
                );
                $visitorId = $wpdb->insert_id;

                if ($visitorId) {
                    $results['visitors']++;

                    // Store mapping
                    $this->idRemapManager->addMapping('visitor', (int)$v14Visitor->ID, $visitorId);

                    // Create session record
                    $sessionData = $this->buildSessionData($transformed, $visitorId, $v14Visitor);

                    $wpdb->insert(
                        DatabaseSchema::table('sessions'),
                        $sessionData
                    );
                    $sessionId = $wpdb->insert_id;

                    if ($sessionId) {
                        $results['sessions']++;

                        // Store session mapping for views
                        $this->idRemapManager->addMapping('sessions', (int)$v14Visitor->ID, $sessionId);
                    }
                }

                if ($progressCallback) {
                    $progressCallback(1, 1, 'visitors');
                }
            }

            if (count($visitors) < self::BATCH_SIZE) {
                break;
            }

            $offset += self::BATCH_SIZE;
        }

        return $results;
    }

    /**
     * Migrate visitor_relationships to views.
     *
     * @param callable|null $progressCallback Progress callback
     * @return array Results
     */
    private function migrateViews(?callable $progressCallback = null): array
    {
        $results = ['views' => 0];

        $offset = 0;

        while (true) {
            $relationships = Query::select('*')
                ->from('visitor_relationships')
                ->limit(self::BATCH_SIZE)
                ->offset($offset)
                ->getAll();

            if (empty($relationships)) {
                break;
            }

            global $wpdb;

            foreach ($relationships as $rel) {
                // Get remapped session ID
                $sessionId = $this->idRemapManager->getNewId('sessions', (int)$rel->visitor_id);

                // Get remapped resource_uri ID
                $resourceUriId = $this->idRemapManager->getNewId('resource_uris', (int)$rel->page_id);

                // Get resource ID (from resource_uris if available)
                $resourceId = null;
                if ($resourceUriId) {
                    $resourceUri = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT resource_id FROM " . DatabaseSchema::table('resource_uris') . " WHERE ID = %d",
                            $resourceUriId
                        )
                    );
                    $resourceId = $resourceUri->resource_id ?? null;
                }

                if ($sessionId && $resourceUriId) {
                    $viewData = [
                        'session_id'      => $sessionId,
                        'resource_uri_id' => $resourceUriId,
                        'resource_id'     => $resourceId ?? 0,
                        'viewed_at'       => $rel->date ?? current_time('mysql'),
                    ];

                    $wpdb->insert(
                        DatabaseSchema::table('views'),
                        $viewData
                    );

                    if ($wpdb->insert_id) {
                        $results['views']++;
                    }
                }

                if ($progressCallback) {
                    $progressCallback(1, 1, 'views');
                }
            }

            if (count($relationships) < self::BATCH_SIZE) {
                break;
            }

            $offset += self::BATCH_SIZE;
        }

        return $results;
    }

    /**
     * Transform a v14 visitor row.
     *
     * @param array $v14Row V14 visitor row
     * @return array Transformed data
     */
    private function transformVisitorRow(array $v14Row): array
    {
        return [
            'hash'            => $this->generateVisitorHash($v14Row),
            'created_at'      => $v14Row['last_counter'] ?? current_time('mysql'),
            'ip'              => $v14Row['ip'] ?? null,
            'country_code'    => $v14Row['location'] ?? null,
            'city_name'       => $v14Row['city'] ?? null,
            'region'          => $v14Row['region'] ?? null,
            'browser_name'    => $v14Row['browser'] ?? null,
            'browser_version' => $v14Row['version'] ?? null,
            'os_name'         => $v14Row['platform'] ?? null,
            'device_type'     => $v14Row['device'] ?? null,
            'user_id'         => $v14Row['user_id'] ?? null,
            'referrer_url'    => $v14Row['referred'] ?? null,
            'total_views'     => $v14Row['hits'] ?? 1,
        ];
    }

    /**
     * Generate visitor hash from v14 data.
     *
     * @param array $v14Row V14 visitor row
     * @return string Hash
     */
    private function generateVisitorHash(array $v14Row): string
    {
        $components = [
            $v14Row['ip'] ?? '',
            $v14Row['browser'] ?? '',
            $v14Row['platform'] ?? '',
        ];

        return hash('sha256', implode('|', $components));
    }

    /**
     * Build session data from transformed visitor data.
     *
     * @param array  $transformed Transformed visitor data
     * @param int    $visitorId   New visitor ID
     * @param object $v14Visitor  Original v14 visitor record
     * @return array Session data for insert
     */
    private function buildSessionData(array $transformed, int $visitorId, object $v14Visitor): array
    {
        $session = [
            'visitor_id'   => $visitorId,
            'ip'           => $transformed['ip'] ?? null,
            'total_views'  => $transformed['total_views'] ?? 1,
            'started_at'   => $transformed['created_at'] ?? current_time('mysql'),
            'user_id'      => $transformed['user_id'] ?? null,
        ];

        // Resolve country
        if (!empty($transformed['country_code'])) {
            $countryId = $this->resolvers['countries']->resolve([
                'code' => $transformed['country_code'],
            ]);
            $session['country_id'] = $countryId;

            // Resolve city (requires country_id)
            if ($countryId && !empty($transformed['city_name'])) {
                $cityId = $this->resolvers['cities']->resolve([
                    'city_name'   => $transformed['city_name'],
                    'country_id'  => $countryId,
                    'region_name' => $transformed['region'] ?? '',
                ]);
                $session['city_id'] = $cityId;
            }
        }

        // Resolve device type
        if (!empty($transformed['device_type'])) {
            $deviceTypeId = $this->resolvers['device_types']->resolve([
                'name' => $transformed['device_type'],
            ]);
            $session['device_type_id'] = $deviceTypeId;
        }

        // Resolve browser
        if (!empty($transformed['browser_name'])) {
            $browserId = $this->resolvers['device_browsers']->resolve([
                'name' => $transformed['browser_name'],
            ]);
            $session['device_browser_id'] = $browserId;

            // Resolve browser version
            if ($browserId && !empty($transformed['browser_version'])) {
                $versionId = $this->resolvers['device_browser_versions']->resolve([
                    'browser_id' => $browserId,
                    'version'    => $transformed['browser_version'],
                ]);
                $session['device_browser_version_id'] = $versionId;
            }
        }

        // Resolve OS
        if (!empty($transformed['os_name'])) {
            $osId = $this->resolvers['device_oss']->resolve([
                'name' => $transformed['os_name'],
            ]);
            $session['device_os_id'] = $osId;
        }

        // Resolve referrer
        if (!empty($transformed['referrer_url'])) {
            $parsed  = parse_url($transformed['referrer_url']);
            $domain  = $parsed['host'] ?? '';
            $channel = $this->determineReferrerChannel($domain);

            $referrerId = $this->resolvers['referrers']->resolve([
                'channel' => $channel,
                'domain'  => $domain,
            ]);
            $session['referrer_id'] = $referrerId;
        }

        return $session;
    }

    /**
     * Determine referrer channel from domain.
     *
     * @param string $domain Referrer domain
     * @return string Channel name
     */
    private function determineReferrerChannel(string $domain): string
    {
        if (empty($domain)) {
            return 'direct';
        }

        $searchEngines = ['google', 'bing', 'yahoo', 'duckduckgo', 'baidu', 'yandex'];
        $socialNetworks = ['facebook', 'twitter', 'instagram', 'linkedin', 'pinterest', 'youtube', 'tiktok'];

        $domainLower = strtolower($domain);

        foreach ($searchEngines as $engine) {
            if (strpos($domainLower, $engine) !== false) {
                return 'search';
            }
        }

        foreach ($socialNetworks as $social) {
            if (strpos($domainLower, $social) !== false) {
                return 'social';
            }
        }

        return 'referral';
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
     * Check if migration is needed.
     *
     * @return bool True if v14 tables have data to migrate
     */
    public function isMigrationNeeded(): bool
    {
        // Check if v14 visitor table has data
        $visitorTable = DatabaseSchema::table('visitor');

        if (!DatabaseSchema::tableExists($visitorTable)) {
            return false;
        }

        $count = DatabaseSchema::getRowCount('visitor');

        return $count > 0;
    }

    /**
     * Get migration progress info.
     *
     * @return array Progress information
     */
    public function getMigrationInfo(): array
    {
        return [
            'source_tables' => [
                'visitor'               => DatabaseSchema::getRowCount('visitor'),
                'pages'                 => DatabaseSchema::getRowCount('pages'),
                'visitor_relationships' => DatabaseSchema::getRowCount('visitor_relationships'),
            ],
            'migration_needed' => $this->isMigrationNeeded(),
        ];
    }
}
