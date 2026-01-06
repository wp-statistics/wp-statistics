<?php

namespace WP_Statistics\Service\ImportExport\Adapters;

use WP_Statistics\Service\Database\DatabaseSchema;
use WP_Statistics\Service\ImportExport\Resolvers\ResourceResolver;
use WP_Statistics\Service\ImportExport\Resolvers\ResourceUriResolver;
use WP_Statistics\Utils\Query;

/**
 * Plausible Analytics Import Adapter.
 *
 * Imports Plausible CSV exports into WP Statistics summary tables.
 * Plausible exports aggregate data (daily totals), not individual sessions.
 *
 * Expected CSV columns from Plausible Export:
 * - date (YYYY-MM-DD format)
 * - page (e.g., /about-us/)
 * - visitors (unique visitors)
 * - visits (sessions)
 * - pageviews
 * - bounce_rate (%)
 * - visit_duration (seconds)
 *
 * @since 15.0.0
 */
class PlausibleAdapter extends AbstractImportAdapter
{
    /**
     * Adapter identifier.
     *
     * @var string
     */
    protected $name = 'plausible';

    /**
     * Human-readable label.
     *
     * @var string
     */
    protected $label = 'Plausible Analytics';

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
    protected $requiredColumns = ['date'];

    /**
     * Optional columns in the source file.
     *
     * @var array
     */
    protected $optionalColumns = [
        'page',
        'visitors',
        'visits',
        'pageviews',
        'bounce_rate',
        'visit_duration',
        'time_on_page',
        'country',
        'region',
        'city',
        'device',
        'browser',
        'os',
        'source',
        'referrer',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'entry_page',
        'exit_page',
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
     * Field mapping from Plausible to WP Statistics.
     *
     * @var array
     */
    protected $fieldMapping = [
        'date'           => 'date',
        'page'           => 'uri',
        'visitors'       => 'visitors',
        'visits'         => 'sessions',
        'pageviews'      => 'views',
        'bounce_rate'    => 'bounce_rate',
        'visit_duration' => 'avg_duration',
        'time_on_page'   => 'avg_duration',
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
        $this->label = __('Plausible Analytics', 'wp-statistics');
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
        // Normalize headers to lowercase for comparison
        $normalizedHeaders = array_map('strtolower', $headers);

        // Must have date column
        if (!in_array('date', $normalizedHeaders, true)) {
            return false;
        }

        // Should have at least one metric column
        $metricColumns = ['visitors', 'visits', 'pageviews'];
        $hasMetric     = false;

        foreach ($metricColumns as $metric) {
            if (in_array($metric, $normalizedHeaders, true)) {
                $hasMetric = true;
                break;
            }
        }

        return $hasMetric;
    }

    /**
     * Transform a single row from Plausible format to WP Statistics schema.
     *
     * @param array $sourceRow   Raw row data from CSV
     * @param array $fieldMapping Custom field mapping (optional)
     * @return array Transformed data for summary table
     */
    public function transformRow(array $sourceRow, array $fieldMapping = []): array
    {
        $mapping = !empty($fieldMapping) ? $fieldMapping : $this->fieldMapping;

        // Normalize source row keys to lowercase
        $normalizedRow = [];
        foreach ($sourceRow as $key => $value) {
            $normalizedRow[strtolower($key)] = $value;
        }

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
        foreach ($normalizedRow as $sourceField => $value) {
            $targetField = $mapping[$sourceField] ?? null;

            if ($targetField === null) {
                continue;
            }

            switch ($targetField) {
                case 'date':
                    $transformed['date'] = $this->normalizeDate($value);
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
     * Import a single row of Plausible data.
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

        // Create generic resource for imported Plausible data
        $resourceId = $this->resourceResolver->resolve([
            'resource_type' => 'plausible_import',
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
     * Normalize URI from Plausible page path.
     *
     * @param string $uri Page path
     * @return string Normalized URI
     */
    private function normalizeUri(string $uri): string
    {
        $uri = trim($uri);

        if (empty($uri) || $uri === '(none)' || $uri === '(not set)') {
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
     * Handles formats: "123", "1:23", "1:23:45", "123s"
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
     * @param string $percentage Percentage string (e.g., "45.5%", "45.5", "0.455")
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
