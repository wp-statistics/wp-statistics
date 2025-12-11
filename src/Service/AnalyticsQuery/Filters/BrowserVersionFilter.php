<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Browser version filter - filters by browser version.
 *
 * @since 15.0.0
 */
class BrowserVersionFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[browser_version]=...
     */
    protected $name = 'browser_version';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: device_browser_versions.version
     */
    protected $column = 'device_browser_versions.version';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: string
     */
    protected $type = 'string';

    /**
     * Required JOINs to access the column.
     *
     * @var array JOIN: sessions -> device_browser_versions
     */
    protected $joins = [
        'table' => 'device_browser_versions',
        'alias' => 'device_browser_versions',
        'on'    => 'sessions.device_browser_version_id = device_browser_versions.ID',
    ];

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: is, is_not, in, not_in, contains, starts_with, ends_with
     */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'contains', 'starts_with', 'ends_with'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Browser Version', 'wp-statistics');
    }
}
