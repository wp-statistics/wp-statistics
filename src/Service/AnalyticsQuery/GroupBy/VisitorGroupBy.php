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
     * @param string $attribution Attribution model ('first_touch' or 'last_touch').
     * @return array
     */
    public function getSelectColumns(string $attribution = 'first_touch'): array
    {
        $columns = [$this->column . ' AS ' . $this->alias];
        $columns = array_merge($columns, $this->baseExtraColumns);

        // Add attribution-aware session-level columns
        $columns = array_merge($columns, $this->getAttributedColumns($attribution));

        return $columns;
    }

    /**
     * Get attribution-aware columns for session-level data.
     *
     * Uses subqueries to get data from the correct session based on attribution model.
     *
     * @param string $attribution Attribution model.
     * @return array
     */
    private function getAttributedColumns(string $attribution): array
    {
        $orderDirection = $attribution === 'last_touch' ? 'DESC' : 'ASC';

        $sessionsTable      = $this->tablePrefix . 'sessions';
        $countriesTable     = $this->tablePrefix . 'countries';
        $citiesTable        = $this->tablePrefix . 'cities';
        $deviceTypesTable   = $this->tablePrefix . 'device_types';
        $deviceOssTable     = $this->tablePrefix . 'device_oss';
        $deviceBrowsersTable = $this->tablePrefix . 'device_browsers';
        $referrersTable     = $this->tablePrefix . 'referrers';

        return [
            // User/IP from attributed session
            $this->buildSubquery(
                "s.user_id",
                $sessionsTable,
                $orderDirection
            ) . ' AS user_id',

            $this->buildSubquery(
                "s.ip",
                $sessionsTable,
                $orderDirection
            ) . ' AS ip_address',

            // Country from attributed session
            $this->buildSubquery(
                "c.code",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$countriesTable} c ON s.country_id = c.ID"
            ) . ' AS country_code',

            $this->buildSubquery(
                "c.name",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$countriesTable} c ON s.country_id = c.ID"
            ) . ' AS country_name',

            // City/Region from attributed session
            $this->buildSubquery(
                "ct.city_name",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$citiesTable} ct ON s.city_id = ct.ID"
            ) . ' AS city',

            $this->buildSubquery(
                "ct.region_name",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$citiesTable} ct ON s.city_id = ct.ID"
            ) . ' AS region',

            // Device info from attributed session
            $this->buildSubquery(
                "dt.name",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$deviceTypesTable} dt ON s.device_type_id = dt.ID"
            ) . ' AS device_type',

            $this->buildSubquery(
                "dos.name",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$deviceOssTable} dos ON s.device_os_id = dos.ID"
            ) . ' AS os',

            $this->buildSubquery(
                "db.name",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$deviceBrowsersTable} db ON s.device_browser_id = db.ID"
            ) . ' AS browser',

            // Referrer info from attributed session
            $this->buildSubquery(
                "r.domain",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$referrersTable} r ON s.referrer_id = r.ID"
            ) . ' AS referrer',

            $this->buildSubquery(
                "r.channel",
                $sessionsTable,
                $orderDirection,
                "LEFT JOIN {$referrersTable} r ON s.referrer_id = r.ID"
            ) . ' AS referrer_channel',
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
