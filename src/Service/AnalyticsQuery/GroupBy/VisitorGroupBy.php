<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

use WP_Statistics\Components\DateTime;

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
        'LEFT(visitors.hash, 6) AS visitor_hash',
        'MIN(sessions.started_at) AS first_visit',
        'MAX(sessions.started_at) AS last_visit',
        'COUNT(DISTINCT sessions.ID) AS total_sessions',
        'SUM(sessions.total_views) AS total_views',
    ];

    /**
     * Joins for visitor grouping.
     *
     * Only joins visitors table. Session attributes (country, browser, etc.)
     * are fetched by QueryExecutor in a second query.
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
     * Datetime fields that need UTC to site timezone conversion.
     *
     * @var array
     */
    protected $datetimeFields = ['first_visit', 'last_visit'];

    /**
     * Columns added by postProcess (not in SQL, but valid for column selection).
     *
     * @var array
     */
    protected $postProcessedColumns = ['first_visit_formatted', 'last_visit_formatted'];

    /**
     * Get SELECT columns with attribution support.
     *
     * Returns base columns + attributed_session_id. The QueryExecutor will
     * fetch session attributes (country, browser, etc.) in a second query
     * and merge them into the results.
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

        // Add attributed_session_id - QueryExecutor will use this to fetch session attributes
        $aggFunc = $attribution === 'last_touch' ? 'MAX' : 'MIN';
        $columns[] = "CAST(SUBSTRING_INDEX({$aggFunc}(CONCAT(sessions.started_at, '||', sessions.ID)), '||', -1) AS UNSIGNED) AS attributed_session_id";

        return $columns;
    }

    /**
     * Get base extra columns conditionally based on requested columns.
     *
     * @param array $requestedColumns List of requested column aliases. Empty = include all.
     * @return array
     */
    protected function getBaseExtraColumns(array $requestedColumns = []): array
    {
        $includeAll = empty($requestedColumns);
        $columns = [];

        // visitor_hash (truncated to 6 chars for display)
        if ($includeAll || in_array('visitor_hash', $requestedColumns, true)) {
            $columns[] = 'LEFT(visitors.hash, 6) AS visitor_hash';
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
     * Get joins for visitor grouping.
     *
     * Returns only the visitors join. Session attributes (country, browser, etc.)
     * are fetched by QueryExecutor in a second query using attributed_session_id.
     *
     * @return array Array of join configurations.
     */
    public function getJoins(): array
    {
        return $this->joins;
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
            'first_visit',
            'last_visit',
            'total_sessions',
            'total_views',
            'user_id',
            'user_login',
            'user_email',
            'user_role',
            'ip_address',
            'country_code',
            'country_name',
            'city_name',
            'region_name',
            'device_type_name',
            'os_name',
            'browser_name',
            'browser_version',
            'referrer_domain',
            'referrer_channel',
            'entry_page',
            'entry_page_title',
            'entry_page_type',
            'entry_page_wp_id',
            'entry_page_resource_id',
            'exit_page',
            'exit_page_title',
            'exit_page_type',
            'exit_page_wp_id',
            'exit_page_resource_id',
        ];
    }

    /**
     * Post-process visitor rows to enrich with session attributes.
     *
     * Fetches session attributes (country, browser, referrer, etc.) in a second
     * query using the attributed_session_id and merges them into the results.
     *
     * @param array $rows Query result rows.
     * @param \wpdb $wpdb WordPress database instance.
     * @return array Enriched rows.
     */
    public function postProcess(array $rows, \wpdb $wpdb): array
    {
        if (empty($rows) || !isset($rows[0]['attributed_session_id'])) {
            $rows = $this->convertDatetimeFields($rows);
            return $this->addFormattedDateFields($rows);
        }

        $sessionIds = array_filter(array_column($rows, 'attributed_session_id'));

        if (!empty($sessionIds)) {
            $sessionAttributes = $this->fetchSessionAttributes($sessionIds, $wpdb);
            $rows              = $this->mergeSessionAttributes($rows, $sessionAttributes);
        }

        // Remove the temporary attributed_session_id column from output
        foreach ($rows as &$row) {
            unset($row['attributed_session_id']);
        }

        $rows = $this->convertDatetimeFields($rows);
        return $this->addFormattedDateFields($rows);
    }

    /**
     * Add formatted date fields for first_visit and last_visit.
     *
     * @param array $rows Query result rows.
     * @return array Rows with formatted date fields added.
     */
    private function addFormattedDateFields(array $rows): array
    {
        foreach ($rows as &$row) {
            if (!empty($row['first_visit'])) {
                $row['first_visit_formatted'] = DateTime::format($row['first_visit'], [
                    'include_time' => false,
                    'short_month'  => true,
                ]);
            }
            if (!empty($row['last_visit'])) {
                $row['last_visit_formatted'] = DateTime::format($row['last_visit'], [
                    'include_time' => false,
                    'short_month'  => true,
                ]);
            }
        }

        return $rows;
    }

    /**
     * Fetch session attributes for given session IDs.
     *
     * @param array $sessionIds Array of session IDs.
     * @param \wpdb $wpdb       WordPress database instance.
     * @return array Indexed array of session attributes by session ID.
     */
    private function fetchSessionAttributes(array $sessionIds, \wpdb $wpdb): array
    {
        $tablePrefix  = $wpdb->prefix . 'statistics_';
        $placeholders = implode(',', array_fill(0, count($sessionIds), '%d'));

        $sql = "
            SELECT
                sessions.ID AS session_id,
                sessions.user_id,
                attr_user.user_login,
                attr_user.user_email,
                sessions.ip AS ip_address,
                attr_country.code AS country_code,
                attr_country.name AS country_name,
                attr_city.city_name,
                attr_city.region_name,
                attr_device_type.name AS device_type_name,
                attr_os.name AS os_name,
                attr_browser.name AS browser_name,
                attr_browser_version.version AS browser_version,
                attr_referrer.domain AS referrer_domain,
                attr_referrer.channel AS referrer_channel,
                entry_page_uri.uri AS entry_page,
                entry_page_resource.cached_title AS entry_page_title,
                entry_page_resource.resource_type AS entry_page_type,
                entry_page_resource.resource_id AS entry_page_wp_id,
                entry_page_resource.ID AS entry_page_resource_id,
                exit_page_uri.uri AS exit_page,
                exit_page_resource.cached_title AS exit_page_title,
                exit_page_resource.resource_type AS exit_page_type,
                exit_page_resource.resource_id AS exit_page_wp_id,
                exit_page_resource.ID AS exit_page_resource_id,
                attr_user_role.meta_value AS user_role_raw
            FROM {$tablePrefix}sessions sessions
            LEFT JOIN {$wpdb->users} attr_user ON sessions.user_id = attr_user.ID
            LEFT JOIN {$wpdb->usermeta} attr_user_role ON sessions.user_id = attr_user_role.user_id AND attr_user_role.meta_key = '{$wpdb->prefix}capabilities'
            LEFT JOIN {$tablePrefix}countries attr_country ON sessions.country_id = attr_country.ID
            LEFT JOIN {$tablePrefix}cities attr_city ON sessions.city_id = attr_city.ID
            LEFT JOIN {$tablePrefix}device_types attr_device_type ON sessions.device_type_id = attr_device_type.ID
            LEFT JOIN {$tablePrefix}device_oss attr_os ON sessions.device_os_id = attr_os.ID
            LEFT JOIN {$tablePrefix}device_browsers attr_browser ON sessions.device_browser_id = attr_browser.ID
            LEFT JOIN {$tablePrefix}device_browser_versions attr_browser_version ON sessions.device_browser_version_id = attr_browser_version.ID
            LEFT JOIN {$tablePrefix}referrers attr_referrer ON sessions.referrer_id = attr_referrer.ID
            LEFT JOIN {$tablePrefix}views entry_view ON sessions.initial_view_id = entry_view.ID
            LEFT JOIN {$tablePrefix}resource_uris entry_page_uri ON entry_view.resource_uri_id = entry_page_uri.ID
            LEFT JOIN {$tablePrefix}resources entry_page_resource ON entry_page_uri.resource_id = entry_page_resource.ID
            LEFT JOIN {$tablePrefix}views exit_view ON sessions.last_view_id = exit_view.ID
            LEFT JOIN {$tablePrefix}resource_uris exit_page_uri ON exit_view.resource_uri_id = exit_page_uri.ID
            LEFT JOIN {$tablePrefix}resources exit_page_resource ON exit_page_uri.resource_id = exit_page_resource.ID
            WHERE sessions.ID IN ($placeholders)
        ";

        $preparedSql = $wpdb->prepare($sql, $sessionIds);
        $results     = $wpdb->get_results($preparedSql, ARRAY_A);

        // Index by session_id for fast lookup
        $indexed = [];
        foreach ($results as $row) {
            $indexed[$row['session_id']] = $row;
        }

        return $indexed;
    }

    /**
     * Merge session attributes into visitor rows.
     *
     * @param array $rows              Visitor rows with attributed_session_id.
     * @param array $sessionAttributes Session attributes indexed by session ID.
     * @return array Merged rows.
     */
    private function mergeSessionAttributes(array $rows, array $sessionAttributes): array
    {
        foreach ($rows as &$row) {
            $sessionId = $row['attributed_session_id'] ?? null;

            if ($sessionId && isset($sessionAttributes[$sessionId])) {
                $attrs = $sessionAttributes[$sessionId];
                unset($attrs['session_id']);

                // Convert serialized capabilities to readable role name
                if (!empty($attrs['user_role_raw'])) {
                    $attrs['user_role'] = $this->extractRoleFromCapabilities($attrs['user_role_raw']);
                } else {
                    $attrs['user_role'] = null;
                }
                unset($attrs['user_role_raw']);

                $row = array_merge($row, $attrs);
            } else {
                $row = array_merge($row, $this->getEmptySessionAttributes());
            }
        }

        return $rows;
    }

    /**
     * Extract the primary role name from serialized WordPress capabilities.
     *
     * @param string $serialized Serialized capabilities string from wp_usermeta.
     * @return string|null The primary role name, or null if unable to extract.
     */
    private function extractRoleFromCapabilities(string $serialized): ?string
    {
        $capabilities = @unserialize($serialized);

        if (!is_array($capabilities) || empty($capabilities)) {
            return null;
        }

        // Get the first role (primary role)
        foreach ($capabilities as $role => $enabled) {
            if ($enabled) {
                // Convert role slug to readable name using WordPress translate functions
                $roleNames = \wp_roles()->get_names();
                return isset($roleNames[$role]) ? \translate_user_role($roleNames[$role]) : ucfirst($role);
            }
        }

        return null;
    }

    /**
     * Get empty session attributes array.
     *
     * @return array
     */
    private function getEmptySessionAttributes(): array
    {
        return [
            'user_id'          => null,
            'user_login'       => null,
            'user_email'       => null,
            'user_role'        => null,
            'ip_address'       => null,
            'country_code'     => null,
            'country_name'     => null,
            'city_name'        => null,
            'region_name'      => null,
            'device_type_name' => null,
            'os_name'          => null,
            'browser_name'     => null,
            'browser_version'  => null,
            'referrer_domain'  => null,
            'referrer_channel' => null,
            'entry_page'       => null,
            'entry_page_title' => null,
            'entry_page_type'  => null,
            'entry_page_wp_id' => null,
            'entry_page_resource_id' => null,
            'exit_page'        => null,
            'exit_page_title'  => null,
            'exit_page_type'   => null,
            'exit_page_wp_id'  => null,
            'exit_page_resource_id' => null,
        ];
    }
}
