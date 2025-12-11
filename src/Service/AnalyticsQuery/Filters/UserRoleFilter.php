<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * User role filter - filters by WordPress user role.
 *
 * @since 15.0.0
 */
class UserRoleFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[user_role]=...
     */
    protected $name = 'user_role';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: users.role
     */
    protected $column = 'users.role';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: string
     */
    protected $type = 'string';

    /**
     * Required JOINs to access the column.
     *
     * @var array JOIN: sessions -> users
     */
    protected $joins = [
        'table' => 'users',
        'alias' => 'users',
        'on'    => 'sessions.user_id = users.ID',
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
        return esc_html__('User Role', 'wp-statistics');
    }

    /**
     * Get WordPress user roles as options.
     *
     * @return array|null
     */
    public function getOptions(): ?array
    {
        global $wp_roles;

        if (!isset($wp_roles)) {
            $wp_roles = new \WP_Roles();
        }

        $options = [];
        foreach ($wp_roles->get_names() as $role => $name) {
            $options[] = [
                'value' => $role,
                'label' => translate_user_role($name),
            ];
        }

        return $options;
    }
}
