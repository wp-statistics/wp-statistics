<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Post type filter - filters by resource/post type.
 *
 * @since 15.0.0
 */
class PostTypeFilter extends AbstractFilter
{
    protected $name        = 'post_type';
    protected $column      = 'resources.resource_type';
    protected $type        = 'string';
    protected $requirement = 'views';
    protected $joins       = [
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

    protected $inputType          = 'dropdown';
    protected $supportedOperators = ['is', 'is_not'];
    protected $pages              = [
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
