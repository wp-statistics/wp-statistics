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

    protected $inputType          = 'dropdown';
    protected $supportedOperators = ['is', 'is_not'];
    protected $groups             = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Device Type', 'wp-statistics');
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(): ?array
    {
        return [
            ['value' => 'desktop', 'label' => esc_html__('Desktop', 'wp-statistics')],
            ['value' => 'mobile', 'label' => esc_html__('Mobile', 'wp-statistics')],
            ['value' => 'tablet', 'label' => esc_html__('Tablet', 'wp-statistics')],
        ];
    }
}
