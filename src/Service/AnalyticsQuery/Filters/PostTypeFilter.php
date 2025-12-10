<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Post type filter - filters by resource/post type.
 *
 * @since 15.0.0
 */
class PostTypeFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[post_type]=... */
    protected $name = 'post_type';

    /** @var string SQL column: WordPress post type from resources table (e.g., post, page, product) */
    protected $column = 'resources.resource_type';

    /** @var string Data type: string for post type matching */
    protected $type = 'string';

    /** @var string Required base table: needs views table to access resource data */
    protected $requirement = 'views';

    /**
     * Required JOINs: views -> resource_uris -> resources chain.
     * Needed to access the post type stored in the resources table.
     *
     * @var array
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
        ],
    ];

    /** @var string UI component: dropdown with dynamic WordPress post types */
    protected $inputType = 'dropdown';

    /** @var array Supported operators: exact match and exclusion */
    protected $supportedOperators = ['is', 'is_not'];

    /** @var array Available on: views page for content type analysis */
    protected $groups = [
        'views',
    ];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Post Type', 'wp-statistics');
    }

    /**
     * Get options dynamically from WordPress post types.
     *
     * @return array Array of options with 'value' and 'label'.
     */
    public function getOptions(): array
    {
        $postTypes = get_post_types(['public' => true], 'objects');
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
