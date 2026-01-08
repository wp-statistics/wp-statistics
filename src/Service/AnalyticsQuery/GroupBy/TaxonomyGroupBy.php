<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Taxonomy group by - groups by taxonomy term (category, tag, custom taxonomy).
 *
 * Uses cached_terms field from resources table which stores comma-separated term IDs.
 * Joins with WordPress term tables to get term names and filter by taxonomy type.
 *
 * Usage:
 * - group_by: ['taxonomy']
 * - filters: ['taxonomy_type' => 'category'] or ['taxonomy_type' => 'post_tag']
 *
 * @since 15.0.0
 */
class TaxonomyGroupBy extends AbstractGroupBy
{
    protected $name         = 'taxonomy';
    protected $column       = 'terms.term_id';
    protected $alias        = 'term_id';
    protected $extraColumns = [
        'terms.name AS term_name',
        'terms.slug AS term_slug',
        'term_taxonomy.taxonomy AS taxonomy_type',
    ];

    /**
     * Columns added by postProcess (not in SQL, but valid for column selection).
     *
     * @var array
     */
    protected $postProcessedColumns = [
        'taxonomy_label',
        'term_link',
        'term_description',
        'term_count',
    ];
    protected $joins        = [
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
    protected $groupBy      = 'terms.term_id';
    protected $filter       = 'resources.cached_terms IS NOT NULL AND resources.cached_terms != \'\'';
    protected $requirement  = 'views';

    /**
     * Default taxonomy type to filter by.
     *
     * @var string
     */
    protected $taxonomyType = 'category';

    /**
     * Get JOINs including WordPress term tables.
     *
     * @return array
     */
    public function getJoins(): array
    {
        global $wpdb;

        $joins = parent::getJoins();

        // Add WordPress term tables joins
        // We use FIND_IN_SET to match term IDs in the cached_terms comma-separated list
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
     * Get filter including taxonomy type restriction.
     *
     * @return string|null
     */
    public function getFilter(): ?string
    {
        $baseFilter = parent::getFilter();

        // Add taxonomy type filter (default: category)
        // This can be overridden via request filters['taxonomy_type']
        return $baseFilter . " AND term_taxonomy.taxonomy = '{$this->taxonomyType}'";
    }

    /**
     * Set taxonomy type to filter by.
     *
     * @param string $taxonomyType Taxonomy name (e.g., 'category', 'post_tag').
     * @return self
     */
    public function setTaxonomyType(string $taxonomyType): self
    {
        $this->taxonomyType = $taxonomyType;
        return $this;
    }

    /**
     * Post-process rows to add term link, count, and taxonomy label.
     *
     * @param array $rows Query result rows.
     * @param \wpdb $wpdb WordPress database object.
     * @return array Processed rows with additional term data.
     */
    public function postProcess(array $rows, \wpdb $wpdb): array
    {
        foreach ($rows as &$row) {
            if (!empty($row['term_id']) && !empty($row['taxonomy_type'])) {
                // Add term link
                $row['term_link'] = get_term_link((int) $row['term_id'], $row['taxonomy_type']);
                if (is_wp_error($row['term_link'])) {
                    $row['term_link'] = '';
                }

                // Add taxonomy label (human-readable name)
                $taxonomyObject = get_taxonomy($row['taxonomy_type']);
                if ($taxonomyObject) {
                    $row['taxonomy_label'] = $taxonomyObject->labels->singular_name;
                } else {
                    $row['taxonomy_label'] = $row['taxonomy_type'];
                }

                // Add term description and count
                $term = get_term((int) $row['term_id'], $row['taxonomy_type']);
                if ($term && !is_wp_error($term)) {
                    $row['term_description'] = $term->description;
                    $row['term_count']       = $term->count;
                }
            }
        }

        return $rows;
    }
}
