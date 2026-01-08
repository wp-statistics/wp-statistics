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
     * @var string Column path: device_types.ID
     */
    protected $column = 'device_types.ID';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: integer
     */
    protected $type = 'integer';

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
     * @var array Operators: is, is_not, in, not_in
     */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: visitors
     */
    protected $groups = ['visitors', 'views', 'individual-content'];

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
        global $wpdb;

        $table = $wpdb->prefix . 'statistics_device_types';
        $results = $wpdb->get_results("SELECT ID as value, name as label FROM {$table} ORDER BY name ASC", ARRAY_A);

        return $results ?: [];
    }
}
