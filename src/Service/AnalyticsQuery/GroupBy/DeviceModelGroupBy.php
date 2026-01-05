<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Device model group by - groups by device model (e.g., iPhone, Galaxy S10).
 *
 * @since 15.0.0
 */
class DeviceModelGroupBy extends AbstractGroupBy
{
    protected $name         = 'device_model';
    protected $column       = 'device_models.name';
    protected $alias        = 'device_model';
    protected $extraColumns = [
        'device_models.ID AS device_model_id',
    ];
    protected $joins        = [
        'table' => 'device_models',
        'alias' => 'device_models',
        'on'    => 'sessions.device_model_id = device_models.ID',
        'type'  => 'INNER',
    ];
    protected $groupBy      = 'device_models.ID';
}
