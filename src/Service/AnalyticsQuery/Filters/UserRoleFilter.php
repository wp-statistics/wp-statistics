<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * User role filter - filters by WordPress user role.
 *
 * @since 15.0.0
 */
class UserRoleFilter extends AbstractFilter
{
    protected $name   = 'user_role';
    protected $column = 'users.role';
    protected $type   = 'string';
    protected $joins  = [
        'table' => 'users',
        'alias' => 'users',
        'on'    => 'sessions.user_id = users.ID',
    ];

    protected $inputType          = 'dropdown';
    protected $supportedOperators = ['is', 'is_not'];
    protected $groups             = ['visitors'];

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
