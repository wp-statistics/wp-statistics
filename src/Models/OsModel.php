<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;

/**
 * Model class for performing database operations related to operating systems.
 *
 * @deprecated 15.0.0 Use AnalyticsQueryHandler with os groupBy instead.
 * @see \WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler
 */
class OsModel extends BaseModel
{
    /**
     * Get all operating systems by total views within a date range.
     *
     * @deprecated 15.0.0 Use AnalyticsQueryHandler instead.
     *
     * @param array $args Query arguments.
     * @return array Always returns empty array.
     */
    public function getTop($args = [])
    {
        _deprecated_function(__METHOD__, '15.0.0', 'AnalyticsQueryHandler');
        return [];
    }
}
