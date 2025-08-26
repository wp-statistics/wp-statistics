<?php

namespace WP_Statistics\Utils;

/**
 * Utility class for retrieving and analyzing WordPress comment data.
 *
 * Includes methods for counting comments by status and calculating
 * publishing rates over time. Supports both localized spam counts
 * and metrics like daily averages or intervals between approved comments.
 *
 * @package WP_Statistics\Utils
 * @since 15.0.0
 */
class Comment
{
    /**
     * Count comments for the given status.
     *
     * Accepts any key returned by {@see wp_count_comments()}, such as
     * 'approved', 'spam', or 'moderated'. When the requested status is
     * 'spam', Akismet’s stored counter is used (if available) and returned
     * via {@see number_format_i18n()} for localisation.
     *
     * @param string $status Optional. Status key. Default 'approved'.
     * @return int|string    Localised string for 'spam', otherwise raw count.
     */
    public static function countAll(string $status = 'approved')
    {
        if ($status === 'spam') {
            return number_format_i18n(get_option('akismet_spam_count'));
        }

        $totals = wp_count_comments();

        return (is_object($totals) && isset($totals->{$status}))
            ? (int)$totals->{$status}
            : 0;
    }

    /**
     * Calculate comment publish rate.
     *
     * When <code>$daysBetween</code> is <code>true</code> the method returns
     * the average <em>days between</em> approved comments. Otherwise it
     * returns the average <em>approved comments per day</em>. The start date
     * is the timestamp of the earliest approved comment on the site.
     *
     * @param bool $daysBetween Optional. True for days‑between, false for
     *                          comments‑per‑day. Default false.
     * @return float            Rounded average or 0 when no approved comments.
     */
    public static function getPublishRate(bool $daysBetween = false): float
    {
        $totals        = wp_count_comments();
        $approvedCount = (is_object($totals) && isset($totals->approved))
            ? (int)$totals->approved
            : 0;

        if ($approvedCount === 0) {
            return 0;
        }

        $earliestIds = get_comments([
            'status'  => 'approve',
            'number'  => 1,
            'orderby' => 'comment_date_gmt',
            'order'   => 'ASC',
            'fields'  => 'ids',
        ]);

        if (empty($earliestIds)) {
            return 0;
        }

        $firstTimestamp = get_comment($earliestIds[0])->comment_date_gmt;
        if (empty($firstTimestamp)) {
            return 0;
        }

        $firstUnix = strtotime($firstTimestamp);

        $daysSpan = max(
            1,
            (int)floor((time() - $firstUnix) / DAY_IN_SECONDS)
        );

        return $daysBetween
            ? round($daysSpan / $approvedCount, 0)
            : round($approvedCount / $daysSpan, 2);
    }
}