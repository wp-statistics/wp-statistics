<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Device type filter - filters by device type name.
 *
 * @since 15.0.0
 */
class DeviceTypeFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[device_type]=...
     */
    protected $name = 'device_type';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: device_types.name
     */
    protected $column = 'device_types.name';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: string
     */
    protected $type = 'string';

    /**
     * Required JOINs to access the column.
     *
     * @var array JOIN: sessions -> device_types
     */
    protected $joins = [
        'table' => 'device_types',
        'alias' => 'device_types',
        'on'    => 'sessions.device_type_id = device_types.ID',
    ];

    /**
     * UI input component type.
     *
     * @var string Input type: dropdown
     */
    protected $inputType = 'dropdown';

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: is, is_not
     */
    protected $supportedOperators = ['is', 'is_not'];

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: visitors
     */
    protected $groups = ['visitors'];

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
