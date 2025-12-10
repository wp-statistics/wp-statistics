<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * User role filter - filters by WordPress user role.
 *
 * @since 15.0.0
 */
class UserRoleFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[user_role]=... */
    protected $name = 'user_role';

    /** @var string SQL column: WordPress user role from users table (e.g., administrator, editor, subscriber) */
    protected $column = 'users.role';

    /** @var string Data type: string for role name matching */
    protected $type = 'string';

    /**
     * Required JOIN: sessions -> users.
     * Links session's user ID to the WordPress users table for role lookup.
     *
     * @var array
     */
    protected $joins = [
        'table' => 'users',
        'alias' => 'users',
        'on'    => 'sessions.user_id = users.ID',
    ];

    /** @var string UI component: dropdown with dynamic WordPress roles */
    protected $inputType = 'dropdown';

    /** @var array Supported operators: exact match and exclusion */
    protected $supportedOperators = ['is', 'is_not'];

    /** @var array Available on: visitors page for user segmentation */
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
