<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Visitor group by - groups by visitor.
 *
 * Returns visitor data with attribution-aware session-level fields.
 * Session-level data (referrer, country, device, etc.) is attributed based on:
 * - First Touch: data from the visitor's FIRST session
 * - Last Touch: data from the visitor's MOST RECENT session
 *
 * Aggregate data (total sessions, total views) is NOT affected by attribution.
 *
 * @since 15.0.0
 */
class VisitorGroupBy extends AbstractGroupBy
{
    protected $name    = 'visitor';
    protected $column  = 'visitors.ID';
    protected $alias   = 'visitor_id';
    protected $groupBy = 'visitors.ID';

    /**
     * Base extra columns that are not affected by attribution.
     *
     * @var array
     */
    protected $baseExtraColumns = [
        'visitors.hash AS visitor_hash',
        'MIN(sessions.started_at) AS first_visit',
        'MAX(sessions.started_at) AS last_visit',
        'COUNT(DISTINCT sessions.ID) AS total_sessions',
        'SUM(sessions.total_views) AS total_views',
    ];

    /**
     * Joins for visitor grouping.
     *
     * Note: We only join sessions and visitors here.
     * Other joins (countries, cities, etc.) are done via subqueries
     * to support proper attribution.
     *
     * Entry/exit page joins are added dynamically in getJoins() method
     * to support efficient attribution-based lookups.
     *
     * @var array
     */
    protected $joins = [
        [
            'table' => 'visitors',
            'alias' => 'visitors',
            'on'    => 'sessions.visitor_id = visitors.ID',
            'type'  => 'INNER',
        ],
    ];

    /**
     * Table prefix for subqueries.
     *
     * @var string
     */
    private $tablePrefix;

    /**
     * Requested columns cache for join optimization.
     *
     * @var array
     */
    private $requestedColumnsCache = [];

    /**
     * Attribution model cache.
     *
     * @var string
     */
    private $attributionCache = 'first_touch';

    /**
     * Constructor.
     */
    public function __construct()
    {
        global $wpdb;
        $this->tablePrefix = $wpdb->prefix . 'statistics_';
    }

    /**
     * Get SELECT columns with attribution support.
     *
     * @param string $attribution      Attribution model ('first_touch' or 'last_touch').
     * @param array  $requestedColumns Optional list of requested column aliases to filter which columns to include.
     * @return array
     */
    public function getSelectColumns(string $attribution = 'first_touch', array $requestedColumns = []): array
    {
        $columns = [$this->column . ' AS ' . $this->alias];

        // Add base extra columns conditionally based on requested columns
        $columns = array_merge($columns, $this->getBaseExtraColumns($requestedColumns));

        // Add attribution-aware session-level columns (conditionally if columns are requested)
        $columns = array_merge($columns, $this->getAttributedColumns($attribution, $requestedColumns));

        return $columns;
    }

    /**
     * Get base extra columns conditionally based on requested columns.
     *
     * @param array $requestedColumns List of requested column aliases. Empty = include all.
     * @return array
     */
    private function getBaseExtraColumns(array $requestedColumns = []): array
    {
        $includeAll = empty($requestedColumns);
        $columns = [];

        // visitor_hash
        if ($includeAll || in_array('visitor_hash', $requestedColumns, true)) {
            $columns[] = 'visitors.hash AS visitor_hash';
        }

        // first_visit
        if ($includeAll || in_array('first_visit', $requestedColumns, true)) {
            $columns[] = 'MIN(sessions.started_at) AS first_visit';
        }

        // last_visit
        if ($includeAll || in_array('last_visit', $requestedColumns, true)) {
            $columns[] = 'MAX(sessions.started_at) AS last_visit';
        }

        // total_sessions
        if ($includeAll || in_array('total_sessions', $requestedColumns, true)) {
            $columns[] = 'COUNT(DISTINCT sessions.ID) AS total_sessions';
        }

        // total_views
        if ($includeAll || in_array('total_views', $requestedColumns, true)) {
            $columns[] = 'SUM(sessions.total_views) AS total_views';
        }

        return $columns;
    }

    /**
     * Get attribution-aware columns for session-level data.
     *
     * Uses subqueries to get data from the correct session based on attribution model.
     * Only includes columns that are in the requested list (for performance optimization).
     *
     * @param string $attribution      Attribution model.
     * @param array  $requestedColumns List of requested column aliases. Empty = include all.
     * @return array
     */
    private function getAttributedColumns(string $attribution, array $requestedColumns = []): array
    {
        $orderDirection = $attribution === 'last_touch' ? 'DESC' : 'ASC';
        $includeAll = empty($requestedColumns);

        $sessionsTable       = $this->tablePrefix . 'sessions';
        $countriesTable      = $this->tablePrefix . 'countries';
        $citiesTable         = $this->tablePrefix . 'cities';
        $deviceTypesTable    = $this->tablePrefix . 'device_types';
        $deviceOssTable      = $this->tablePrefix . 'device_oss';
        $deviceBrowsersTable = $this->tablePrefix . 'device_browsers';
        $referrersTable      = $this->tablePrefix . 'referrers';
        $viewsTable          = $this->tablePrefix . 'views';
        $resourceUrisTable   = $this->tablePrefix . 'resource_uris';
        $resourcesTable      = $this->tablePrefix . 'resources';

        // Cache for join generation
        $this->requestedColumnsCache = $requestedColumns;
        $this->attributionCache = $attribution;

        $columns = [];

        // OPTIMIZED: Use MIN/MAX + CONCAT trick instead of GROUP_CONCAT
        // This is MUCH faster because MIN/MAX only tracks one value, not building huge strings
        // For first_touch: MIN(CONCAT(date, value)) gives earliest
        // For last_touch: MAX(CONCAT(date, value)) gives latest
        $aggFunc = $attribution === 'last_touch' ? 'MAX' : 'MIN';

        // User info from attributed session
        if ($includeAll || in_array('user_id', $requestedColumns, true)) {
            $columns[] = "SUBSTRING_INDEX({$aggFunc}(CONCAT(sessions.started_at, '||', sessions.user_id)), '||', -1) AS user_id";
        }

        if ($includeAll || in_array('user_login', $requestedColumns, true)) {
            $columns[] = "SUBSTRING_INDEX({$aggFunc}(CONCAT(sessions.started_at, '||', attr_user.user_login)), '||', -1) AS user_login";
        }

        if ($includeAll || in_array('ip_address', $requestedColumns, true)) {
            $columns[] = "SUBSTRING_INDEX({$aggFunc}(CONCAT(sessions.started_at, '||', sessions.ip)), '||', -1) AS ip_address";
        }

        // Country from attributed session
        if ($includeAll || in_array('country_code', $requestedColumns, true)) {
            $columns[] = "SUBSTRING_INDEX({$aggFunc}(CONCAT(sessions.started_at, '||', attr_country.code)), '||', -1) AS country_code";
        }

        if ($includeAll || in_array('country_name', $requestedColumns, true)) {
            $columns[] = "SUBSTRING_INDEX({$aggFunc}(CONCAT(sessions.started_at, '||', attr_country.name)), '||', -1) AS country_name";
        }

        // City/Region from attributed session
        if ($includeAll || in_array('city_name', $requestedColumns, true)) {
            $columns[] = "SUBSTRING_INDEX({$aggFunc}(CONCAT(sessions.started_at, '||', attr_city.city_name)), '||', -1) AS city_name";
        }

        if ($includeAll || in_array('region_name', $requestedColumns, true)) {
            $columns[] = "SUBSTRING_INDEX({$aggFunc}(CONCAT(sessions.started_at, '||', attr_city.region_name)), '||', -1) AS region_name";
        }

        // Device info from attributed session
        if ($includeAll || in_array('device_type_name', $requestedColumns, true)) {
            $columns[] = "SUBSTRING_INDEX({$aggFunc}(CONCAT(sessions.started_at, '||', attr_device_type.name)), '||', -1) AS device_type_name";
        }

        if ($includeAll || in_array('os_name', $requestedColumns, true)) {
            $columns[] = "SUBSTRING_INDEX({$aggFunc}(CONCAT(sessions.started_at, '||', attr_os.name)), '||', -1) AS os_name";
        }

        if ($includeAll || in_array('browser_name', $requestedColumns, true)) {
            $columns[] = "SUBSTRING_INDEX({$aggFunc}(CONCAT(sessions.started_at, '||', attr_browser.name)), '||', -1) AS browser_name";
        }

        // Referrer info from attributed session
        if ($includeAll || in_array('referrer_domain', $requestedColumns, true)) {
            $columns[] = "SUBSTRING_INDEX({$aggFunc}(CONCAT(sessions.started_at, '||', attr_referrer.domain)), '||', -1) AS referrer_domain";
        }

        if ($includeAll || in_array('referrer_channel', $requestedColumns, true)) {
            $columns[] = "SUBSTRING_INDEX({$aggFunc}(CONCAT(sessions.started_at, '||', attr_referrer.channel)), '||', -1) AS referrer_channel";
        }

        // Entry/Exit pages - optimized using MIN/MAX + CONCAT trick
        // This is MUCH faster because MIN/MAX only tracks one value, not building huge strings

        // Determine if we need entry/exit page columns
        $needsEntryPage = $includeAll || in_array('entry_page', $requestedColumns, true) || in_array('entry_page_title', $requestedColumns, true);
        $needsExitPage = $includeAll || in_array('exit_page', $requestedColumns, true) || in_array('exit_page_title', $requestedColumns, true);

        if ($needsEntryPage || $needsExitPage) {
            // Cache for join generation
            $this->requestedColumnsCache = $requestedColumns;
            $this->attributionCache = $attribution;

            // We'll use aggregate functions on the joined data
            // The joins will be added in getJoins() method

            if ($needsEntryPage) {
                // Get entry page from attributed session using MIN/MAX + CONCAT
                // For first_touch: MIN gives earliest, for last_touch: MAX gives latest

                if ($includeAll || in_array('entry_page', $requestedColumns, true)) {
                    $columns[] = "SUBSTRING_INDEX({$aggFunc}(CONCAT(sessions.started_at, '||', entry_page_uri.uri)), '||', -1) AS entry_page";
                }

                if ($includeAll || in_array('entry_page_title', $requestedColumns, true)) {
                    $columns[] = "SUBSTRING_INDEX({$aggFunc}(CONCAT(sessions.started_at, '||', entry_page_resource.cached_title)), '||', -1) AS entry_page_title";
                }
            }

            if ($needsExitPage) {
                // Get exit page from attributed session using MIN/MAX + CONCAT

                if ($includeAll || in_array('exit_page', $requestedColumns, true)) {
                    $columns[] = "SUBSTRING_INDEX({$aggFunc}(CONCAT(sessions.started_at, '||', exit_page_uri.uri)), '||', -1) AS exit_page";
                }

                if ($includeAll || in_array('exit_page_title', $requestedColumns, true)) {
                    $columns[] = "SUBSTRING_INDEX({$aggFunc}(CONCAT(sessions.started_at, '||', exit_page_resource.cached_title)), '||', -1) AS exit_page_title";
                }
            }
        }

        return $columns;
    }

    /**
     * Get joins with dynamic entry/exit page joins.
     *
     * Override parent to add conditional joins for entry/exit page lookups
     * when those columns are requested.
     *
     * @return array Array of join configurations.
     */
    public function getJoins(): array
    {
        $joins = $this->joins;

        // Check what columns were requested
        $requestedColumns = $this->requestedColumnsCache;
        $includeAll = empty($requestedColumns);

        // Add attributed session data joins (for country, browser, OS, referrer, user, etc.)
        // These replace correlated subqueries for MUCH better performance

        // User joins
        if ($includeAll || in_array('user_login', $requestedColumns, true)) {
            global $wpdb;
            $joins[] = [
                'table' => $wpdb->users,
                'alias' => 'attr_user',
                'on'    => 'sessions.user_id = attr_user.ID',
                'type'  => 'LEFT',
            ];
        }

        // Country joins
        if ($includeAll || in_array('country_code', $requestedColumns, true) || in_array('country_name', $requestedColumns, true)) {
            $joins[] = [
                'table' => 'countries',
                'alias' => 'attr_country',
                'on'    => 'sessions.country_id = attr_country.ID',
                'type'  => 'LEFT',
            ];
        }

        // City joins
        if ($includeAll || in_array('city_name', $requestedColumns, true) || in_array('region_name', $requestedColumns, true)) {
            $joins[] = [
                'table' => 'cities',
                'alias' => 'attr_city',
                'on'    => 'sessions.city_id = attr_city.ID',
                'type'  => 'LEFT',
            ];
        }

        // Device type joins
        if ($includeAll || in_array('device_type_name', $requestedColumns, true)) {
            $joins[] = [
                'table' => 'device_types',
                'alias' => 'attr_device_type',
                'on'    => 'sessions.device_type_id = attr_device_type.ID',
                'type'  => 'LEFT',
            ];
        }

        // OS joins
        if ($includeAll || in_array('os_name', $requestedColumns, true)) {
            $joins[] = [
                'table' => 'device_oss',
                'alias' => 'attr_os',
                'on'    => 'sessions.device_os_id = attr_os.ID',
                'type'  => 'LEFT',
            ];
        }

        // Browser joins
        if ($includeAll || in_array('browser_name', $requestedColumns, true)) {
            $joins[] = [
                'table' => 'device_browsers',
                'alias' => 'attr_browser',
                'on'    => 'sessions.device_browser_id = attr_browser.ID',
                'type'  => 'LEFT',
            ];
        }

        // Referrer joins
        if ($includeAll || in_array('referrer_domain', $requestedColumns, true) || in_array('referrer_channel', $requestedColumns, true)) {
            $joins[] = [
                'table' => 'referrers',
                'alias' => 'attr_referrer',
                'on'    => 'sessions.referrer_id = attr_referrer.ID',
                'type'  => 'LEFT',
            ];
        }

        // Entry/exit page joins
        $needsEntryPage = $includeAll ||
            in_array('entry_page', $requestedColumns, true) ||
            in_array('entry_page_title', $requestedColumns, true);

        $needsExitPage = $includeAll ||
            in_array('exit_page', $requestedColumns, true) ||
            in_array('exit_page_title', $requestedColumns, true);

        // Add entry page joins if needed
        if ($needsEntryPage) {
            $joins[] = [
                'table' => 'views',
                'alias' => 'entry_page_view',
                'on'    => 'sessions.initial_view_id = entry_page_view.ID',
                'type'  => 'LEFT',
            ];

            $joins[] = [
                'table' => 'resource_uris',
                'alias' => 'entry_page_uri',
                'on'    => 'entry_page_view.resource_uri_id = entry_page_uri.ID',
                'type'  => 'LEFT',
            ];

            if ($includeAll || in_array('entry_page_title', $requestedColumns, true)) {
                $joins[] = [
                    'table' => 'resources',
                    'alias' => 'entry_page_resource',
                    'on'    => 'entry_page_uri.resource_id = entry_page_resource.ID',
                    'type'  => 'LEFT',
                ];
            }
        }

        // Add exit page joins if needed
        if ($needsExitPage) {
            $joins[] = [
                'table' => 'views',
                'alias' => 'exit_page_view',
                'on'    => 'sessions.last_view_id = exit_page_view.ID',
                'type'  => 'LEFT',
            ];

            $joins[] = [
                'table' => 'resource_uris',
                'alias' => 'exit_page_uri',
                'on'    => 'exit_page_view.resource_uri_id = exit_page_uri.ID',
                'type'  => 'LEFT',
            ];

            if ($includeAll || in_array('exit_page_title', $requestedColumns, true)) {
                $joins[] = [
                    'table' => 'resources',
                    'alias' => 'exit_page_resource',
                    'on'    => 'exit_page_uri.resource_id = exit_page_resource.ID',
                    'type'  => 'LEFT',
                ];
            }
        }

        return $joins;
    }

    /**
     * Get aliases of extra columns for validation.
     *
     * Override parent to include all dynamically generated column aliases
     * from both base columns and attributed columns.
     *
     * @return array Array of extra column aliases.
     */
    public function getExtraColumnAliases(): array
    {
        return [
            'visitor_hash',
            'last_visit',
            'total_sessions',
            'total_views',
            'user_id',
            'user_login',
            'ip_address',
            'country_code',
            'country_name',
            'city_name',
            'region_name',
            'device_type_name',
            'os_name',
            'browser_name',
            'referrer_domain',
            'referrer_channel',
            'entry_page',
            'entry_page_title',
            'exit_page',
            'exit_page_title',
        ];
    }

    /**
     * Build a subquery to get data from attributed session.
     *
     * @param string      $selectColumn   Column to select.
     * @param string      $sessionsTable  Full sessions table name.
     * @param string      $orderDirection ASC for first touch, DESC for last touch.
     * @param string|null $additionalJoin Optional additional JOIN clause.
     * @return string Subquery SQL.
     */
    private function buildSubquery(
        string $selectColumn,
        string $sessionsTable,
        string $orderDirection,
        ?string $additionalJoin = null
    ): string {
        $join = $additionalJoin ? "\n        {$additionalJoin}" : '';

        return "(
        SELECT {$selectColumn}
        FROM {$sessionsTable} s{$join}
        WHERE s.visitor_id = visitors.ID
        ORDER BY s.started_at {$orderDirection}
        LIMIT 1
    )";
    }
}
