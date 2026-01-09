<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;

/**
 * Model class for exclusion analytics.
 *
 * @deprecated 15.0.0 Use AnalyticsQueryHandler with exclusions source instead.
 * @see \WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler
 */
class ExclusionsModel extends BaseModel
{
    /**
     * Count exclusions.
     *
     * @deprecated 15.0.0 Use AnalyticsQueryHandler instead.
     *
     * @param array $args Query arguments.
     * @return int Always returns 0.
     */
    public function countExclusions($args = [])
    {
        _deprecated_function(__METHOD__, '15.0.0', 'AnalyticsQueryHandler');
        return 0;
    }

    /**
     * Get exclusions.
     *
     * @deprecated 15.0.0 Use AnalyticsQueryHandler instead.
     *
     * @param array $args Query arguments.
     * @return array Always returns empty array.
     */
    public function getExclusions($args = [])
    {
        _deprecated_function(__METHOD__, '15.0.0', 'AnalyticsQueryHandler');
        return [];
    }
}
