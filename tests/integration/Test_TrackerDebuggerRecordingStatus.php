<?php

namespace WP_Statistics\Tests\Service\Debugger;

use WP_STATISTICS\DB;
use WP_Statistics\Components\View;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;
use WP_Statistics\Service\Debugger\Provider\VisitorProvider;
use WP_STATISTICS\TimeZone;
use WP_UnitTestCase;

/**
 * The Tracker Debugger shows when the last visit was recorded, so a site where
 * visits stopped arriving is visible at a glance, whatever stopped them (#1178).
 */
class Test_TrackerDebuggerRecordingStatus extends WP_UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        TableHandler::createTable('visitor', Manager::getSchemaForTable('visitor'));
    }

    public static function tearDownAfterClass(): void
    {
        global $wpdb;
        $wpdb->query('DROP TABLE IF EXISTS `' . DB::table('visitor') . '`'); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        parent::tearDownAfterClass();
    }

    public function setUp(): void
    {
        parent::setUp();

        global $wpdb;
        $wpdb->query('DELETE FROM `' . DB::table('visitor') . '`'); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

    private function addVisitor($lastView, $ip)
    {
        global $wpdb;

        $wpdb->insert(DB::table('visitor'), [
            'last_counter' => substr($lastView, 0, 10),
            'last_view'    => $lastView,
            'referred'     => '',
            'agent'        => 'Chrome',
            'platform'     => 'Windows',
            'version'      => '141',
            'ip'           => $ip,
            'user_id'      => 0,
        ]);
    }

    public function test_no_visits_yet()
    {
        $status = (new VisitorProvider())->getRecordingStatus();

        $this->assertNull($status['last_visit']);
        $this->assertNull($status['seconds_since']);
        $this->assertSame(0, $status['visitors_24h']);
    }

    public function test_recent_visits_are_counted()
    {
        $this->addVisitor(TimeZone::getCurrentDate('Y-m-d H:i:s', null), '10.0.0.1');
        $this->addVisitor(TimeZone::getCurrentDate('Y-m-d H:i:s', -3), '10.0.0.2');

        $status = (new VisitorProvider())->getRecordingStatus();

        $this->assertSame(1, $status['visitors_24h']);
        $this->assertLessThan(MINUTE_IN_SECONDS, $status['seconds_since']);
    }

    public function test_last_visit_older_than_the_recent_range_is_still_found()
    {
        $eightDaysAgo = TimeZone::getCurrentDate('Y-m-d H:i:s', -8);
        $this->addVisitor($eightDaysAgo, '10.0.0.3');

        $status = (new VisitorProvider())->getRecordingStatus();

        $this->assertSame($eightDaysAgo, $status['last_visit']);
        $this->assertSame(0, $status['visitors_24h']);
        $this->assertGreaterThan(7 * DAY_IN_SECONDS, $status['seconds_since']);
    }

    public function test_last_visit_is_the_latest_date_not_the_highest_id()
    {
        // A restored row with an older date gets a higher ID than the real last visit.
        $twoDaysAgo  = TimeZone::getCurrentDate('Y-m-d H:i:s', -3);
        $tenDaysAgo  = TimeZone::getCurrentDate('Y-m-d H:i:s', -10);
        $this->addVisitor($twoDaysAgo, '10.0.0.4');
        $this->addVisitor($tenDaysAgo, '10.0.0.5');

        $this->assertSame($twoDaysAgo, (new VisitorProvider())->getRecordingStatus()['last_visit']);
    }

    public function test_future_rows_do_not_make_recording_look_current()
    {
        $threeDaysAgo = TimeZone::getCurrentDate('Y-m-d H:i:s', -3);
        $nextMonth    = TimeZone::getCurrentDate('Y-m-d H:i:s', 30);
        $this->addVisitor($threeDaysAgo, '10.0.0.8');
        $this->addVisitor($nextMonth, '10.0.0.9');

        $status = (new VisitorProvider())->getRecordingStatus();

        $this->assertSame($threeDaysAgo, $status['last_visit']);
        $this->assertSame(0, $status['visitors_24h']);
        $this->assertGreaterThan(2 * DAY_IN_SECONDS, $status['seconds_since']);
    }

    public function test_counts_the_week_before_a_stopped_site()
    {
        for ($day = 3; $day <= 9; $day++) {
            $this->addVisitor(TimeZone::getCurrentDate('Y-m-d H:i:s', -$day), '10.1.0.' . $day);
            $this->addVisitor(TimeZone::getCurrentDate('Y-m-d H:i:s', -$day), '10.2.0.' . $day);
        }

        $status = (new VisitorProvider())->getRecordingStatus();

        $this->assertSame(14, $status['visitors_week_before']);
    }

    public function test_week_before_count_uses_seven_calendar_days()
    {
        for ($day = 3; $day <= 10; $day++) {
            $this->addVisitor(TimeZone::getCurrentDate('Y-m-d H:i:s', -$day), '10.3.0.' . $day);
        }

        $status = (new VisitorProvider())->getRecordingStatus();

        $this->assertSame(7, $status['visitors_week_before']);
    }

    public function test_no_week_before_count_while_visits_arrive()
    {
        $this->addVisitor(TimeZone::getCurrentDate(), '10.0.0.6');

        $this->assertNull((new VisitorProvider())->getRecordingStatus()['visitors_week_before']);
    }

    public function test_a_failed_query_is_reported_as_an_error()
    {
        global $wpdb;

        $breakQuery = static function ($query) {
            return str_replace(DB::table('visitor'), 'wp_statistics_missing_table', $query);
        };

        add_filter('query', $breakQuery);
        $suppress = $wpdb->suppress_errors(true);

        try {
            $status = (new VisitorProvider())->getRecordingStatus();
        } finally {
            $wpdb->suppress_errors($suppress);
            remove_filter('query', $breakQuery);
        }

        $this->assertTrue($status['error']);
        $this->assertNull($status['last_visit']);
    }

    public function test_a_failed_week_before_query_is_reported_as_an_error()
    {
        global $wpdb;

        $this->addVisitor(TimeZone::getCurrentDate('Y-m-d H:i:s', -3), '10.0.0.7');

        $breakWeekBeforeQuery = static function ($query) {
            if (strpos($query, 'SELECT COUNT(*) FROM `' . DB::table('visitor') . '`') === false) {
                return $query;
            }

            return str_replace(DB::table('visitor'), 'wp_statistics_missing_table', $query);
        };

        add_filter('query', $breakWeekBeforeQuery);
        $suppress = $wpdb->suppress_errors(true);

        try {
            $status = (new VisitorProvider())->getRecordingStatus();
        } finally {
            $wpdb->suppress_errors($suppress);
            remove_filter('query', $breakWeekBeforeQuery);
        }

        $this->assertTrue($status['error']);
        $this->assertNull($status['visitors_week_before']);
    }

    private function renderDebugger($recording)
    {
        $previousOptions       = Option::getOptions();
        $previousActivePlugins = get_option('active_plugins', []);

        Option::update('consent_integration', 'borlabs_cookie');
        Option::update('anonymous_tracking', true);
        update_option('active_plugins', ['borlabs-cookie/borlabs-cookie.php']);

        $options = new class {
            public function getExcludedIPs()
            {
                return [];
            }

            public function getUserRoleExclusions()
            {
                return [];
            }

            public function getExcludedUrls()
            {
                return [];
            }

            public function getIncludedCountries()
            {
                return [];
            }

            public function getExcludedCountries()
            {
                return [];
            }

            public function getOption($name)
            {
                return false;
            }

            public function formatValuesAsHtml($values, $tag)
            {
                return '';
            }
        };

        $tracker = new class {
            public function getTrackerStatus()
            {
                return [
                    'exists'         => true,
                    'frontendStatus' => ['isAccessible' => true],
                ];
            }

            public function getCacheStatus()
            {
                return false;
            }

            public function getCachePlugin()
            {
                return '';
            }
        };

        $visitors = new class($recording) {
            private $recording;

            public function __construct($recording)
            {
                $this->recording = $recording;
            }

            public function getRecordingStatus()
            {
                return $this->recording;
            }

            public function getLatestVisitors()
            {
                return [];
            }

            public function getVisitor()
            {
                return [];
            }
        };

        try {
            return View::load('pages/tracker-debugger/tracker-debugger', compact('options', 'tracker', 'visitors'), true);
        } finally {
            update_option('active_plugins', $previousActivePlugins);
            Option::save_options($previousOptions);
        }
    }

    public function test_debugger_describes_anonymous_consent_tracking()
    {
        $html = $this->renderDebugger([
            'last_visit'           => TimeZone::getCurrentDate('Y-m-d H:i:s', -3),
            'seconds_since'        => 3 * DAY_IN_SECONDS,
            'visitors_24h'         => 0,
            'visitors_week_before' => 10,
            'error'                => false,
        ]);

        $this->assertStringContainsString('Visitors who have not given consent are recorded anonymously.', $html);
        $this->assertStringNotContainsString('so a visit is only recorded after the visitor gives consent.', $html);
    }

    public function test_long_outage_is_not_downgraded_for_a_quiet_site()
    {
        $html = $this->renderDebugger([
            'last_visit'           => TimeZone::getCurrentDate('Y-m-d H:i:s', -60),
            'seconds_since'        => 60 * DAY_IN_SECONDS,
            'visitors_24h'         => 0,
            'visitors_week_before' => 1,
            'error'                => false,
        ]);

        $this->assertMatchesRegularExpression('/wps-audit-card--warning.*Visit Recording: No Recent Visits/s', $html);
        $this->assertStringNotContainsString('so a day without visits can be normal.', $html);
    }
}
