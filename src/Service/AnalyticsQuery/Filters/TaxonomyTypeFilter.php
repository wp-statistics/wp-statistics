<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Taxonomy type filter - filters by taxonomy type (category, post_tag, custom taxonomy).
 *
 * This filter is used to restrict results to a specific taxonomy type when
 * querying taxonomy-related data in the Categories analytics page.
 *
 * @since 15.0.0
 */
class TaxonomyTypeFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[taxonomy_type]=...
     */
    protected $name = 'taxonomy_type';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: term_taxonomy.taxonomy
     */
    protected $column = 'term_taxonomy.taxonomy';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: string
     */
    protected $type = 'string';

    /**
     * Required base table to enable this filter.
     *
     * @var string|null Table name: views
     */
    protected $requirement = 'views';

    /**
     * Required JOINs to access the column.
     *
     * @var array JOIN chain: views -> resource_uris -> resources -> term_taxonomy -> terms
     */
    protected $joins = [
        [
            'table' => 'resource_uris',
            'alias' => 'resource_uris',
            'on'    => 'views.resource_uri_id = resource_uris.ID',
        ],
        [
            'table' => 'resources',
            'alias' => 'resources',
            'on'    => 'resource_uris.resource_id = resources.ID AND resources.is_deleted = 0',
            'type'  => 'LEFT',
        ],
    ];

    /**
     * UI input component type.
     *
     * @var string Input type: dropdown
     */
    protected $inputType = 'dropdown';

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: is, is_not, in, not_in
     */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: individual content, categories
     */
    protected $groups = [
        'individual-content',
        'categories',
    ];

    /**
     * Get JOINs including WordPress term tables.
     *
     * @return array|null
     */
    public function getJoins(): ?array
    {
        global $wpdb;

        $joins = parent::getJoins() ?: [];

        // Add WordPress term_taxonomy table join
        // Uses FIND_IN_SET to match term IDs in the cached_terms comma-separated list
        $joins[] = [
            'table'    => $wpdb->term_taxonomy,
            'alias'    => 'term_taxonomy',
            'on'       => "FIND_IN_SET(term_taxonomy.term_id, REPLACE(resources.cached_terms, ' ', '')) > 0",
            'type'     => 'INNER',
            'external' => true, // Flag to indicate this is a WordPress table, not statistics table
        ];

        return $joins;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return \esc_html__('Taxonomy Type', 'wp-statistics');
    }

    /**
     * Get options dynamically from WordPress taxonomies.
     *
     * @return array Array of options with 'value' and 'label'.
     */
    public function getOptions(): array
    {
        $taxonomies = \get_taxonomies(['public' => true], 'objects');
        $options    = [];

        foreach ($taxonomies as $taxonomy) {
            $options[] = [
                'value' => $taxonomy->name,
                'label' => $taxonomy->labels->singular_name,
            ];
        }

        return $options;
    }
}
