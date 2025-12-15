<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Device type group by - groups by device type.
 *
 * @since 15.0.0
 */
class DeviceTypeGroupBy extends AbstractGroupBy
{
    protected $name         = 'device_type';
    protected $column       = 'device_types.name';
    protected $alias        = 'device_type_name';
    protected $extraColumns = [
        'device_types.ID AS device_type_id',
    ];
    protected $joins        = [
        'table' => 'device_types',
        'alias' => 'device_types',
        'on'    => 'sessions.device_type_id = device_types.ID',
        'type'  => 'INNER',
    ];
    protected $groupBy      = 'device_types.ID';
}
