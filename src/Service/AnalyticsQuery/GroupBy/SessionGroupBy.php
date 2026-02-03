<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

use WP_Statistics\Components\DateTime;

/**
 * Session group by - returns individual session rows.
 *
 * Used for session history tables where each row represents one session.
 * Unlike other group by classes that aggregate data, this returns raw session rows.
 *
 * @since 15.0.0
 */
class SessionGroupBy extends AbstractGroupBy
{
    protected $name    = 'session';
    protected $column  = 'sessions.ID';
    protected $alias   = 'session_id';
    protected $groupBy = 'sessions.ID';
    protected $order   = 'DESC';

    /**
     * Extra columns for session data.
     *
     * @var array
     */
    protected $extraColumns = [
        'sessions.started_at AS session_start',
        'sessions.ended_at AS session_end',
        'sessions.duration AS session_duration',
        'sessions.total_views AS page_count',
    ];

    /**
     * Datetime fields that need UTC to site timezone conversion.
     *
     * @var array
     */
    protected $datetimeFields = ['session_start', 'session_end'];

    /**
     * Columns added by postProcess (not in SQL, but valid for column selection).
     *
     * @var array
     */
    protected $postProcessedColumns = ['session_start_formatted'];

    /**
     * JOINs for session data.
     *
     * Includes visitors join for filtering, plus entry/exit page and referrer info.
     *
     * @var array
     */
    protected $joins = [
        // Visitors join (needed for visitor_hash filter)
        [
            'table' => 'visitors',
            'alias' => 'visitors',
            'on'    => 'sessions.visitor_id = visitors.ID',
            'type'  => 'LEFT',
        ],
        // Entry page joins (via initial_view_id)
        [
            'table' => 'views',
            'alias' => 'entry_view',
            'on'    => 'sessions.initial_view_id = entry_view.ID',
            'type'  => 'LEFT',
        ],
        [
            'table' => 'resource_uris',
            'alias' => 'entry_uri',
            'on'    => 'entry_view.resource_uri_id = entry_uri.ID',
            'type'  => 'LEFT',
        ],
        [
            'table' => 'resources',
            'alias' => 'entry_resource',
            'on'    => 'entry_uri.resource_id = entry_resource.ID',
            'type'  => 'LEFT',
        ],
        // Exit page joins (via last_view_id)
        [
            'table' => 'views',
            'alias' => 'exit_view',
            'on'    => 'sessions.last_view_id = exit_view.ID',
            'type'  => 'LEFT',
        ],
        [
            'table' => 'resource_uris',
            'alias' => 'exit_uri',
            'on'    => 'exit_view.resource_uri_id = exit_uri.ID',
            'type'  => 'LEFT',
        ],
        [
            'table' => 'resources',
            'alias' => 'exit_resource',
            'on'    => 'exit_uri.resource_id = exit_resource.ID',
            'type'  => 'LEFT',
        ],
        // Referrer join
        [
            'table' => 'referrers',
            'alias' => 'referrers',
            'on'    => 'sessions.referrer_id = referrers.ID',
            'type'  => 'LEFT',
        ],
    ];

    /**
     * Get SELECT columns with entry/exit page and referrer info.
     *
     * @param string $attribution      Attribution model (unused for session groupby).
     * @param array  $requestedColumns Optional list of requested column aliases.
     * @return array
     */
    public function getSelectColumns(string $attribution = 'first_touch', array $requestedColumns = []): array
    {
        $columns = [$this->column . ' AS ' . $this->alias];

        // Add base extra columns
        foreach ($this->extraColumns as $extraColumn) {
            if (preg_match('/\sAS\s+(\w+)$/i', $extraColumn, $matches)) {
                $alias = $matches[1];
                if (empty($requestedColumns) || in_array($alias, $requestedColumns, true)) {
                    $columns[] = $extraColumn;
                }
            }
        }

        // Entry page columns
        if (empty($requestedColumns) || in_array('entry_page', $requestedColumns, true)) {
            $columns[] = 'entry_uri.uri AS entry_page';
        }
        if (empty($requestedColumns) || in_array('entry_page_title', $requestedColumns, true)) {
            $columns[] = 'entry_resource.cached_title AS entry_page_title';
        }

        // Exit page columns
        if (empty($requestedColumns) || in_array('exit_page', $requestedColumns, true)) {
            $columns[] = 'exit_uri.uri AS exit_page';
        }
        if (empty($requestedColumns) || in_array('exit_page_title', $requestedColumns, true)) {
            $columns[] = 'exit_resource.cached_title AS exit_page_title';
        }

        // Referrer columns
        if (empty($requestedColumns) || in_array('referrer_domain', $requestedColumns, true)) {
            $columns[] = 'referrers.domain AS referrer_domain';
        }
        if (empty($requestedColumns) || in_array('referrer_name', $requestedColumns, true)) {
            $columns[] = 'referrers.name AS referrer_name';
        }
        if (empty($requestedColumns) || in_array('referrer_channel', $requestedColumns, true)) {
            $columns[] = 'referrers.channel AS referrer_channel';
        }

        return $columns;
    }

    /**
     * Get aliases of extra columns for validation.
     *
     * @return array Array of extra column aliases.
     */
    public function getExtraColumnAliases(): array
    {
        return [
            'session_start',
            'session_end',
            'session_duration',
            'page_count',
            'entry_page',
            'entry_page_title',
            'exit_page',
            'exit_page_title',
            'referrer_domain',
            'referrer_name',
            'referrer_channel',
        ];
    }

    /**
     * Post-process session rows to add formatted datetime fields.
     *
     * @param array $rows Query result rows.
     * @param \wpdb $wpdb WordPress database instance.
     * @return array Processed rows with formatted fields.
     */
    public function postProcess(array $rows, \wpdb $wpdb): array
    {
        // First convert UTC to site timezone
        $rows = $this->convertDatetimeFields($rows);

        // Then add formatted versions
        foreach ($rows as &$row) {
            if (!empty($row['session_start'])) {
                $row['session_start_formatted'] = DateTime::format($row['session_start'], [
                    'include_time' => true,
                    'short_month'  => true,
                ]);
            }
        }

        return $rows;
    }
}
