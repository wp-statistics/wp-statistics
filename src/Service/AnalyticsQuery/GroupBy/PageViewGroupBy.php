<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Page view group by - returns individual page view rows.
 *
 * Used for session page views subtables where each row is one view.
 * Unlike other group by classes that aggregate data, this returns raw page view rows.
 *
 * @since 15.0.0
 */
class PageViewGroupBy extends AbstractGroupBy
{
    protected $name    = 'page_view';
    protected $column  = 'views.ID';
    protected $alias   = 'view_id';
    protected $groupBy = 'views.ID';
    protected $order   = 'ASC';

    /**
     * Extra columns for page view data.
     *
     * @var array
     */
    protected $extraColumns = [
        'resource_uris.uri AS page_uri',
        'resources.cached_title AS page_title',
        'ROUND(views.duration / 1000) AS time_on_page',
        'views.viewed_at AS timestamp',
    ];

    /**
     * Datetime fields that need UTC to site timezone conversion.
     *
     * @var array
     */
    protected $datetimeFields = ['timestamp'];

    /**
     * JOINs for page view data.
     *
     * @var array
     */
    protected $joins = [
        [
            'table' => 'resource_uris',
            'alias' => 'resource_uris',
            'on'    => 'views.resource_uri_id = resource_uris.ID',
            'type'  => 'LEFT',
        ],
        [
            'table' => 'resources',
            'alias' => 'resources',
            'on'    => 'resource_uris.resource_id = resources.ID',
            'type'  => 'LEFT',
        ],
    ];

    /**
     * Requires views table as base.
     *
     * @var string
     */
    protected $requirement = 'views';

    /**
     * Get aliases of extra columns for validation.
     *
     * @return array Array of extra column aliases.
     */
    public function getExtraColumnAliases(): array
    {
        return ['page_uri', 'page_title', 'time_on_page', 'timestamp'];
    }
}
