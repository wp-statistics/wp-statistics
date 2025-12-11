<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Referrer filter - filters by referrer domain.
 *
 * @since 15.0.0
 */
class ReferrerFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[referrer]=... */
    protected $name = 'referrer';

    /** @var string SQL column: referrer domain from referrers table (e.g., google.com, facebook.com) */
    protected $column = 'referrers.domain';

    /** @var string Data type: string for domain matching */
    protected $type = 'string';

    /**
     * Required JOIN: sessions -> referrers.
     * Links session's referrer ID to the referrer details lookup table.
     *
     * @var array
     */
    protected $joins = [
        'table' => 'referrers',
        'alias' => 'referrers',
        'on'    => 'sessions.referrer_id = referrers.ID',
    ];

    /** @var string UI component: searchable autocomplete with referrer domains */
    protected $inputType = 'searchable';

    /** @var array Supported operators: exact match, exclusion, and pattern matching */
    protected $supportedOperators = ['is', 'is_not', 'contains'];

    /** @var array Available on: visitors page for referrer analysis */
    protected $groups = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Referrer', 'wp-statistics');
    }

    /**
     * Search referrer options via AJAX.
     *
     * @param string $search Search term.
     * @param int    $limit  Maximum results.
     * @return array Array of options with 'value' and 'label'.
     */
    public function searchOptions(string $search = '', int $limit = 20): array
    {
        global $wpdb;

        $table = $wpdb->prefix . 'statistics_referrers';

        $sql = "SELECT domain as value, domain as label FROM {$table}";

        if (!empty($search)) {
            $sql .= $wpdb->prepare(" WHERE domain LIKE %s", '%' . $wpdb->esc_like($search) . '%');
        }

        $sql .= " ORDER BY domain ASC LIMIT %d";
        $sql  = $wpdb->prepare($sql, $limit);

        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results ?: [];
    }
}
