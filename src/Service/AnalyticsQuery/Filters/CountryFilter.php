<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Country filter - filters by country code.
 *
 * @since 15.0.0
 */
class CountryFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[country]=... */
    protected $name = 'country';

    /** @var string SQL column: ISO country code from countries table (e.g., US, GB, DE, FR) */
    protected $column = 'countries.code';

    /** @var string Data type: string for country code matching */
    protected $type = 'string';

    /**
     * Required JOIN: sessions -> countries.
     * Links session's country ID to the country details lookup table.
     *
     * @var array
     */
    protected $joins = [
        'table' => 'countries',
        'alias' => 'countries',
        'on'    => 'sessions.country_id = countries.ID',
    ];

    /** @var string UI component: searchable autocomplete with country names and codes */
    protected $inputType = 'searchable';

    /** @var array Supported operators: exact match and exclusion */
    protected $supportedOperators = ['is', 'is_not'];

    /** @var array Available on: visitors page for geographic analysis */
    protected $groups = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Country', 'wp-statistics');
    }

    /**
     * Search country options via AJAX.
     *
     * @param string $search Search term.
     * @param int    $limit  Maximum results.
     * @return array Array of options with 'value' and 'label'.
     */
    public function searchOptions(string $search = '', int $limit = 20): array
    {
        global $wpdb;

        $table = $wpdb->prefix . 'statistics_countries';

        $sql = "SELECT code as value, name as label FROM {$table}";

        if (!empty($search)) {
            $sql .= $wpdb->prepare(" WHERE name LIKE %s OR code LIKE %s", '%' . $wpdb->esc_like($search) . '%', '%' . $wpdb->esc_like($search) . '%');
        }

        $sql .= " ORDER BY name ASC LIMIT %d";
        $sql  = $wpdb->prepare($sql, $limit);

        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results ?: [];
    }
}
