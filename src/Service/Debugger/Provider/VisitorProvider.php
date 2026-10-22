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
     * Both queries use an index, so they stay fast on a large visitor table: the recent range
     * is narrowed by `last_counter` (the leading column of `date_ip_agent`), and the fallback
     * for an older last visit reads the newest row by primary key. Dates are in the site's
     * timezone, as WP Statistics stores them.
     *
     * @return array{last_visit: string|null, seconds_since: int|null, visitors_24h: int, error: bool}
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

        $failed    = $recent === null && $wpdb->last_error !== '';
        $lastVisit = !empty($recent->last_visit) ? (string)$recent->last_visit : null;

        if ($lastVisit === null && !$failed) {
            $lastVisit = $wpdb->get_var("SELECT COALESCE(`last_view`, `last_counter`) FROM `{$table}` ORDER BY `ID` DESC LIMIT 1");
            $failed    = $lastVisit === null && $wpdb->last_error !== '';
            $lastVisit = !empty($lastVisit) ? (string)$lastVisit : null;
        }
        // phpcs:enable

        return [
            'last_visit'    => $lastVisit,
            'seconds_since' => $lastVisit ? max(0, strtotime($now) - strtotime($lastVisit)) : null,
            'visitors_24h'  => !empty($recent->visitors_24h) ? (int)$recent->visitors_24h : 0,
            // A failed query is not "no visits": the card says it could not check.
            'error'         => $failed,
        ];
    }
}
