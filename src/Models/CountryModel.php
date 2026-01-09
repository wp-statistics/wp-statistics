<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;

/**
 * Model class for performing database operations related to countries.
 *
 * @deprecated 15.0.0 Use AnalyticsQueryHandler with 'country' group_by instead.
 * @see \WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler
 */
class CountryModel extends BaseModel
{
    /**
     * Get top countries by views within a date range.
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
