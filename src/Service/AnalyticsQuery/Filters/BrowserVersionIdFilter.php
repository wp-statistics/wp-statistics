<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Browser version ID filter - filters by browser version ID.
 *
 * @since 15.0.0
 */
class BrowserVersionIdFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[browser_version_id]=... */
    protected $name = 'browser_version_id';

    /** @var string SQL column: foreign key ID to device_browser_versions table (for programmatic filtering) */
    protected $column = 'sessions.device_browser_version_id';

    /** @var string Data type: integer for database ID matching */
    protected $type = 'integer';

    /** @var array Supported operators: exact match, exclusion, and set membership */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Browser Version ID', 'wp-statistics');
    }
}
