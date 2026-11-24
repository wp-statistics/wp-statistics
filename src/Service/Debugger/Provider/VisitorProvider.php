<?php

namespace WP_Statistics\Service\Debugger\Provider;

use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Debugger\AbstractDebuggerProvider;
use WP_STATISTICS\DB;
use WP_STATISTICS\TimeZone;

/**
 * Provider for handling visitor statistics data
 */
class VisitorProvider extends AbstractDebuggerProvider
{
    /**
     * Stores visitor statistics data.
     *
     * @var array|null
     */
    private $visitors;

    /**
     * Instance of VisitorsModel for data retrieval.
     *
     * @var VisitorsModel
     */
    private $visitorsModel;

    /**
     * Initialize provider
     */
    public function __construct()
    {
        $this->visitorsModel = new VisitorsModel();
    }

    /**
     * Get stored visitor data
     *
     * @return array Array of visitor statistics data
     */
    public function getVisitor()
    {
        return $this->visitors ?? [];
    }

    /**
     * Get latest visitors data with pagination
     *
     * @return array Array of latest visitor data
     */
    public function getLatestVisitors()
    {
        $this->visitors = $this->visitorsModel->getVisitorsData([
            'user_info' => true,
            'order_by' => 'visitor.ID',
            'order' => 'DESC',
            'page' => 1,
            'per_page' => 5,
        ]);

        return $this->visitors;
    }

    /**
     * When the last visit was recorded, and how many visitors were recorded in the last 24 hours.
     *
     * The checks on this page look at the server. This looks at the result: if visits stopped
     * arriving, it says so, whatever stopped them (a consent plugin that never reports consent,
     * a cache, a blocked script).
     *
     * The recent range is narrowed by `last_counter`, the leading column of `date_ip_agent`, so
     * it reads two days of rows at most (about 30 ms for 200,000 rows, measured locally). Only
     * when nothing arrived in those two days does it look further back: the latest visit day is
     * read from the index, and the week before is counted, so the page can tell a quiet site
     * from one where tracking stopped. Dates are in the site's timezone, as WP Statistics
     * stores them.
     *
     * @return array{last_visit: string|null, seconds_since: int|null, visitors_24h: int, visitors_week_before: int|null, active_days: int|null, error: bool}
     */
    public function getRecordingStatus()
    {
        global $wpdb;

        $now   = TimeZone::getCurrentDate();
        $since = TimeZone::getCurrentDate('Y-m-d H:i:s', -1);
        $today = substr($now, 0, 10);
        $table = DB::table('visitor');

        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from DB::table()
        $recent = $wpdb->get_row($wpdb->prepare(
            "SELECT MAX(`last_view`) AS last_visit, SUM(`last_view` >= %s) AS visitors_24h FROM `{$table}` WHERE `last_counter` >= %s AND `last_counter` <= %s AND (`last_view` IS NULL OR `last_view` <= %s)",
            $since,
            substr($since, 0, 10),
            $today,
            $now
        ));

        $failed     = $recent === null && $wpdb->last_error !== '';
        $lastVisit  = !empty($recent->last_visit) ? (string)$recent->last_visit : null;
        $weekBefore = null;
        $activeDays = null;

        if ($lastVisit === null && !$failed) {
            // The latest visit by date, not by ID: imported or restored rows can have higher IDs.
            // Ignore future rows so an imported or clock-skewed timestamp cannot keep the card green.
            // Walk the date index from newest to oldest until a non-future row is found.
            // ORDER BY ... LIMIT can stop there; MAX with a last_view filter scans every row.
            // Two queries, not a subquery: MySQL cannot read a temporary table twice in one query.
            $lastDay = $wpdb->get_var($wpdb->prepare(
                "SELECT `last_counter` FROM `{$table}` WHERE `last_counter` <= %s AND (`last_view` IS NULL OR `last_view` <= %s) ORDER BY `last_counter` DESC LIMIT 1",
                $today,
                $now
            ));
            $failed    = $lastDay === null && $wpdb->last_error !== '';
            $lastVisit = null;

            if (!empty($lastDay) && !$failed) {
                $lastVisit = $wpdb->get_var($wpdb->prepare(
                    "SELECT COALESCE(MAX(`last_view`), MAX(`last_counter`)) FROM `{$table}` WHERE `last_counter` = %s AND (`last_view` IS NULL OR `last_view` <= %s)",
                    $lastDay,
                    $now
                ));
                $failed    = $lastVisit === null && $wpdb->last_error !== '';
                $lastVisit = !empty($lastVisit) ? (string)$lastVisit : null;
            }
        }

        if ($lastVisit !== null && $lastVisit < $since && !$failed) {
            // Visitors in the 7 calendar days ending on the last visit. A site that gets a few
            // visits a week can go a day without one; a site that got hundreds has stopped recording.
            $weekBeforeResult = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM `{$table}` WHERE `last_counter` >= %s AND `last_counter` <= %s",
                gmdate('Y-m-d', strtotime($lastVisit . ' -6 days')),
                substr($lastVisit, 0, 10)
            ));

            if ($weekBeforeResult === null && $wpdb->last_error !== '') {
                $failed = true;
            } else {
                $weekBefore = (int)$weekBeforeResult;
            }

            // Days with at least one visit in the 14 days ending on the last visit: tells a site
            // with visits every day from one with a few visits a week (read from the date index).
            if (!$failed) {
                $activeDaysResult = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(DISTINCT `last_counter`) FROM `{$table}` WHERE `last_counter` >= %s AND `last_counter` <= %s",
                    gmdate('Y-m-d', strtotime($lastVisit . ' -13 days')),
                    substr($lastVisit, 0, 10)
                ));

                if ($activeDaysResult === null && $wpdb->last_error !== '') {
                    $failed = true;
                } else {
                    $activeDays = (int)$activeDaysResult;
                }
            }
        }
        // phpcs:enable

        return [
            'last_visit'           => $lastVisit,
            'seconds_since'        => $lastVisit ? max(0, strtotime($now) - strtotime($lastVisit)) : null,
            'visitors_24h'         => !empty($recent->visitors_24h) ? (int)$recent->visitors_24h : 0,
            'visitors_week_before' => $weekBefore,
            'active_days'          => $activeDays,
            // A failed query is not "no visits": the card says it could not check.
            'error'                => $failed,
        ];
    }

    /**
     * Reads a getRecordingStatus() result the way the Tracker Debugger card does.
     *
     * One rule for the card and for the "visits stopped" alert, so they never disagree:
     * - error:     the visitor table could not be read, so nothing is known.
     * - no_visits: no visit was ever recorded (a new or empty site, never an alert).
     * - working:   a visit was recorded in the last 24 hours.
     * - quiet:     no visit for 1-2 days, but the site had fewer than 7 visitors in the
     *              week before, so a day without visits can be normal.
     * - stopped:   anything older; the card shows "No Recent Visits" as a warning.
     *
     * @param array $status Result of getRecordingStatus().
     * @return string One of error, no_visits, working, quiet, stopped.
     */
    public static function getRecordingState($status)
    {
        if (!empty($status['error'])) {
            return 'error';
        }

        if (!isset($status['last_visit']) || $status['last_visit'] === null) {
            return 'no_visits';
        }

        $secondsSince = (int)$status['seconds_since'];

        if ($secondsSince <= DAY_IN_SECONDS) {
            return 'working';
        }

        $quietSite = $secondsSince <= 2 * DAY_IN_SECONDS
            && isset($status['visitors_week_before'])
            && $status['visitors_week_before'] !== null
            && $status['visitors_week_before'] < 7;

        return $quietSite ? 'quiet' : 'stopped';
    }
}
