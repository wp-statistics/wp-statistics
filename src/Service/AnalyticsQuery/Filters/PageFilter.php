<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Page filter - filters by page URI.
 *
 * @since 15.0.0
 */
class PageFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[page]=...
     */
    protected $name = 'page';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: resource_uris.uri
     */
    protected $column = 'resource_uris.uri';

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
     * @var array JOIN: views -> resource_uris
     */
    protected $joins = [
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
