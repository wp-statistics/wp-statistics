<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Resolution filter - filters by screen resolution.
 *
 * @since 15.0.0
 */
class ResolutionFilter extends AbstractFilter
{
    protected $name   = 'resolution';
    protected $column = 'CONCAT(resolutions.width, \'x\', resolutions.height)';
    protected $type   = 'string';
    protected $joins  = [
        'table' => 'resolutions',
        'alias' => 'resolutions',
        'on'    => 'sessions.resolution_id = resolutions.ID',
    ];
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Resolution', 'wp-statistics');
    }
}
