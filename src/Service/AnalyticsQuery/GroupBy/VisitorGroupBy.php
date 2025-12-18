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
        $columns = array_merge($columns, $this->baseExtraColumns);

        // Add attribution-aware session-level columns (conditionally if columns are requested)
        $columns = array_merge($columns, $this->getAttributedColumns($attribution, $requestedColumns));

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

        $columns = [];

        // User info from attributed session
        if ($includeAll || in_array('user_id', $requestedColumns, true)) {
            $columns[] = $this->buildSubquery(
                "s.user_id",
                $sessionsTable,
                $orderDirection
            ) . ' AS user_id';
        }

        if ($includeAll || in_array('user_login', $requestedColumns, true)) {
            global $wpdb;
            $usersTable = $wpdb->users;
            $columns[] = $this->buildSubquery(
                "u.user_login",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$usersTable} u ON s.user_id = u.ID"
            ) . ' AS user_login';
        }

        if ($includeAll || in_array('ip_address', $requestedColumns, true)) {
            $columns[] = $this->buildSubquery(
                "s.ip",
                $sessionsTable,
                $orderDirection
            ) . ' AS ip_address';
        }

        // Country from attributed session
        if ($includeAll || in_array('country_code', $requestedColumns, true)) {
            $columns[] = $this->buildSubquery(
                "c.code",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$countriesTable} c ON s.country_id = c.ID"
            ) . ' AS country_code';
        }

        if ($includeAll || in_array('country_name', $requestedColumns, true)) {
            $columns[] = $this->buildSubquery(
                "c.name",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$countriesTable} c ON s.country_id = c.ID"
            ) . ' AS country_name';
        }

        // City/Region from attributed session
        if ($includeAll || in_array('city_name', $requestedColumns, true)) {
            $columns[] = $this->buildSubquery(
                "ct.city_name",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$citiesTable} ct ON s.city_id = ct.ID"
            ) . ' AS city_name';
        }

        if ($includeAll || in_array('region_name', $requestedColumns, true)) {
            $columns[] = $this->buildSubquery(
                "ct.region_name",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$citiesTable} ct ON s.city_id = ct.ID"
            ) . ' AS region_name';
        }

        // Device info from attributed session
        if ($includeAll || in_array('device_type_name', $requestedColumns, true)) {
            $columns[] = $this->buildSubquery(
                "dt.name",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$deviceTypesTable} dt ON s.device_type_id = dt.ID"
            ) . ' AS device_type_name';
        }

        if ($includeAll || in_array('os_name', $requestedColumns, true)) {
            $columns[] = $this->buildSubquery(
                "dos.name",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$deviceOssTable} dos ON s.device_os_id = dos.ID"
            ) . ' AS os_name';
        }

        if ($includeAll || in_array('browser_name', $requestedColumns, true)) {
            $columns[] = $this->buildSubquery(
                "db.name",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$deviceBrowsersTable} db ON s.device_browser_id = db.ID"
            ) . ' AS browser_name';
        }

        // Referrer info from attributed session
        if ($includeAll || in_array('referrer_domain', $requestedColumns, true)) {
            $columns[] = $this->buildSubquery(
                "r.domain",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$referrersTable} r ON s.referrer_id = r.ID"
            ) . ' AS referrer_domain';
        }

        if ($includeAll || in_array('referrer_channel', $requestedColumns, true)) {
            $columns[] = $this->buildSubquery(
                "r.channel",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$referrersTable} r ON s.referrer_id = r.ID"
            ) . ' AS referrer_channel';
        }

        // Entry page (first page in session) - optimized with single subquery for both fields
        $needsEntryPage = $includeAll || in_array('entry_page', $requestedColumns, true) || in_array('entry_page_title', $requestedColumns, true);

        if ($needsEntryPage) {
            // Use single subquery to get entry page data (uri and title together for efficiency)
            $columns[] = $this->buildSubquery(
                "ru_entry.uri",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$viewsTable} v_entry ON s.initial_view_id = v_entry.ID
        LEFT JOIN {$resourceUrisTable} ru_entry ON v_entry.resource_uri_id = ru_entry.ID"
            ) . ' AS entry_page';

            $columns[] = $this->buildSubquery(
                "res_entry.cached_title",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$viewsTable} v_entry ON s.initial_view_id = v_entry.ID
        LEFT JOIN {$resourceUrisTable} ru_entry ON v_entry.resource_uri_id = ru_entry.ID
        LEFT JOIN {$resourcesTable} res_entry ON ru_entry.resource_id = res_entry.ID"
            ) . ' AS entry_page_title';
        }

        // Exit page (last page in session) - optimized with single subquery for both fields
        $needsExitPage = $includeAll || in_array('exit_page', $requestedColumns, true) || in_array('exit_page_title', $requestedColumns, true);

        if ($needsExitPage) {
            // Use single subquery to get exit page data (uri and title together for efficiency)
            $columns[] = $this->buildSubquery(
                "ru_exit.uri",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$viewsTable} v_exit ON s.last_view_id = v_exit.ID
        LEFT JOIN {$resourceUrisTable} ru_exit ON v_exit.resource_uri_id = ru_exit.ID"
            ) . ' AS exit_page';

            $columns[] = $this->buildSubquery(
                "res_exit.cached_title",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$viewsTable} v_exit ON s.last_view_id = v_exit.ID
        LEFT JOIN {$resourceUrisTable} ru_exit ON v_exit.resource_uri_id = ru_exit.ID
        LEFT JOIN {$resourcesTable} res_exit ON ru_exit.resource_id = res_exit.ID"
            ) . ' AS exit_page_title';
        }

        return $columns;
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
