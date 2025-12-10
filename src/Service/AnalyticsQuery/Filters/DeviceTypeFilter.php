<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Device type filter - filters by device type name.
 *
 * @since 15.0.0
 */
class DeviceTypeFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[device_type]=... */
    protected $name = 'device_type';

    /** @var string SQL column: device type name from device_types table (desktop, mobile, tablet) */
    protected $column = 'device_types.name';

    /** @var string Data type: string for device type matching */
    protected $type = 'string';

    /**
     * Required JOIN: sessions -> device_types.
     * Links session's device type ID to the device type lookup table.
     *
     * @var array
     */
    protected $joins = [
        'table' => 'device_types',
        'alias' => 'device_types',
        'on'    => 'sessions.device_type_id = device_types.ID',
    ];

    /** @var string UI component: dropdown with predefined device types */
    protected $inputType = 'dropdown';

    /** @var array Supported operators: exact match and exclusion */
    protected $supportedOperators = ['is', 'is_not'];

    /** @var array Available on: visitors page for device analysis */
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
