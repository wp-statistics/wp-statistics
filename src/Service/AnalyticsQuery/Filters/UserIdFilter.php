<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * User ID filter - filters by WordPress user ID.
 *
 * @since 15.0.0
 */
class UserIdFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[user_id]=...
     */
    protected $name = 'user_id';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: sessions.user_id
     */
    protected $column = 'sessions.user_id';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: integer (WordPress user ID)
     */
    protected $type = 'integer';

    /**
     * UI input component type.
     *
     * @var string Input type: searchable
     */
    protected $inputType = 'searchable';

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
    protected $groups = ['visitors', 'views'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('User', 'wp-statistics');
    }

    /**
     * Search user options via AJAX.
     *
     * @param string $search Search term.
     * @param int    $limit  Maximum results.
     * @return array Array of options with 'value' (user ID) and 'label' (Display Name (username)).
     */
    public function searchOptions(string $search = '', int $limit = 20): array
    {
        $args = [
            'number'  => $limit,
            'orderby' => 'display_name',
            'order'   => 'ASC',
        ];

        if (!empty($search)) {
            $args['search']         = '*' . $search . '*';
            $args['search_columns'] = ['ID', 'user_login', 'user_email', 'display_name'];
        }

        $users = get_users($args);

        if (empty($users)) {
            return [];
        }

        $options = [];
        foreach ($users as $user) {
            $options[] = [
                'value' => $user->ID,
                'label' => sprintf('%s (%s)', $user->display_name, $user->user_login),
            ];
        }

        return $options;
    }
}
