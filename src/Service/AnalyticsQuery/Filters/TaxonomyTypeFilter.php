<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Filter by taxonomy type (category, post_tag, custom taxonomies).
 *
 * Used with TaxonomyGroupBy to restrict results to a specific taxonomy.
 *
 * @since 15.0.0
 */
class TaxonomyTypeFilter extends AbstractFilter
{
    /**
     * Filter name/identifier.
     *
     * @var string
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
     * @var string
     */
    protected $type = 'string';

    /**
     * Input type for UI.
     *
     * @var string
     */
    protected $inputType = 'dropdown';

    /**
     * Supported operators for this filter.
     *
     * @var array
     */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];

    /**
     * Get the human-readable label for the filter.
     *
     * @return string
     */
    public function getLabel(): string
    {
        return esc_html__('Taxonomy Type', 'wp-statistics');
    }

    /**
     * Get available taxonomy options for UI.
     *
     * @return array|null Array of taxonomy options with value and label.
     */
    public function getOptions(): ?array
    {
        $taxonomies = get_taxonomies(['public' => true], 'objects');
        $options = [];

        foreach ($taxonomies as $taxonomy) {
            $options[] = [
                'value' => $taxonomy->name,
                'label' => $taxonomy->labels->name,
            ];
        }

        return $options;
    }
}
