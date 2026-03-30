<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Utils\Query;

/**
 * UserModel
 *
 * Provides database access methods for WordPress users.
 * Extends the BaseModel and uses the Query utility to perform
 * user-related data operations such as existence checks.
 *
 * @deprecated 15.0.0 Use WordPress core user functions or AnalyticsQueryHandler with author groupBy instead.
 * @see \WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler
 * @since 15.0.0
 */
class UserModel extends BaseModel
{
    /**
     * Determine whether a user exists by given criteria.
     *
     * Parses the provided arguments (currently supports 'ID') and returns
     * the count of matching users in the database.
     *
     * @param array $args {
     *     Optional. Query parameters for checking existence.
     *
     *     @type int $ID User ID to check.
     * }
     * @return int The number of users.
     */
    public function exists($args) {
        $args = $this->parseArgs($args, [
            'ID' => '',
        ]);

        return Query::select('COUNT(*)')
            ->from('users')
            ->where('ID', '=', $args['ID'])
            ->getVar();
    }
}