<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Resolution group by - groups by screen resolution.
 *
 * @since 15.0.0
 */
class ResolutionGroupBy extends AbstractGroupBy
{
    protected $name         = 'resolution';
    protected $column       = "CONCAT(resolutions.width, 'x', resolutions.height)";
    protected $alias        = 'resolution';
    protected $extraColumns = [
        'resolutions.ID AS resolution_id',
        'resolutions.width AS resolution_width',
        'resolutions.height AS resolution_height',
    ];
    protected $joins        = [
        'table' => 'resolutions',
        'alias' => 'resolutions',
        'on'    => 'sessions.resolution_id = resolutions.ID',
        'type'  => 'INNER',
    ];
    protected $groupBy      = 'resolutions.ID';
}
