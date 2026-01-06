<?php

namespace WP_Statistics\Service\ImportExport\Adapters;

use WP_Statistics\Service\Database\DatabaseSchema;
use WP_Statistics\Service\ImportExport\Resolvers\ResourceResolver;
use WP_Statistics\Service\ImportExport\Resolvers\ResourceUriResolver;
use WP_Statistics\Utils\Query;

/**
 * Google Analytics 4 Import Adapter.
 *
 * Imports GA4 CSV exports into WP Statistics summary tables.
 * GA4 exports aggregate data (daily totals), not individual sessions.
 *
 * Expected CSV columns from GA4 Export:
 * - Date (YYYYMMDD format)
 * - Page path (e.g., /about-us/)
 * - Sessions
 * - Total users / Active users
 * - Pageviews / Views
 * - Average session duration
 * - Bounce rate (%)
 *
 * @since 15.0.0
 */
class GoogleAnalytics4Adapter extends AbstractImportAdapter
{
    /**
     * Adapter identifier.
     *
     * @var string
     */
    protected $name = 'google_analytics_4';

    /**
     * Human-readable label.
     *
     * @var string
     */
    protected $label = 'Google Analytics 4';

    /**
     * Supported file extensions.
     *
     * @var array
     */
    protected $extensions = ['csv'];

    /**
     * Required columns in the source file.
     *
     * @var array
     */
    protected $requiredColumns = ['Date'];

    /**
     * Optional columns in the source file.
     *
     * @var array
     */
    protected $optionalColumns = [
        'Page path',
        'Page path + query string',
        'Sessions',
        'Total users',
        'Active users',
        'Views',
        'Pageviews',
        'Screen pageviews',
        'Average session duration',
        'Avg. session duration',
        'Bounce rate',
        'Engagement rate',
        'Country',
        'City',
        'Device category',
        'Browser',
        'Operating system',
        'Source',
        'Medium',
        'Source / Medium',
    ];

    /**
     * Target tables for import.
     *
     * @var array
     */
    protected $targetTables = ['summary', 'summary_totals'];

    /**
     * Whether this adapter imports aggregate data.
     *
     * @var bool
     */
    protected $isAggregate = true;

    /**
     * Field mapping from GA4 to WP Statistics.
     *
     * @var array
     */
    protected $fieldMapping = [
        'Date'                        => 'date',
        'Page path'                   => 'uri',
        'Page path + query string'    => 'uri',
        'Sessions'                    => 'sessions',
        'Total users'                 => 'visitors',
        'Active users'                => 'visitors',
        'Views'                       => 'views',
        'Pageviews'                   => 'views',
        'Screen pageviews'            => 'views',
        'Average session duration'    => 'avg_duration',
        'Avg. session duration'       => 'avg_duration',
        'Bounce rate'                 => 'bounce_rate',
        'Engagement rate'             => 'engagement_rate',
    ];

    /**
     * Resource resolver.
     *
     * @var ResourceResolver|null
     */
    private $resourceResolver = null;

    /**
     * Resource URI resolver.
     *
     * @var ResourceUriResolver|null
     */
    private $resourceUriResolver = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->label = __('Google Analytics 4', 'wp-statistics');
    }

    /**
     * Validate the source file.
     *
     * @param string $filePath Path to file
     * @param array  $headers  Column headers from file
     * @return bool
     */
    public function validateSource(string $filePath, array $headers): bool
    {
        // Must have Date column
        $hasDate = false;

        foreach ($headers as $header) {
            if (stripos($header, 'date') !== false) {
                $hasDate = true;
                break;
            }
        }

        if (!$hasDate) {
            return false;
        }

        // Should have at least one metric column
        $metricColumns = ['Sessions', 'Total users', 'Active users', 'Views', 'Pageviews'];
        $hasMetric     = false;

        foreach ($metricColumns as $metric) {
            if (in_array($metric, $headers, true)) {
                $hasMetric = true;
                break;
            }
        }

        return $hasMetric;
    }

    /**
     * Transform a single row from GA4 format to WP Statistics schema.
     *
     * @param array $sourceRow   Raw row data from CSV
     * @param array $fieldMapping Custom field mapping (optional)
     * @return array Transformed data for summary table
     */
    public function transformRow(array $sourceRow, array $fieldMapping = []): array
    {
        $mapping = !empty($fieldMapping) ? $fieldMapping : $this->fieldMapping;

        $transformed = [
            'date'           => null,
            'uri'            => '/',
            'visitors'       => 0,
            'sessions'       => 0,
            'views'          => 0,
            'total_duration' => 0,
            'bounces'        => 0,
        ];

        // Process each source field
        foreach ($sourceRow as $sourceField => $value) {
            $targetField = $mapping[$sourceField] ?? null;

            if ($targetField === null) {
                continue;
            }

            switch ($targetField) {
                case 'date':
                    $transformed['date'] = $this->normalizeGa4Date($value);
                    break;

                case 'uri':
                    $transformed['uri'] = $this->normalizeUri($value);
                    break;

                case 'visitors':
                    $transformed['visitors'] = $this->normalizeInt($value);
                    break;

                case 'sessions':
                    $transformed['sessions'] = $this->normalizeInt($value);
                    break;

                case 'views':
                    $transformed['views'] = $this->normalizeInt($value);
                    break;

                case 'avg_duration':
                    // Convert avg duration to total duration
                    $avgSeconds = $this->parseDuration($value);
                    $sessions   = $transformed['sessions'] ?: 1;
                    $transformed['total_duration'] = $avgSeconds * $sessions;
                    break;

                case 'bounce_rate':
                    // Calculate bounces from bounce rate and sessions
                    $bounceRate = $this->parsePercentage($value);
                    $sessions   = $transformed['sessions'] ?: 0;
                    $transformed['bounces'] = (int)round($sessions * $bounceRate);
                    break;

                case 'engagement_rate':
                    // Calculate bounces from engagement rate (inverse)
                    // Bounce rate = 100% - Engagement rate
                    $engagementRate = $this->parsePercentage($value);
                    $bounceRate     = 1 - $engagementRate;
                    $sessions       = $transformed['sessions'] ?: 0;
                    $transformed['bounces'] = (int)round($sessions * $bounceRate);
                    break;
            }
        }

        return $transformed;
    }

    /**
     * Estimate record count from CSV file.
     *
     * @param string $filePath Path to CSV file
     * @return int Estimated row count
     */
    public function estimateRecordCount(string $filePath): int
    {
        $count  = 0;
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            return 0;
        }

        // Skip header
        fgets($handle);

        while (!feof($handle)) {
            $line = fgets($handle);
            if ($line !== false && trim($line) !== '') {
                $count++;
            }
        }

        fclose($handle);

        return $count;
    }

    /**
     * Import GA4 data from CSV file.
     *
     * @param string        $filePath          Path to CSV file
     * @param callable|null $progressCallback  Progress callback(processed, total)
     * @param bool          $skipExisting      Skip dates that already have data
     * @return array Import results
     */
    public function importFromFile(string $filePath, ?callable $progressCallback = null, bool $skipExisting = true): array
    {
        $parser = new \WP_Statistics\Service\ImportExport\Parsers\CsvParser();
        $parser->open($filePath);

        $results = [
            'imported'       => 0,
            'skipped'        => 0,
            'errors'         => [],
            'dates_imported' => [],
        ];

        // Initialize resolvers
        $this->initializeResolvers();

        $totalRows    = $parser->getTotalRows();
        $processed    = 0;
        $aggregated   = []; // Aggregate by date + uri

        // First pass: read and aggregate all data
        while ($parser->hasMore()) {
            $batch = $parser->readBatch(100);

            foreach ($batch as $row) {
                $transformed = $this->transformRow($row);

                if (empty($transformed['date'])) {
                    $results['errors'][] = [
                        'row'     => $processed + 1,
                        'message' => 'Invalid or missing date',
                    ];
                    $processed++;
                    continue;
                }

                $date = $transformed['date'];
                $uri  = $transformed['uri'];
                $key  = $date . '|' . $uri;

                // Aggregate by date + uri
                if (!isset($aggregated[$key])) {
                    $aggregated[$key] = [
                        'date'           => $date,
                        'uri'            => $uri,
                        'visitors'       => 0,
                        'sessions'       => 0,
                        'views'          => 0,
                        'total_duration' => 0,
                        'bounces'        => 0,
                    ];
                }

                $aggregated[$key]['visitors']       += $transformed['visitors'];
                $aggregated[$key]['sessions']       += $transformed['sessions'];
                $aggregated[$key]['views']          += $transformed['views'];
                $aggregated[$key]['total_duration'] += $transformed['total_duration'];
                $aggregated[$key]['bounces']        += $transformed['bounces'];

                $processed++;

                if ($progressCallback) {
                    $progressCallback($processed, $totalRows);
                }
            }
        }

        $parser->close();

        // Second pass: insert aggregated data
        global $wpdb;

        foreach ($aggregated as $key => $data) {
            // Get or create resource_uri
            $resourceUriId = $this->resolveResourceUri($data['uri']);

            if (!$resourceUriId) {
                $results['errors'][] = [
                    'row'     => $key,
                    'message' => 'Could not resolve resource URI',
                ];
                continue;
            }

            // Check if record already exists
            if ($skipExisting) {
                $exists = Query::select('ID')
                    ->from('summary')
                    ->where('date', '=', $data['date'])
                    ->where('resource_uri_id', '=', $resourceUriId)
                    ->getVar();

                if ($exists) {
                    $results['skipped']++;
                    continue;
                }
            }

            // Insert into summary table
            $insertData = [
                'date'            => $data['date'],
                'resource_uri_id' => $resourceUriId,
                'visitors'        => $data['visitors'],
                'sessions'        => $data['sessions'],
                'views'           => $data['views'],
                'total_duration'  => $data['total_duration'],
                'bounces'         => $data['bounces'],
            ];

            $inserted = $wpdb->insert(
                DatabaseSchema::table('summary'),
                $insertData
            );

            if ($inserted) {
                $results['imported']++;
                $results['dates_imported'][$data['date']] = true;
            } else {
                $results['errors'][] = [
                    'row'     => $key,
                    'message' => $wpdb->last_error,
                ];
            }
        }

        // Update summary_totals for imported dates
        $this->updateSummaryTotals(array_keys($results['dates_imported']));

        $results['dates_imported'] = array_keys($results['dates_imported']);

        return $results;
    }

    /**
     * Initialize the adapter for import.
     *
     * @return void
     */
    public function initialize(): void
    {
        $this->initializeResolvers();
    }

    /**
     * Initialize resolvers.
     *
     * @return void
     */
    private function initializeResolvers(): void
    {
        $this->resourceResolver    = new ResourceResolver();
        $this->resourceUriResolver = new ResourceUriResolver();

        $this->resourceResolver->warmCache(1000);
        $this->resourceUriResolver->warmCache(5000);
    }

    /**
     * Import a single row of GA4 data.
     *
     * @param array $sourceRow Raw row data from CSV
     * @return bool|null True if imported, false if skipped
     * @throws \Exception On import error
     */
    public function importRow(array $sourceRow)
    {
        global $wpdb;

        // Initialize resolvers if not already done
        if ($this->resourceResolver === null) {
            $this->initializeResolvers();
        }

        $transformed = $this->transformRow($sourceRow);

        if (empty($transformed['date'])) {
            return false; // Skip rows without date
        }

        // Get or create resource_uri
        $resourceUriId = $this->resolveResourceUri($transformed['uri']);

        if (!$resourceUriId) {
            return false;
        }

        // Check if record already exists
        $exists = Query::select('ID')
            ->from('summary')
            ->where('date', '=', $transformed['date'])
            ->where('resource_uri_id', '=', $resourceUriId)
            ->getVar();

        if ($exists) {
            return false; // Skip existing
        }

        // Insert into summary table
        $inserted = $wpdb->insert(
            DatabaseSchema::table('summary'),
            [
                'date'            => $transformed['date'],
                'resource_uri_id' => $resourceUriId,
                'visitors'        => $transformed['visitors'],
                'sessions'        => $transformed['sessions'],
                'views'           => $transformed['views'],
                'total_duration'  => $transformed['total_duration'],
                'bounces'         => $transformed['bounces'],
            ]
        );

        if (!$inserted) {
            throw new \Exception($wpdb->last_error ?: 'Failed to insert summary record');
        }

        return true;
    }

    /**
     * Finalize the adapter after import.
     *
     * @return void
     */
    public function finalize(): void
    {
        // Clear resolver caches
        if ($this->resourceResolver) {
            $this->resourceResolver->clearCache();
        }
        if ($this->resourceUriResolver) {
            $this->resourceUriResolver->clearCache();
        }
    }

    /**
     * Resolve or create resource_uri for a page path.
     *
     * @param string $uri Page path
     * @return int|null Resource URI ID
     */
    private function resolveResourceUri(string $uri): ?int
    {
        // First try to find existing
        $existing = $this->resourceUriResolver->resolveByUri($uri);

        if ($existing) {
            return $existing['id'];
        }

        // Create generic resource for imported GA4 data
        $resourceId = $this->resourceResolver->resolve([
            'resource_type' => 'ga4_import',
            'resource_id'   => crc32($uri), // Generate stable ID from URI
            'cached_title'  => $uri,
        ]);

        if (!$resourceId) {
            return null;
        }

        // Create resource_uri
        return $this->resourceUriResolver->resolve([
            'resource_id' => $resourceId,
            'uri'         => $uri,
        ]);
    }

    /**
     * Update summary_totals for given dates.
     *
     * @param array $dates Array of dates (Y-m-d format)
     * @return void
     */
    private function updateSummaryTotals(array $dates): void
    {
        global $wpdb;

        foreach ($dates as $date) {
            // Calculate totals from summary table
            $totals = Query::select([
                'SUM(visitors) as visitors',
                'SUM(sessions) as sessions',
                'SUM(views) as views',
                'SUM(total_duration) as total_duration',
                'SUM(bounces) as bounces',
            ])
                ->from('summary')
                ->where('date', '=', $date)
                ->getRow();

            if (!$totals) {
                continue;
            }

            // Check if summary_totals exists
            $existing = Query::select('ID')
                ->from('summary_totals')
                ->where('date', '=', $date)
                ->getVar();

            $totalData = [
                'date'           => $date,
                'visitors'       => (int)$totals->visitors,
                'sessions'       => (int)$totals->sessions,
                'views'          => (int)$totals->views,
                'total_duration' => (int)$totals->total_duration,
                'bounces'        => (int)$totals->bounces,
            ];

            if ($existing) {
                $wpdb->update(
                    DatabaseSchema::table('summary_totals'),
                    $totalData,
                    ['ID' => $existing]
                );
            } else {
                $wpdb->insert(
                    DatabaseSchema::table('summary_totals'),
                    $totalData
                );
            }
        }
    }

    /**
     * Normalize GA4 date format (YYYYMMDD) to standard format.
     *
     * @param string $date GA4 date string
     * @return string|null Normalized date (Y-m-d) or null
     */
    private function normalizeGa4Date(string $date): ?string
    {
        $date = trim($date);

        // GA4 format: YYYYMMDD
        if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $date, $matches)) {
            return "{$matches[1]}-{$matches[2]}-{$matches[3]}";
        }

        // Try standard date parsing
        return $this->normalizeDate($date);
    }

    /**
     * Normalize URI from GA4 page path.
     *
     * @param string $uri Page path
     * @return string Normalized URI
     */
    private function normalizeUri(string $uri): string
    {
        $uri = trim($uri);

        if (empty($uri) || $uri === '(not set)') {
            return '/';
        }

        // Ensure leading slash
        if (strpos($uri, '/') !== 0) {
            $uri = '/' . $uri;
        }

        return $uri;
    }

    /**
     * Parse duration string to seconds.
     *
     * Handles formats: "123", "1:23", "1:23:45", "123.45s"
     *
     * @param string $duration Duration string
     * @return int Duration in seconds
     */
    private function parseDuration(string $duration): int
    {
        $duration = trim($duration);

        if (empty($duration)) {
            return 0;
        }

        // Plain numeric (seconds)
        if (is_numeric($duration)) {
            return (int)round((float)$duration);
        }

        // Remove 's' suffix if present
        $duration = rtrim($duration, 's');

        // Format: HH:MM:SS or MM:SS
        if (strpos($duration, ':') !== false) {
            $parts = explode(':', $duration);

            if (count($parts) === 2) {
                return (int)$parts[0] * 60 + (int)$parts[1];
            }

            if (count($parts) === 3) {
                return (int)$parts[0] * 3600 + (int)$parts[1] * 60 + (int)$parts[2];
            }
        }

        return (int)$duration;
    }

    /**
     * Parse percentage string to decimal.
     *
     * @param string $percentage Percentage string (e.g., "45.5%", "45.5")
     * @return float Decimal value (0-1)
     */
    private function parsePercentage(string $percentage): float
    {
        $percentage = trim($percentage);
        $percentage = rtrim($percentage, '%');

        if (empty($percentage) || !is_numeric($percentage)) {
            return 0.0;
        }

        $value = (float)$percentage;

        // If value > 1, assume it's a percentage (e.g., 45.5 = 45.5%)
        if ($value > 1) {
            $value = $value / 100;
        }

        return max(0, min(1, $value));
    }
}
