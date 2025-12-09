<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Device type filter - filters by device type name.
 *
 * @since 15.0.0
 */
class DeviceTypeFilter extends AbstractFilter
{
    protected $name   = 'device_type';
    protected $column = 'device_types.name';
    protected $type   = 'string';
    protected $joins  = [
        'table' => 'device_types',
        'alias' => 'device_types',
        'on'    => 'sessions.device_type_id = device_types.ID',
    ];

    protected $inputType = 'dropdown';
    protected $options   = [
        ['value' => 'desktop', 'label' => 'Desktop'],
        ['value' => 'mobile', 'label' => 'Mobile'],
        ['value' => 'tablet', 'label' => 'Tablet'],
    ];
    protected $supportedOperators = ['is', 'is_not'];
    protected $pages              = [
        'visitors-overview',
        'visitors',
        'online-visitors',
        'top-visitors',
        'views',
        'geographic',
    ];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Device Type', 'wp-statistics');
    }
}
