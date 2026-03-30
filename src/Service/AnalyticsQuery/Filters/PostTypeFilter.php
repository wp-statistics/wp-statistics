<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Post type filter - filters by resource/post type.
 *
 * @since 15.0.0
 */
class PostTypeFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[post_type]=...
     */
    protected $name = 'post_type';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: resources.resource_type
     */
    protected $column = 'resources.resource_type';

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
     * The resources JOIN includes is_deleted = 0 to exclude deleted content.
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
            'on'    => 'resource_uris.resource_id = resources.ID AND resources.is_deleted = 0',
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
     * @var array Groups: views, content
     */
    protected $groups = [
        'views',
        'content',
    ];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return \esc_html__('Post Type', 'wp-statistics');
    }

    /**
     * Get options dynamically from WordPress post types.
     *
     * @return array Array of options with 'value' and 'label'.
     */
    public function getOptions(): array
    {
        $postTypes = \get_post_types(['public' => true], 'objects');
        $options   = [];

        foreach ($postTypes as $postType) {
            $options[] = [
                'value' => $postType->name,
                'label' => $postType->labels->singular_name,
            ];
        }

        return $options;
    }
}
