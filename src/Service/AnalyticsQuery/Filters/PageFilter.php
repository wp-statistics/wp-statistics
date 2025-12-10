<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Page filter - filters by page URI.
 *
 * @since 15.0.0
 */
class PageFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[page]=... */
    protected $name = 'page';

    /** @var string SQL column: page URI/path from resource_uris table (e.g., /about, /contact) */
    protected $column = 'resource_uris.uri';

    /** @var string Data type: string for URI matching */
    protected $type = 'string';

    /** @var string Required base table: needs views table to access page URIs */
    protected $requirement = 'views';

    /**
     * Required JOIN: views -> resource_uris.
     * Links view's resource URI ID to the URI path lookup table.
     *
     * @var array
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
