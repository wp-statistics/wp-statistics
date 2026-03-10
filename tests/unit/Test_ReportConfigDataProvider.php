<?php

namespace WP_Statistics\Tests\ReportConfig;

use WP_UnitTestCase;
use WP_Statistics\Service\Admin\ReactApp\Providers\ReportConfigDataProvider;

/**
 * Test ReportConfigDataProvider structural integrity.
 *
 * Validates that ALL report configs returned by getData() are well-formed,
 * catching misconfigurations like wrong field names, missing required fields, etc.
 */
class Test_ReportConfigDataProvider extends WP_UnitTestCase
{
    /**
     * Known valid column types across table, overview, and detail configs.
     */
    private static $validColumnTypes = [
        'text',
        'numeric',
        'page-link',
        'percentage',
        'duration',
        'location',
        'referrer',
        'author',
        'term',
        'uri',
        'entry-page',
        'visitor-info',
        'last-visit',
        'visitor-status',
        'computed-ratio',
        'source-category',
        'date',
        'journey',
    ];

    private static $validColumnPriorities = ['primary', 'secondary', 'hidden'];

    private static $validQueryFormats = ['table', 'chart', 'flat'];

    /**
     * @var ReportConfigDataProvider
     */
    private $provider;

    /**
     * @var array All configs keyed by report slug.
     */
    private $configs;

    public function setUp(): void
    {
        parent::setUp();

        $this->provider = new ReportConfigDataProvider();
        $this->configs  = $this->provider->getData();

        // Clean up any filters so premium additions don't interfere
        remove_all_filters('wp_statistics_report_definitions');
    }

    public function tearDown(): void
    {
        remove_all_filters('wp_statistics_report_definitions');
        parent::tearDown();
    }

    // ------------------------------------------------------------------
    // Top-level structure
    // ------------------------------------------------------------------

    /**
     * Test getData returns a non-empty array keyed by report slug.
     */
    public function test_get_data_returns_non_empty_array()
    {
        $this->assertIsArray($this->configs);
        $this->assertNotEmpty($this->configs);
    }

    /**
     * Test every config has a title and filterGroup.
     */
    public function test_all_configs_have_required_top_level_fields()
    {
        foreach ($this->configs as $slug => $config) {
            $this->assertArrayHasKey('title', $config, "Report '{$slug}' missing 'title'");
            $this->assertNotEmpty($config['title'], "Report '{$slug}' has empty 'title'");

            $this->assertArrayHasKey('filterGroup', $config, "Report '{$slug}' missing 'filterGroup'");
            $this->assertNotEmpty($config['filterGroup'], "Report '{$slug}' has empty 'filterGroup'");
        }
    }

    // ------------------------------------------------------------------
    // Table report validation
    // ------------------------------------------------------------------

    /**
     * Get table reports — those without `type` or with `type === 'table'`.
     */
    private function getTableReports()
    {
        return array_filter($this->configs, function ($config) {
            return !isset($config['type']) || $config['type'] === 'table';
        });
    }

    /**
     * Test table reports have required fields.
     */
    public function test_table_reports_have_required_fields()
    {
        foreach ($this->getTableReports() as $slug => $config) {
            $this->assertArrayHasKey('context', $config, "Table report '{$slug}' missing 'context'");
            $this->assertNotEmpty($config['context'], "Table report '{$slug}' has empty 'context'");

            $this->assertArrayHasKey('columns', $config, "Table report '{$slug}' missing 'columns'");
            $this->assertIsArray($config['columns'], "Table report '{$slug}' 'columns' is not an array");
            $this->assertNotEmpty($config['columns'], "Table report '{$slug}' has empty 'columns'");

            // Must have a data source
            $this->assertArrayHasKey('dataSource', $config, "Table report '{$slug}' missing 'dataSource'");
            $this->assertIsArray($config['dataSource'], "Table report '{$slug}' 'dataSource' is not an array");
        }
    }

    /**
     * Test context values are unique across table reports.
     */
    public function test_table_report_contexts_are_unique()
    {
        $contexts = [];
        foreach ($this->getTableReports() as $slug => $config) {
            if (!isset($config['context'])) {
                continue;
            }
            $context = $config['context'];
            $this->assertArrayNotHasKey(
                $context,
                $contexts,
                "Duplicate context '{$context}' found in '{$slug}' and '" . ($contexts[$context] ?? '') . "'"
            );
            $contexts[$context] = $slug;
        }
    }

    // ------------------------------------------------------------------
    // Column validation (table reports)
    // ------------------------------------------------------------------

    /**
     * Test each column in table reports has required fields and valid types.
     */
    public function test_table_report_columns_have_required_fields()
    {
        foreach ($this->getTableReports() as $slug => $config) {
            foreach ($config['columns'] as $i => $column) {
                $colId = $column['key'] ?? "index:{$i}";

                $this->assertArrayHasKey('key', $column, "Column {$colId} in '{$slug}' missing 'key'");
                $this->assertArrayHasKey('title', $column, "Column {$colId} in '{$slug}' missing 'title'");
                $this->assertArrayHasKey('type', $column, "Column {$colId} in '{$slug}' missing 'type'");
                $this->assertArrayHasKey('priority', $column, "Column {$colId} in '{$slug}' missing 'priority'");

                $this->assertContains(
                    $column['type'],
                    self::$validColumnTypes,
                    "Column '{$colId}' in '{$slug}' has invalid type '{$column['type']}'"
                );

                $this->assertContains(
                    $column['priority'],
                    self::$validColumnPriorities,
                    "Column '{$colId}' in '{$slug}' has invalid priority '{$column['priority']}'"
                );
            }
        }
    }

    /**
     * Test column keys are unique within each table report.
     */
    public function test_table_report_column_keys_are_unique()
    {
        foreach ($this->getTableReports() as $slug => $config) {
            $keys = array_column($config['columns'], 'key');
            $this->assertCount(
                count(array_unique($keys)),
                $keys,
                "Report '{$slug}' has duplicate column keys"
            );
        }
    }

    /**
     * Test dataField is a non-empty string when present on columns.
     */
    public function test_column_datafield_is_valid_when_present()
    {
        foreach ($this->getTableReports() as $slug => $config) {
            foreach ($config['columns'] as $column) {
                $colId = $column['key'];
                if (isset($column['dataField'])) {
                    $this->assertIsString($column['dataField'], "Column '{$colId}' in '{$slug}' dataField is not a string");
                    $this->assertNotEmpty($column['dataField'], "Column '{$colId}' in '{$slug}' dataField is empty");
                }
            }
        }
    }

    /**
     * Test comparable columns have a previousKey.
     */
    public function test_comparable_columns_have_previous_key()
    {
        foreach ($this->getTableReports() as $slug => $config) {
            foreach ($config['columns'] as $column) {
                $colId = $column['key'];
                if (!empty($column['comparable']) && $column['type'] !== 'computed-ratio') {
                    $this->assertArrayHasKey(
                        'previousKey',
                        $column,
                        "Comparable column '{$colId}' in '{$slug}' missing 'previousKey'"
                    );
                    $this->assertNotEmpty(
                        $column['previousKey'],
                        "Comparable column '{$colId}' in '{$slug}' has empty 'previousKey'"
                    );
                }
            }
        }
    }

    // ------------------------------------------------------------------
    // DataSource validation (table reports with batch queries)
    // ------------------------------------------------------------------

    /**
     * Test table reports with batch queries have valid queryId and query structure.
     */
    public function test_batch_query_datasource_structure()
    {
        foreach ($this->getTableReports() as $slug => $config) {
            $ds = $config['dataSource'];

            if (!isset($ds['queries'])) {
                continue;
            }

            // queryId must reference an existing query
            if (isset($ds['queryId'])) {
                $queryIds = array_column($ds['queries'], 'id');
                $this->assertContains(
                    $ds['queryId'],
                    $queryIds,
                    "Report '{$slug}' dataSource.queryId '{$ds['queryId']}' not found in queries"
                );
            }

            // Validate each query
            foreach ($ds['queries'] as $qi => $query) {
                $qId = $query['id'] ?? "index:{$qi}";

                $this->assertArrayHasKey('id', $query, "Query {$qi} in '{$slug}' missing 'id'");

                // Chart-only queries (e.g., { id: 'chart', chart: 'search_engine_chart' }) skip normal validation
                if (isset($query['chart'])) {
                    continue;
                }

                $this->assertArrayHasKey('sources', $query, "Query '{$qId}' in '{$slug}' missing 'sources'");
                $this->assertIsArray($query['sources'], "Query '{$qId}' in '{$slug}' 'sources' is not an array");
                $this->assertNotEmpty($query['sources'], "Query '{$qId}' in '{$slug}' has empty 'sources'");

                $this->assertArrayHasKey('format', $query, "Query '{$qId}' in '{$slug}' missing 'format'");
                $this->assertContains(
                    $query['format'],
                    self::$validQueryFormats,
                    "Query '{$qId}' in '{$slug}' has invalid format '{$query['format']}'"
                );
            }
        }
    }

    /**
     * Test table reports with simple data sources have required fields.
     */
    public function test_simple_datasource_structure()
    {
        foreach ($this->getTableReports() as $slug => $config) {
            $ds = $config['dataSource'];

            if (isset($ds['queries'])) {
                continue; // Skip batch query configs
            }

            $this->assertArrayHasKey('sources', $ds, "Report '{$slug}' simple dataSource missing 'sources'");
            $this->assertIsArray($ds['sources'], "Report '{$slug}' simple dataSource 'sources' is not an array");
            $this->assertNotEmpty($ds['sources'], "Report '{$slug}' simple dataSource has empty 'sources'");

            $this->assertArrayHasKey('group_by', $ds, "Report '{$slug}' simple dataSource missing 'group_by'");
            $this->assertIsArray($ds['group_by'], "Report '{$slug}' simple dataSource 'group_by' is not an array");
            $this->assertNotEmpty($ds['group_by'], "Report '{$slug}' simple dataSource has empty 'group_by'");
        }
    }

    // ------------------------------------------------------------------
    // columnMapping validation
    // ------------------------------------------------------------------

    /**
     * Test columnMapping keys correspond to column keys when present.
     */
    public function test_column_mapping_keys_match_column_keys()
    {
        foreach ($this->getTableReports() as $slug => $config) {
            $ds = $config['dataSource'];
            if (!isset($ds['columnMapping'])) {
                continue;
            }

            $columnKeys = array_column($config['columns'], 'key');

            foreach (array_keys($ds['columnMapping']) as $mappingKey) {
                $this->assertContains(
                    $mappingKey,
                    $columnKeys,
                    "Report '{$slug}' columnMapping key '{$mappingKey}' does not match any column key"
                );
            }
        }
    }

    // ------------------------------------------------------------------
    // columnConfig validation
    // ------------------------------------------------------------------

    /**
     * Test columnConfig.baseColumns is a non-empty array when present.
     */
    public function test_column_config_base_columns()
    {
        foreach ($this->getTableReports() as $slug => $config) {
            if (!isset($config['columnConfig'])) {
                continue;
            }

            $this->assertArrayHasKey(
                'baseColumns',
                $config['columnConfig'],
                "Report '{$slug}' columnConfig missing 'baseColumns'"
            );
            $this->assertIsArray(
                $config['columnConfig']['baseColumns'],
                "Report '{$slug}' columnConfig.baseColumns is not an array"
            );
            $this->assertNotEmpty(
                $config['columnConfig']['baseColumns'],
                "Report '{$slug}' columnConfig.baseColumns is empty"
            );
        }
    }

    /**
     * Test columnConfig.columnDependencies keys correspond to column keys.
     */
    public function test_column_dependencies_keys_match_column_keys()
    {
        foreach ($this->getTableReports() as $slug => $config) {
            if (!isset($config['columnConfig']['columnDependencies'])) {
                continue;
            }

            $columnKeys = array_column($config['columns'], 'key');

            foreach (array_keys($config['columnConfig']['columnDependencies']) as $depKey) {
                $this->assertContains(
                    $depKey,
                    $columnKeys,
                    "Report '{$slug}' columnDependencies key '{$depKey}' does not match any column key"
                );
            }
        }
    }

    /**
     * Test defaultApiColumns contain all baseColumns.
     */
    public function test_default_api_columns_contain_base_columns()
    {
        foreach ($this->getTableReports() as $slug => $config) {
            if (!isset($config['columnConfig']['baseColumns']) || !isset($config['defaultApiColumns'])) {
                continue;
            }

            foreach ($config['columnConfig']['baseColumns'] as $baseCol) {
                $this->assertContains(
                    $baseCol,
                    $config['defaultApiColumns'],
                    "Report '{$slug}' defaultApiColumns missing baseColumn '{$baseCol}'"
                );
            }
        }
    }

    // ------------------------------------------------------------------
    // Chart config validation (table reports)
    // ------------------------------------------------------------------

    /**
     * Test chart.queryId matches a query id in dataSource.queries when present.
     */
    public function test_chart_query_id_matches_query()
    {
        foreach ($this->getTableReports() as $slug => $config) {
            if (!isset($config['chart'])) {
                continue;
            }

            $this->assertArrayHasKey('queryId', $config['chart'], "Report '{$slug}' chart missing 'queryId'");

            if (isset($config['dataSource']['queries'])) {
                $queryIds = array_column($config['dataSource']['queries'], 'id');
                $this->assertContains(
                    $config['chart']['queryId'],
                    $queryIds,
                    "Report '{$slug}' chart.queryId '{$config['chart']['queryId']}' not found in dataSource.queries"
                );
            }
        }
    }

    /**
     * Test chart metrics have required fields when present.
     */
    public function test_chart_metrics_structure()
    {
        foreach ($this->getTableReports() as $slug => $config) {
            if (!isset($config['chart']['metrics'])) {
                continue;
            }

            foreach ($config['chart']['metrics'] as $i => $metric) {
                $mId = $metric['key'] ?? "index:{$i}";

                $this->assertArrayHasKey('key', $metric, "Chart metric {$mId} in '{$slug}' missing 'key'");
                $this->assertArrayHasKey('label', $metric, "Chart metric {$mId} in '{$slug}' missing 'label'");
                $this->assertArrayHasKey('color', $metric, "Chart metric {$mId} in '{$slug}' missing 'color'");
            }
        }
    }

    // ------------------------------------------------------------------
    // Locked/Hardcoded filter validation
    // ------------------------------------------------------------------

    /**
     * Test lockedFilters have required fields.
     */
    public function test_locked_filters_structure()
    {
        foreach ($this->configs as $slug => $config) {
            if (!isset($config['lockedFilters'])) {
                continue;
            }

            foreach ($config['lockedFilters'] as $i => $filter) {
                $fId = $filter['id'] ?? "index:{$i}";

                $this->assertArrayHasKey('id', $filter, "Locked filter {$fId} in '{$slug}' missing 'id'");
                $this->assertArrayHasKey('label', $filter, "Locked filter {$fId} in '{$slug}' missing 'label'");
                $this->assertArrayHasKey('operator', $filter, "Locked filter {$fId} in '{$slug}' missing 'operator'");
                $this->assertArrayHasKey('value', $filter, "Locked filter {$fId} in '{$slug}' missing 'value'");
            }
        }
    }

    /**
     * Test hardcodedFilters have required fields.
     */
    public function test_hardcoded_filters_structure()
    {
        foreach ($this->configs as $slug => $config) {
            if (!isset($config['hardcodedFilters'])) {
                continue;
            }

            foreach ($config['hardcodedFilters'] as $i => $filter) {
                $fId = $filter['id'] ?? "index:{$i}";

                $this->assertArrayHasKey('id', $filter, "Hardcoded filter {$fId} in '{$slug}' missing 'id'");
                $this->assertArrayHasKey('label', $filter, "Hardcoded filter {$fId} in '{$slug}' missing 'label'");
                $this->assertArrayHasKey('operator', $filter, "Hardcoded filter {$fId} in '{$slug}' missing 'operator'");
                $this->assertArrayHasKey('rawOperator', $filter, "Hardcoded filter {$fId} in '{$slug}' missing 'rawOperator'");
                $this->assertArrayHasKey('value', $filter, "Hardcoded filter {$fId} in '{$slug}' missing 'value'");
                $this->assertArrayHasKey('rawValue', $filter, "Hardcoded filter {$fId} in '{$slug}' missing 'rawValue'");
            }
        }
    }

    // ------------------------------------------------------------------
    // Overview page validation
    // ------------------------------------------------------------------

    /**
     * Get overview configs.
     */
    private function getOverviewConfigs()
    {
        return array_filter($this->configs, function ($config) {
            return isset($config['type']) && $config['type'] === 'overview';
        });
    }

    /**
     * Test overview configs have required fields.
     */
    public function test_overview_configs_have_required_fields()
    {
        foreach ($this->getOverviewConfigs() as $slug => $config) {
            $this->assertArrayHasKey('pageId', $config, "Overview '{$slug}' missing 'pageId'");
            $this->assertNotEmpty($config['pageId'], "Overview '{$slug}' has empty 'pageId'");

            $this->assertArrayHasKey('queries', $config, "Overview '{$slug}' missing 'queries'");
            $this->assertIsArray($config['queries'], "Overview '{$slug}' 'queries' is not an array");
            $this->assertNotEmpty($config['queries'], "Overview '{$slug}' has empty 'queries'");

            $this->assertArrayHasKey('metrics', $config, "Overview '{$slug}' missing 'metrics'");
            $this->assertIsArray($config['metrics'], "Overview '{$slug}' 'metrics' is not an array");
            $this->assertNotEmpty($config['metrics'], "Overview '{$slug}' has empty 'metrics'");

            $this->assertArrayHasKey('widgets', $config, "Overview '{$slug}' missing 'widgets'");
            $this->assertIsArray($config['widgets'], "Overview '{$slug}' 'widgets' is not an array");
            $this->assertNotEmpty($config['widgets'], "Overview '{$slug}' has empty 'widgets'");
        }
    }

    /**
     * Test overview pageIds are unique.
     */
    public function test_overview_page_ids_are_unique()
    {
        $pageIds = [];
        foreach ($this->getOverviewConfigs() as $slug => $config) {
            $pageId = $config['pageId'];
            $this->assertArrayNotHasKey(
                $pageId,
                $pageIds,
                "Duplicate overview pageId '{$pageId}' in '{$slug}' and '" . ($pageIds[$pageId] ?? '') . "'"
            );
            $pageIds[$pageId] = $slug;
        }
    }

    /**
     * Test overview queries have required fields.
     */
    public function test_overview_queries_have_required_fields()
    {
        foreach ($this->getOverviewConfigs() as $slug => $config) {
            foreach ($config['queries'] as $qi => $query) {
                $qId = $query['id'] ?? "index:{$qi}";

                $this->assertArrayHasKey('id', $query, "Overview query {$qi} in '{$slug}' missing 'id'");

                // Chart-only queries skip normal validation
                if (isset($query['chart'])) {
                    continue;
                }

                $this->assertArrayHasKey('sources', $query, "Overview query '{$qId}' in '{$slug}' missing 'sources'");
                $this->assertIsArray($query['sources'], "Overview query '{$qId}' in '{$slug}' 'sources' is not an array");
                $this->assertNotEmpty($query['sources'], "Overview query '{$qId}' in '{$slug}' has empty 'sources'");

                $this->assertArrayHasKey('format', $query, "Overview query '{$qId}' in '{$slug}' missing 'format'");
                $this->assertContains(
                    $query['format'],
                    self::$validQueryFormats,
                    "Overview query '{$qId}' in '{$slug}' has invalid format '{$query['format']}'"
                );
            }
        }
    }

    /**
     * Test overview query ids are unique within each overview.
     */
    public function test_overview_query_ids_are_unique()
    {
        foreach ($this->getOverviewConfigs() as $slug => $config) {
            $ids = array_column($config['queries'], 'id');
            $this->assertCount(
                count(array_unique($ids)),
                $ids,
                "Overview '{$slug}' has duplicate query ids"
            );
        }
    }

    /**
     * Test overview metrics have required fields.
     */
    public function test_overview_metrics_have_required_fields()
    {
        foreach ($this->getOverviewConfigs() as $slug => $config) {
            foreach ($config['metrics'] as $i => $metric) {
                $mId = $metric['id'] ?? "index:{$i}";

                $this->assertArrayHasKey('id', $metric, "Overview metric {$i} in '{$slug}' missing 'id'");
                $this->assertArrayHasKey('label', $metric, "Overview metric '{$mId}' in '{$slug}' missing 'label'");
                $this->assertArrayHasKey('queryId', $metric, "Overview metric '{$mId}' in '{$slug}' missing 'queryId'");
                $this->assertArrayHasKey('valueField', $metric, "Overview metric '{$mId}' in '{$slug}' missing 'valueField'");
            }
        }
    }

    /**
     * Test overview metric queryIds reference existing queries.
     */
    public function test_overview_metric_query_ids_reference_existing_queries()
    {
        foreach ($this->getOverviewConfigs() as $slug => $config) {
            $queryIds = array_column($config['queries'], 'id');

            foreach ($config['metrics'] as $metric) {
                $mId = $metric['id'];
                $this->assertContains(
                    $metric['queryId'],
                    $queryIds,
                    "Overview metric '{$mId}' in '{$slug}' references missing queryId '{$metric['queryId']}'"
                );
            }
        }
    }

    /**
     * Test overview widgets have required fields.
     */
    public function test_overview_widgets_have_required_fields()
    {
        foreach ($this->getOverviewConfigs() as $slug => $config) {
            foreach ($config['widgets'] as $i => $widget) {
                $wId = $widget['id'] ?? "index:{$i}";

                $this->assertArrayHasKey('id', $widget, "Overview widget {$i} in '{$slug}' missing 'id'");
                $this->assertArrayHasKey('type', $widget, "Overview widget '{$wId}' in '{$slug}' missing 'type'");

                // Skip registered placeholders
                if ($widget['type'] === 'registered') {
                    continue;
                }

                $this->assertArrayHasKey('label', $widget, "Overview widget '{$wId}' in '{$slug}' missing 'label'");
                $this->assertArrayHasKey('defaultSize', $widget, "Overview widget '{$wId}' in '{$slug}' missing 'defaultSize'");
            }
        }
    }

    /**
     * Test overview widgets with queryId reference existing queries.
     */
    public function test_overview_widget_query_ids_reference_existing_queries()
    {
        foreach ($this->getOverviewConfigs() as $slug => $config) {
            $queryIds = array_column($config['queries'], 'id');

            foreach ($config['widgets'] as $widget) {
                if (!isset($widget['queryId']) || $widget['type'] === 'registered') {
                    continue;
                }
                $wId = $widget['id'];
                $this->assertContains(
                    $widget['queryId'],
                    $queryIds,
                    "Overview widget '{$wId}' in '{$slug}' references missing queryId '{$widget['queryId']}'"
                );
            }
        }
    }

    // ------------------------------------------------------------------
    // Detail page validation
    // ------------------------------------------------------------------

    /**
     * Get detail configs.
     */
    private function getDetailConfigs()
    {
        return array_filter($this->configs, function ($config) {
            return isset($config['type']) && $config['type'] === 'detail';
        });
    }

    /**
     * Test detail configs have required fields.
     */
    public function test_detail_configs_have_required_fields()
    {
        foreach ($this->getDetailConfigs() as $slug => $config) {
            $this->assertArrayHasKey('pageId', $config, "Detail '{$slug}' missing 'pageId'");
            $this->assertNotEmpty($config['pageId'], "Detail '{$slug}' has empty 'pageId'");

            $this->assertArrayHasKey('entityParam', $config, "Detail '{$slug}' missing 'entityParam'");
            $this->assertNotEmpty($config['entityParam'], "Detail '{$slug}' has empty 'entityParam'");

            $this->assertArrayHasKey('filterField', $config, "Detail '{$slug}' missing 'filterField'");
            $this->assertNotEmpty($config['filterField'], "Detail '{$slug}' has empty 'filterField'");

            $this->assertArrayHasKey('queries', $config, "Detail '{$slug}' missing 'queries'");
            $this->assertIsArray($config['queries'], "Detail '{$slug}' 'queries' is not an array");
            $this->assertNotEmpty($config['queries'], "Detail '{$slug}' has empty 'queries'");

            $this->assertArrayHasKey('metrics', $config, "Detail '{$slug}' missing 'metrics'");
            $this->assertIsArray($config['metrics'], "Detail '{$slug}' 'metrics' is not an array");
            $this->assertNotEmpty($config['metrics'], "Detail '{$slug}' has empty 'metrics'");

            $this->assertArrayHasKey('widgets', $config, "Detail '{$slug}' missing 'widgets'");
            $this->assertIsArray($config['widgets'], "Detail '{$slug}' 'widgets' is not an array");
            $this->assertNotEmpty($config['widgets'], "Detail '{$slug}' has empty 'widgets'");
        }
    }

    /**
     * Test detail pageIds are unique.
     */
    public function test_detail_page_ids_are_unique()
    {
        $pageIds = [];
        foreach ($this->getDetailConfigs() as $slug => $config) {
            $pageId = $config['pageId'];
            $this->assertArrayNotHasKey(
                $pageId,
                $pageIds,
                "Duplicate detail pageId '{$pageId}' in '{$slug}' and '" . ($pageIds[$pageId] ?? '') . "'"
            );
            $pageIds[$pageId] = $slug;
        }
    }

    /**
     * Test detail queries have required fields.
     */
    public function test_detail_queries_have_required_fields()
    {
        foreach ($this->getDetailConfigs() as $slug => $config) {
            foreach ($config['queries'] as $qi => $query) {
                $qId = $query['id'] ?? "index:{$qi}";

                $this->assertArrayHasKey('id', $query, "Detail query {$qi} in '{$slug}' missing 'id'");
                $this->assertArrayHasKey('sources', $query, "Detail query '{$qId}' in '{$slug}' missing 'sources'");
                $this->assertIsArray($query['sources'], "Detail query '{$qId}' in '{$slug}' 'sources' is not an array");
                $this->assertNotEmpty($query['sources'], "Detail query '{$qId}' in '{$slug}' has empty 'sources'");

                $this->assertArrayHasKey('format', $query, "Detail query '{$qId}' in '{$slug}' missing 'format'");
                $this->assertContains(
                    $query['format'],
                    self::$validQueryFormats,
                    "Detail query '{$qId}' in '{$slug}' has invalid format '{$query['format']}'"
                );
            }
        }
    }

    /**
     * Test detail metric queryIds reference existing queries.
     */
    public function test_detail_metric_query_ids_reference_existing_queries()
    {
        foreach ($this->getDetailConfigs() as $slug => $config) {
            $queryIds = array_column($config['queries'], 'id');

            foreach ($config['metrics'] as $metric) {
                $mId = $metric['id'];
                $this->assertContains(
                    $metric['queryId'],
                    $queryIds,
                    "Detail metric '{$mId}' in '{$slug}' references missing queryId '{$metric['queryId']}'"
                );
            }
        }
    }

    /**
     * Test detail widget queryIds reference existing queries.
     */
    public function test_detail_widget_query_ids_reference_existing_queries()
    {
        foreach ($this->getDetailConfigs() as $slug => $config) {
            $queryIds = array_column($config['queries'], 'id');

            foreach ($config['widgets'] as $widget) {
                if (!isset($widget['queryId']) || ($widget['type'] ?? '') === 'registered') {
                    continue;
                }
                $wId = $widget['id'];
                $this->assertContains(
                    $widget['queryId'],
                    $queryIds,
                    "Detail widget '{$wId}' in '{$slug}' references missing queryId '{$widget['queryId']}'"
                );
            }
        }
    }

    /**
     * Test entityInfo has required queryId and nameField when present.
     */
    public function test_detail_entity_info_structure()
    {
        foreach ($this->getDetailConfigs() as $slug => $config) {
            if (!isset($config['entityInfo'])) {
                continue;
            }

            $this->assertArrayHasKey('queryId', $config['entityInfo'], "Detail '{$slug}' entityInfo missing 'queryId'");
            $this->assertArrayHasKey('nameField', $config['entityInfo'], "Detail '{$slug}' entityInfo missing 'nameField'");

            // entityInfo.queryId should reference an existing query
            $queryIds = array_column($config['queries'], 'id');
            $this->assertContains(
                $config['entityInfo']['queryId'],
                $queryIds,
                "Detail '{$slug}' entityInfo.queryId '{$config['entityInfo']['queryId']}' not found in queries"
            );
        }
    }

    // ------------------------------------------------------------------
    // Overview/Detail chart widget validation
    // ------------------------------------------------------------------

    /**
     * Test chart widgets in overview/detail pages have valid chartConfig.metrics.
     */
    public function test_chart_widget_metrics_have_required_fields()
    {
        $configsWithWidgets = array_filter($this->configs, function ($config) {
            return isset($config['widgets']);
        });

        foreach ($configsWithWidgets as $slug => $config) {
            foreach ($config['widgets'] as $widget) {
                if (($widget['type'] ?? '') !== 'chart' || !isset($widget['chartConfig']['metrics'])) {
                    continue;
                }

                foreach ($widget['chartConfig']['metrics'] as $i => $metric) {
                    $mId = $metric['key'] ?? "index:{$i}";
                    $this->assertArrayHasKey('key', $metric, "Chart widget metric {$mId} in '{$slug}' missing 'key'");
                    $this->assertArrayHasKey('label', $metric, "Chart widget metric {$mId} in '{$slug}' missing 'label'");
                    $this->assertArrayHasKey('color', $metric, "Chart widget metric {$mId} in '{$slug}' missing 'color'");
                }
            }
        }
    }

    // ------------------------------------------------------------------
    // Smoke test: known report slugs exist
    // ------------------------------------------------------------------

    /**
     * Test that expected report slugs exist in the output.
     * This catches accidental removal of reports.
     */
    public function test_known_report_slugs_exist()
    {
        $expectedSlugs = [
            'device-categories',
            'countries',
            'referrers',
            'search-engines',
            'source-categories',
            '404-pages',
            'operating-systems',
            'cities',
            'timezones',
            'us-states',
            'european-countries',
            'country-regions',
            'social-media',
            'search-terms',
            'top-pages',
            'top-authors',
            'top-categories',
            'author-pages',
            'category-pages',
            'visitors',
            'top-visitors',
            'referred-visitors',
            'logged-in-users',
            // Overview pages
            'devices-overview',
            'geographic-overview',
            'referrals-overview',
            'visitors-overview',
            'page-insights-overview',
            'authors-overview',
            'content-overview',
            'categories-overview',
            // Detail pages
            'single-category',
            'single-content',
            'single-url',
        ];

        foreach ($expectedSlugs as $slug) {
            $this->assertArrayHasKey($slug, $this->configs, "Expected report slug '{$slug}' is missing");
        }
    }
}
