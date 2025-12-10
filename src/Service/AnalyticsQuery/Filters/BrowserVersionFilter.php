<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Browser version filter - filters by browser version.
 *
 * @since 15.0.0
 */
class BrowserVersionFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[browser_version]=... */
    protected $name = 'browser_version';

    /** @var string SQL column: version string from device_browser_versions table (e.g., 120.0.0, 119.0) */
    protected $column = 'device_browser_versions.version';

    /** @var string Data type: string for version matching and partial searches */
    protected $type = 'string';

    /**
     * Required JOIN: sessions -> device_browser_versions.
     * Links session's browser version ID to the version lookup table.
     *
     * @var array
     */
    protected $joins = [
        'table' => 'device_browser_versions',
        'alias' => 'device_browser_versions',
        'on'    => 'sessions.device_browser_version_id = device_browser_versions.ID',
    ];

    /** @var array Supported operators: exact match, exclusion, set membership, and partial text matching */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'contains', 'starts_with', 'ends_with'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Browser Version', 'wp-statistics');
    }
}
