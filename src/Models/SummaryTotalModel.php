<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;

/**
 * Model class for performing database operations related to daily traffic totals.
 *
 * @deprecated 15.0.0 Use AnalyticsQueryHandler with views/visitors sources instead.
 * @see \WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler
 */
class SummaryTotalModel extends BaseModel
{
    /**
     * Get total visitors within a date range.
     *
     * @deprecated 15.0.0 Use AnalyticsQueryHandler instead.
     *
     * @param array $args Query arguments.
     * @return int Always returns 0.
     */
    public function getVisitorsCount($args = [])
    {
        _deprecated_function(__METHOD__, '15.0.0', 'AnalyticsQueryHandler');
        return 0;
    }

    /**
     * Get total views within a date range.
     *
     * @deprecated 15.0.0 Use AnalyticsQueryHandler instead.
     *
     * @param array $args Query arguments.
     * @return int Always returns 0.
     */
    public function getViewsCount($args = [])
    {
        _deprecated_function(__METHOD__, '15.0.0', 'AnalyticsQueryHandler');
        return 0;
    }

    /**
     * Get total fields within a date range.
     *
     * @deprecated 15.0.0 Use AnalyticsQueryHandler instead.
     *
     * @param array $args Query arguments.
     * @return \stdClass Always returns empty stdClass.
     */
    public function getFieldsCount($args = [])
    {
        _deprecated_function(__METHOD__, '15.0.0', 'AnalyticsQueryHandler');
        return new \stdClass();
    }

    /**
     * Get daily traffic (views & visitors) for a date range.
     *
     * @deprecated 15.0.0 Use AnalyticsQueryHandler instead.
     *
     * @param array $args Query arguments.
     * @return array Always returns empty array.
     */
    public function getTrafficInRange($args = [])
    {
        _deprecated_function(__METHOD__, '15.0.0', 'AnalyticsQueryHandler');
        return [];
    }
}
