<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Taxonomy filter - filters by taxonomy term ID.
 *
 * This filter is used to filter results to a specific taxonomy term (category, tag, etc.)
 * when viewing individual term analytics.
 *
 * Uses cached_terms field from resources table which stores comma-separated term IDs.
 * Joins with WordPress term tables to filter by specific term_id.
 *
 * @since 15.0.0
 */
class TaxonomyFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[taxonomy]=...
     */
    protected $name = 'taxonomy';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: terms.term_id
     */
    protected $column = 'terms.term_id';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: integer (term_id)
     */
    protected $type = 'integer';

    /**
     * Required base table to enable this filter.
     *
     * @var string|null Table name: views
     */
    protected $requirement = 'views';

    /**
     * Required JOINs to access the column.
     *
     * @var array JOIN chain: views -> resource_uris -> resources
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
            'on'    => 'resource_uris.resource_id = resources.ID',
            'type'  => 'LEFT',
        ],
    ];

    /**
     * UI input component type.
     *
     * @var string Input type: searchable
     */
    protected $inputType = 'searchable';

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: is, is_not, in, not_in
     */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: individual-category, categories
     */
    protected $groups = [
        'individual-category',
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

        $joins[] = [
            'table'    => $wpdb->terms,
            'alias'    => 'terms',
            'on'       => 'term_taxonomy.term_id = terms.term_id',
            'type'     => 'INNER',
            'external' => true,
        ];

        return $joins;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return \esc_html__('Taxonomy Term', 'wp-statistics');
    }

    /**
     * Search taxonomy term options via AJAX.
     *
     * @param string $search Search term.
     * @param int    $limit  Maximum results.
     * @return array Array of options with 'value' (term_id) and 'label' (Term Name (Taxonomy)).
     */
    public function searchOptions(string $search = '', int $limit = 20): array
    {
        global $wpdb;

        $sql = "SELECT t.term_id as value, t.name, tt.taxonomy
                FROM {$wpdb->terms} t
                INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
                WHERE tt.taxonomy IN (
                    SELECT name FROM (
                        SELECT name FROM {$wpdb->prefix}terms
                        WHERE 1=0
                        UNION ALL
                        SELECT 'category' AS name
                        UNION ALL
                        SELECT 'post_tag' AS name
                    ) AS public_taxonomies
                )";

        // Get public taxonomies dynamically
        $publicTaxonomies = \get_taxonomies(['public' => true], 'names');
        if (!empty($publicTaxonomies)) {
            $taxonomyPlaceholders = implode(',', array_fill(0, count($publicTaxonomies), '%s'));
            $sql = $wpdb->prepare(
                "SELECT t.term_id as value, t.name, tt.taxonomy
                FROM {$wpdb->terms} t
                INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
                WHERE tt.taxonomy IN ($taxonomyPlaceholders)",
                ...$publicTaxonomies
            );
        }

        if (!empty($search)) {
            $searchLike = '%' . $wpdb->esc_like($search) . '%';
            $sql       .= $wpdb->prepare(
                " AND t.name LIKE %s",
                $searchLike
            );
        }

        $sql .= $wpdb->prepare(" ORDER BY t.name ASC LIMIT %d", $limit);

        $results = $wpdb->get_results($sql, ARRAY_A);

        if (empty($results)) {
            return [];
        }

        $options = [];
        foreach ($results as $row) {
            $taxonomyLabel = $this->getTaxonomyLabel($row['taxonomy']);

            $options[] = [
                'value' => $row['value'],
                'label' => sprintf('%s (%s)', $row['name'], $taxonomyLabel),
            ];
        }

        return $options;
    }

    /**
     * Get human-readable label for taxonomy type.
     *
     * @param string $taxonomy Taxonomy name.
     * @return string Taxonomy label.
     */
    private function getTaxonomyLabel(string $taxonomy): string
    {
        $taxonomyObject = \get_taxonomy($taxonomy);
        if ($taxonomyObject) {
            return $taxonomyObject->labels->singular_name;
        }

        return $taxonomy;
    }
}
