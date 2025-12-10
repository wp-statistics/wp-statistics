<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Referrer domain filter - filters by referrer domain.
 *
 * @since 15.0.0
 */
class ReferrerDomainFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[referrer_domain]=... */
    protected $name = 'referrer_domain';

    /** @var string SQL column: referring domain from referrers table (e.g., google.com, twitter.com) */
    protected $column = 'referrers.domain';

    /** @var string Data type: string for domain matching */
    protected $type = 'string';

    /**
     * Required JOIN: sessions -> referrers.
     * Links session's referrer ID to the referrer domain lookup table.
     *
     * @var array
     */
    protected $joins = [
        'table' => 'referrers',
        'alias' => 'referrers',
        'on'    => 'sessions.referrer_id = referrers.ID',
    ];

    /** @var array Supported operators: exact match, exclusion, and partial domain matching */
    protected $supportedOperators = ['is', 'is_not', 'contains'];

    /** @var string UI component: searchable autocomplete for domain list */
    protected $inputType = 'searchable';

    /** @var array Available on: visitors page for referrer analysis */
    protected $groups = ['visitors'];

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
