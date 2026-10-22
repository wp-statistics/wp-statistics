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
     * @return array{last_visit: string|null, seconds_since: int|null, visitors_24h: int, visitors_week_before: int|null, error: bool}
     */
    public function getRecordingStatus()
    {
        global $wpdb;

        $now   = TimeZone::getCurrentDate();
        $since = TimeZone::getCurrentDate('Y-m-d H:i:s', -1);
        $table = DB::table('visitor');

        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from DB::table()
        $recent = $wpdb->get_row($wpdb->prepare(
            "SELECT MAX(`last_view`) AS last_visit, SUM(`last_view` >= %s) AS visitors_24h FROM `{$table}` WHERE `last_counter` >= %s",
            $since,
            substr($since, 0, 10)
        ));

        $failed     = $recent === null && $wpdb->last_error !== '';
        $lastVisit  = !empty($recent->last_visit) ? (string)$recent->last_visit : null;
        $weekBefore = null;

        if ($lastVisit === null && !$failed) {
            // The latest visit by date, not by ID: imported or restored rows can have higher IDs.
            $lastVisit = $wpdb->get_var(
                "SELECT COALESCE(MAX(`last_view`), MAX(`last_counter`)) FROM `{$table}` WHERE `last_counter` = (SELECT MAX(`last_counter`) FROM `{$table}`)"
            );
            $failed    = $lastVisit === null && $wpdb->last_error !== '';
            $lastVisit = !empty($lastVisit) ? (string)$lastVisit : null;
        }

        if ($lastVisit !== null && $lastVisit < $since && !$failed) {
            // Visitors in the 7 days before the last visit. A site that gets a few visits a week
            // can go a day without one; a site that got hundreds has stopped recording.
            $weekBefore = (int)$wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM `{$table}` WHERE `last_counter` >= %s AND `last_counter` <= %s",
                gmdate('Y-m-d', strtotime($lastVisit . ' -7 days')),
                substr($lastVisit, 0, 10)
            ));
        }
        // phpcs:enable

        return [
            'last_visit'           => $lastVisit,
            'seconds_since'        => $lastVisit ? max(0, strtotime($now) - strtotime($lastVisit)) : null,
            'visitors_24h'         => !empty($recent->visitors_24h) ? (int)$recent->visitors_24h : 0,
            'visitors_week_before' => $weekBefore,
            // A failed query is not "no visits": the card says it could not check.
            'error'                => $failed,
        ];
    }
}
