<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Page filter - filters by page URI.
 *
 * @since 15.0.0
 */
class PageFilter extends AbstractFilter
{
    protected $name        = 'page';
    protected $column      = 'resource_uris.uri';
    protected $type        = 'string';
    protected $requirement = 'views';
    protected $joins       = [
        'table' => 'resource_uris',
        'alias' => 'resource_uris',
        'on'    => 'views.resource_uri_id = resource_uris.ID',
    ];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Page', 'wp-statistics');
    }
}
