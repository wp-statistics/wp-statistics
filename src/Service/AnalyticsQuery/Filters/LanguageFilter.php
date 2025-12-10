<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Language filter - filters by language code.
 *
 * @since 15.0.0
 */
class LanguageFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[language]=... */
    protected $name = 'language';

    /** @var string SQL column: language code from languages table (e.g., en, fr, de, es) */
    protected $column = 'languages.code';

    /** @var string Data type: string for language code matching */
    protected $type = 'string';

    /**
     * Required JOIN: sessions -> languages.
     * Links session's language ID to the language details lookup table.
     *
     * @var array
     */
    protected $joins = [
        'table' => 'languages',
        'alias' => 'languages',
        'on'    => 'sessions.language_id = languages.ID',
    ];

    /** @var string UI component: searchable autocomplete with language names and codes */
    protected $inputType = 'searchable';

    /** @var array Supported operators: exact match and exclusion */
    protected $supportedOperators = ['is', 'is_not'];

    /** @var array Available on: visitors page for localization analysis */
    protected $groups = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Language', 'wp-statistics');
    }

    /**
     * Search language options via AJAX.
     *
     * @param string $search Search term.
     * @param int    $limit  Maximum results.
     * @return array Array of options with 'value' and 'label'.
     */
    public function searchOptions(string $search = '', int $limit = 20): array
    {
        global $wpdb;

        $table = $wpdb->prefix . 'statistics_languages';

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
