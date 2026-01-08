<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Author filter - filters by post author.
 *
 * @since 15.0.0
 */
class AuthorFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[author]=...
     */
    protected $name = 'author';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: resources.cached_author_id
     */
    protected $column = 'resources.cached_author_id';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: integer (WordPress user ID)
     */
    protected $type = 'integer';

    /**
     * Required base table to enable this filter.
     *
     * @var string|null Table name: views
     */
    protected $requirement = 'views';

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: is, is_not, in, not_in
     */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];

    /**
     * UI input component type.
     *
     * @var string Input type: searchable
     */
    protected $inputType = 'searchable';

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: views
     */
    protected $groups = ['views', 'individual-content', 'individual-author'];

    /**
     * Required JOINs to access the column.
     *
     * @var array JOIN chain: views -> resource_uris -> resources
     */
    protected $joins = [
        [
            'table' => 'resource_uris',
            'alias' => 'resource_uris',
            'on'    => 'views.resource_uri_id = resource_uris.ID',
        ],
        [
            'table' => 'resources',
            'alias' => 'resources',
            'on'    => 'resource_uris.resource_id = resources.ID',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Author', 'wp-statistics');
    }

    /**
     * Search author options via AJAX.
     *
     * @param string $search Search term.
     * @param int    $limit  Maximum results.
     * @return array Array of options with 'value' (user ID) and 'label' (display name).
     */
    public function searchOptions(string $search = '', int $limit = 20): array
    {
        $args = [
            'number'  => $limit,
            'orderby' => 'display_name',
            'order'   => 'ASC',
            'fields'  => ['ID', 'display_name'],
        ];

        if (!empty($search)) {
            $args['search']         = '*' . $search . '*';
            $args['search_columns'] = ['display_name', 'user_login', 'user_nicename'];
        }

        $users   = get_users($args);
        $options = [];

        foreach ($users as $user) {
            $options[] = [
                'value' => $user->ID,
                'label' => $user->display_name,
            ];
        }

        return $options;
    }
}
