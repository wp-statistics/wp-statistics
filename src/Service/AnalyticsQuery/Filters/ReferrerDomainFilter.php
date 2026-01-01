<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Referrer domain filter - filters by referrer domain.
 *
 * @since 15.0.0
 */
class ReferrerDomainFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[referrer_domain]=...
     */
    protected $name = 'referrer_domain';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: referrers.domain
     */
    protected $column = 'referrers.domain';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: string
     */
    protected $type = 'string';

    /**
     * Required JOINs to access the column.
     *
     * @var array JOIN: sessions -> referrers
     */
    protected $joins = [
        'table' => 'referrers',
        'alias' => 'referrers',
        'on'    => 'sessions.referrer_id = referrers.ID',
    ];

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: is, is_not, in, not_in, contains
     */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'contains'];

    /**
     * UI input component type.
     *
     * @var string Input type: searchable
     */
    protected $inputType = 'searchable';

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: visitors
     */
    protected $groups = ['visitors', 'views'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Referrer Domain', 'wp-statistics');
    }

    /**
     * Search referrer domain options via AJAX.
     *
     * @param string $search Search term.
     * @param int    $limit  Maximum results.
     * @return array Array of options with 'value' and 'label'.
     */
    public function searchOptions(string $search = '', int $limit = 20): array
    {
        global $wpdb;

        $table = $wpdb->prefix . 'statistics_referrers';

        $sql = "SELECT DISTINCT domain as value, domain as label FROM {$table} WHERE domain IS NOT NULL AND domain != ''";

        if (!empty($search)) {
            $sql .= $wpdb->prepare(" AND domain LIKE %s", '%' . $wpdb->esc_like($search) . '%');
        }

        $sql .= " ORDER BY domain ASC LIMIT %d";
        $sql  = $wpdb->prepare($sql, $limit);

        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results ?: [];
    }
}
