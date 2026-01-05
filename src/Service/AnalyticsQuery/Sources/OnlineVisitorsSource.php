<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Online Visitors source - counts currently online visitors.
 *
 * This source counts unique visitors who have had activity within the last 5 minutes.
 * It uses sessions.ended_at instead of started_at to determine recent activity.
 *
 * Important: When using this source, the date filters are ignored in favor of
 * the online threshold (5 minutes by default).
 *
 * @since 15.0.0
 */
class OnlineVisitorsSource extends AbstractSource
{
    /**
     * Source name identifier.
     *
     * @var string
     */
    protected $name = 'online_visitors';

    /**
     * SQL expression for counting online visitors.
     *
     * @var string
     */
    protected $expression = 'COUNT(DISTINCT sessions.visitor_id)';

    /**
     * Primary table for this source.
     *
     * @var string
     */
    protected $table = 'sessions';

    /**
     * Return type.
     *
     * @var string
     */
    protected $type = 'integer';

    /**
     * Display format.
     *
     * @var string
     */
    protected $format = 'number';

    /**
     * Online threshold in seconds (5 minutes).
     *
     * @var int
     */
    const ONLINE_THRESHOLD = 300;

    /**
     * This source does not support summary table aggregation.
     *
     * @return bool
     */
    public function supportsSummaryTable(): bool
    {
        return false;
    }

    /**
     * Check if this source requires special date handling.
     *
     * @return bool
     */
    public function usesEndedAtForDateFilter(): bool
    {
        return true;
    }

    /**
     * Get the date threshold for online visitors.
     *
     * @return string Date string in 'Y-m-d H:i:s' format.
     */
    public static function getOnlineThreshold(): string
    {
        return gmdate('Y-m-d H:i:s', time() - self::ONLINE_THRESHOLD);
    }
}
