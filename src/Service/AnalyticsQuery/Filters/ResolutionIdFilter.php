<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Resolution ID filter - filters by resolution ID.
 *
 * @since 15.0.0
 */
class ResolutionIdFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[resolution_id]=... */
    protected $name = 'resolution_id';

    /** @var string SQL column: foreign key ID to resolutions table (for programmatic filtering) */
    protected $column = 'sessions.resolution_id';

    /** @var string Data type: integer for database ID matching */
    protected $type = 'integer';

    /** @var array Supported operators: exact match, exclusion, and set membership */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Resolution ID', 'wp-statistics');
    }
}
