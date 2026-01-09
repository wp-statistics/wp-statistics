<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;

/**
 * Model class for performing database operations on tracked resources.
 *
 * @deprecated 15.0.0 Use AnalyticsQueryHandler with page groupBy instead.
 * @see \WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler
 */
class ResourceModel extends BaseModel
{
    /**
     * Count resources that match the supplied filters.
     *
     * @deprecated 15.0.0 Use AnalyticsQueryHandler instead.
     *
     * @param array $args Query arguments.
     * @return int Always returns 0.
     */
    public function count($args = [])
    {
        _deprecated_function(__METHOD__, '15.0.0', 'AnalyticsQueryHandler');
        return 0;
    }

    /**
     * Return daily totals of published resources.
     *
     * @deprecated 15.0.0 Use AnalyticsQueryHandler instead.
     *
     * @param array $args Query arguments.
     * @return array Always returns empty array.
     */
    public function countDaily($args = [])
    {
        _deprecated_function(__METHOD__, '15.0.0', 'AnalyticsQueryHandler');
        return [];
    }
}
