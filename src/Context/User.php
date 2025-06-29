<?php

namespace WP_Statistics\Context;

/**
 * Helper for user-related statistics.
 *
 * Provides utility methods to retrieve counts and other data about
 * registered WordPress users across the entire site.
 *
 * @package WP_Statistics\Context
 * @since   15.0.0
 */
final class User
{
    /**
     * Count registered users on the site.
     *
     * @return int Total number of users.
     */
    public static function countAll()
    {
        $result = count_users();
        return !empty($result['total_users']) ? $result['total_users'] : 0;
    }

    /**
     * Calculate user‑registration rate.
     *
     * When <code>$daysBetween</code> is <code>true</code> the method returns
     * the average <em>days between</em> registered users. Otherwise it returns
     * the average <em>registrations per day</em>. The calculation starts from
     * the earliest registered user account on the site.
     *
     * @param bool $daysBetween Optional. True for days‑between, false for
     *                          registrations‑per‑day. Default false.
     * @return float            Rounded average, or 0 when no users found.
     */
    public static function getRegisterRate(bool $daysBetween = false): float
    {
        $totals    = count_users();
        $userCount = !empty($totals['total_users']) ? (int)$totals['total_users'] : 0;

        if ($userCount === 0) {
            return 0;
        }

        $query = new \WP_User_Query([
            'number'  => 1,
            'orderby' => 'registered',
            'order'   => 'ASC',
            'fields'  => ['ID'],
        ]);

        $ids = $query->get_results();
        if (empty($ids)) {
            return 0;
        }

        $firstUser = get_user_by('id', $ids[0]);
        if (empty($firstUser->user_registered)) {
            return 0;
        }

        $firstTimestamp = strtotime($firstUser->user_registered);
        $daysSpan       = max(
            1,
            (int)floor((time() - $firstTimestamp) / DAY_IN_SECONDS)
        );

        return $daysBetween
            ? round($daysSpan / $userCount, 0)
            : round($userCount / $daysSpan, 2);
    }
}