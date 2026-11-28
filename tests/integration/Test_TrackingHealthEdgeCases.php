<?php

namespace WP_Statistics\Tests\Service\TrackerDebugger;

use WP_STATISTICS\DB;
use WP_STATISTICS\Option;
use WP_STATISTICS\TimeZone;
use WP_Statistics\Service\Admin\TrackerDebugger\TrackingHealth;
use WP_Statistics\Service\Debugger\Provider\VisitorProvider;
use WP_UnitTestCase;

/**
 * Edge cases for the "tracking stopped" alert (review file, uncommitted).
 * Visitor table is a temporary table in the PHPUnit database, mail goes to the WP mock mailer.
 */
class Test_TrackingHealthEdgeCases extends WP_UnitTestCase
{
    private $ip = 0;

    private $timezoneString;
    private $gmtOffset;
    private $savedOptions;

    public function setUp(): void
    {
        parent::setUp();

        // The table rebuild below commits the test transaction, so options are restored by hand in tearDown().
        // Some tests call setUp() again after changing the timezone: keep the first values.
        if ($this->timezoneString === null) {
            $this->timezoneString = get_option('timezone_string');
            $this->gmtOffset      = get_option('gmt_offset');
            $this->savedOptions   = [
                'email_list'            => Option::get('email_list'),
                TrackingHealth::SETTING => Option::get(TrackingHealth::SETTING),
                'use_cache_plugin'      => Option::get('use_cache_plugin'),
            ];
        }

        reset_phpmailer_instance();
        Option::update('email_list', 'alerts@example.com');
        Option::update(TrackingHealth::SETTING, true);
        Option::update('use_cache_plugin', true);
        delete_option(TrackingHealth::STATE_OPTION);
        delete_option('wp_statistics_dismissed_notices');
        update_option('timezone_string', '');
        update_option('gmt_offset', 0);

        global $wpdb;
        $table = DB::table('visitor');
        $wpdb->query("DROP TABLE IF EXISTS `{$table}`");
        $wpdb->query("CREATE TABLE `{$table}` (
            `ID` bigint(20) NOT NULL AUTO_INCREMENT,
            `last_counter` date NOT NULL,
            `referred` text NOT NULL,
            `agent` varchar(180) NOT NULL,
            `platform` varchar(180) DEFAULT NULL,
            `version` varchar(180) DEFAULT NULL,
            `ip` varchar(60) NOT NULL,
            `user_id` bigint(40) NOT NULL,
            `last_view` datetime DEFAULT NULL,
            PRIMARY KEY (`ID`),
            UNIQUE KEY `date_ip_agent` (`last_counter`,`ip`,`agent`(75),`platform`(75),`version`(75))
        )");
    }

    public static function tearDownAfterClass(): void
    {
        // These tests replace the visitor table with a small one (and one test drops it):
        // put the real table back for the test classes that run after this one.
        global $wpdb;
        $wpdb->query('DROP TABLE IF EXISTS `' . DB::table('visitor') . '`');
        \WP_Statistics\Service\Database\Managers\TableHandler::createAllTables();

        parent::tearDownAfterClass();
    }

    public function tearDown(): void
    {
        delete_option(TrackingHealth::STATE_OPTION);
        wp_clear_scheduled_hook(TrackingHealth::HOOK);
        remove_all_filters('pre_option_' . TrackingHealth::STATE_OPTION);
        remove_all_filters('wp_statistics_tracking_health_status');
        remove_all_filters('wp_statistics_tracking_health_email_receivers');
        remove_all_filters('pre_wp_mail');
        parent::tearDown();

        // Written after the rollback and committed, so later test classes get the original timezone back.
        global $wpdb;
        update_option('timezone_string', $this->timezoneString);
        update_option('gmt_offset', $this->gmtOffset);
        foreach ($this->savedOptions as $name => $value) {
            Option::update($name, $value);
        }
        $wpdb->query('COMMIT');
    }

    // ---------- helpers ----------

    private function siteNow()
    {
        return strtotime(TimeZone::getCurrentDate()); // site-local clock read as UTC
    }

    private function visit($secondsAgo)
    {
        global $wpdb;
        $view = gmdate('Y-m-d H:i:s', $this->siteNow() - $secondsAgo);
        $wpdb->insert(DB::table('visitor'), [
            'last_counter' => substr($view, 0, 10),
            'referred'     => '',
            'agent'        => 'Chrome',
            'platform'     => 'Mac',
            'version'      => '1',
            'ip'           => 'h' . (++$this->ip),
            'user_id'      => 0,
            'last_view'    => $view,
        ]);
    }

    /** Last visit $secondsAgo, plus $weekBefore-1 more visitors 2 days before it. */
    private function site($secondsAgo, $weekBefore)
    {
        $this->visit($secondsAgo);
        for ($i = 1; $i < $weekBefore; $i++) {
            $this->visit($secondsAgo + 2 * DAY_IN_SECONDS);
        }
    }

    /** Steady traffic: $perDay visitors on each of the 14 days ending $secondsAgo ago. */
    private function steadySite($secondsAgo, $perDay = 6, $everyNthDay = 1)
    {
        for ($day = 0; $day < 14; $day += $everyNthDay) {
            for ($i = 0; $i < $perDay; $i++) {
                $this->visit($secondsAgo + $day * DAY_IN_SECONDS + $i);
            }
        }
    }

    private function state()
    {
        return VisitorProvider::getRecordingState((new VisitorProvider())->getRecordingStatus());
    }

    private function st($secondsSince, $week = 700, $error = false)
    {
        $now = $this->siteNow();
        return [
            'last_visit'           => $secondsSince === null ? null : gmdate('Y-m-d H:i:s', $now - $secondsSince),
            'seconds_since'        => $secondsSince,
            'visitors_24h'         => 0,
            'visitors_week_before' => $week,
            'error'                => $error,
        ];
    }

    private function mails()
    {
        return count(tests_retrieve_phpmailer_instance()->mock_sent);
    }

    private function mail($i = 0)
    {
        $m = tests_retrieve_phpmailer_instance()->get_sent($i);
        if ($m && isset($m->body)) {
            $m->body = quoted_printable_decode($m->body); // mock mailer keeps the quoted-printable body
        }
        return $m;
    }

    // ---------- getRecordingState boundaries (pure) ----------

    public function test_state_boundaries()
    {
        $S = function ($s, $w = 700, $e = false) { return VisitorProvider::getRecordingState($this->st($s, $w, $e)); };
        $this->assertSame('working', $S(DAY_IN_SECONDS));
        $this->assertSame('stopped', $S(DAY_IN_SECONDS + 1));
        $this->assertSame('quiet', $S(DAY_IN_SECONDS + 1, 6));
        $this->assertSame('stopped', $S(DAY_IN_SECONDS + 1, 7));
        $this->assertSame('quiet', $S(2 * DAY_IN_SECONDS, 6));
        $this->assertSame('stopped', $S(2 * DAY_IN_SECONDS + 1, 6));
        $this->assertSame('quiet', $S(30 * HOUR_IN_SECONDS, 0));
        $this->assertSame('stopped', $S(30 * HOUR_IN_SECONDS, null));
        $this->assertSame('quiet', $S(30 * HOUR_IN_SECONDS, '6'));
        $this->assertSame('stopped', $S(30 * HOUR_IN_SECONDS, '7'));
        $this->assertSame('error', $S(30 * HOUR_IN_SECONDS, 700, true));
        $this->assertSame('error', $S(null, 700, true));
        $this->assertSame('no_visits', VisitorProvider::getRecordingState([]));
        $this->assertSame('no_visits', VisitorProvider::getRecordingState(['last_visit' => null]));
        $this->assertSame('stopped', $S(PHP_INT_MAX / 2, PHP_INT_MAX));
    }

    public function test_state_with_last_visit_but_null_seconds_is_working()
    {
        // documents behaviour: seconds_since null with a last_visit is read as 0 -> working
        $this->assertSame('working', VisitorProvider::getRecordingState(['last_visit' => '2020-01-01 00:00:00', 'seconds_since' => null]));
    }

    // ---------- real getRecordingStatus() against a temp table ----------

    public function test_never_had_visits()
    {
        $this->assertSame('no_visits', $this->state());
        $this->assertNull(TrackingHealth::check() === 'no_visits' ? null : 'x');
        $this->assertSame(0, $this->mails());
        $this->assertFalse(TrackingHealth::isStopped());
    }

    public function test_brand_new_site_single_visit_today()
    {
        $this->visit(60);
        $this->assertSame('working', $this->state());
        TrackingHealth::check();
        $this->assertSame(0, $this->mails());
    }

    public function test_23h59_vs_24h01()
    {
        $this->site(23 * HOUR_IN_SECONDS + 59 * 60, 700);
        $this->assertSame('working', $this->state());
        $this->setUp();
        $this->site(24 * HOUR_IN_SECONDS + 60, 700);
        $this->assertSame('stopped', $this->state());
    }

    public function test_week_before_6_vs_7_real_rows()
    {
        $this->site(30 * HOUR_IN_SECONDS, 6);
        $this->assertSame('quiet', $this->state());
        $this->setUp();
        $this->site(30 * HOUR_IN_SECONDS, 7);
        $this->assertSame('stopped', $this->state());
    }

    public function test_visits_only_8_days_ago()
    {
        $this->steadySite(8 * DAY_IN_SECONDS);
        $this->assertSame('stopped', $this->state());
        TrackingHealth::check();
        $this->assertSame(1, $this->mails());
    }

    // ---------- who gets the alert (steady traffic, usual gap, baseline) ----------

    public function test_small_blog_with_three_visits_a_week_gets_no_alert()
    {
        // A visit every other day or so: 3 a week, last one 3 days ago
        foreach ([3, 5, 7, 10, 12, 14] as $daysAgo) {
            $this->visit($daysAgo * DAY_IN_SECONDS);
        }

        $status = (new VisitorProvider())->getRecordingStatus();
        $this->assertSame('stopped', VisitorProvider::getRecordingState($status), 'the card still shows no recent visits');
        $this->assertSame(6, $status['active_days']);
        $this->assertFalse(TrackingHealth::shouldAlert($status));

        TrackingHealth::check();
        TrackingHealth::check();
        $this->assertSame(0, $this->mails());
        $this->assertFalse(TrackingHealth::isStopped());
    }

    public function test_dormant_site_gets_no_alert_on_the_first_run_after_update()
    {
        // Steady traffic once, but the last visit was months ago
        $this->steadySite(120 * DAY_IN_SECONDS);

        $status = (new VisitorProvider())->getRecordingStatus();
        $this->assertTrue(TrackingHealth::shouldAlert($status), 'steady before it went quiet');

        TrackingHealth::check();
        TrackingHealth::check();
        $this->assertSame(0, $this->mails());
        $this->assertFalse(TrackingHealth::isStopped());
        $this->assertFalse(get_option(TrackingHealth::BASELINE_OPTION, false), 'no recent visit, so no baseline');
    }

    public function test_steady_site_that_stops_is_alerted_once_then_recovers()
    {
        $this->steadySite(30 * HOUR_IN_SECONDS);

        $this->assertSame('stopped', TrackingHealth::check());
        $this->assertSame(1, $this->mails());
        $this->assertNotFalse(get_option(TrackingHealth::BASELINE_OPTION, false));

        TrackingHealth::check();
        TrackingHealth::check();
        $this->assertSame(1, $this->mails(), 'one email per outage');

        $this->visit(60);
        $this->assertSame('working', TrackingHealth::check());
        $this->assertSame(2, $this->mails());
        $this->assertStringContainsString('recorded again', $this->mail(1)->subject);
    }

    public function test_quiet_window_follows_the_usual_gap()
    {
        // Visits on 7 of 14 days (every other day): the alert waits 3 days, not 1
        $base = ['last_visit' => '2026-01-01 00:00:00', 'visitors_week_before' => 70, 'active_days' => 7, 'error' => false];

        $this->assertFalse(TrackingHealth::shouldAlert($base + ['seconds_since' => 2 * DAY_IN_SECONDS]));
        $this->assertTrue(TrackingHealth::shouldAlert($base + ['seconds_since' => 3 * DAY_IN_SECONDS + 1]));

        // Visits every day: one day is enough
        $daily = ['active_days' => 14] + $base;
        $this->assertTrue(TrackingHealth::shouldAlert($daily + ['seconds_since' => DAY_IN_SECONDS + 1]));

        // Few visitors and few active days: never
        $this->assertFalse(TrackingHealth::shouldAlert(['visitors_week_before' => 3, 'active_days' => 6, 'seconds_since' => 30 * DAY_IN_SECONDS] + $base));
    }

    public function test_deactivation_resets_the_baseline()
    {
        update_option(TrackingHealth::BASELINE_OPTION, time());
        \WP_Statistics::deactivator(false);
        $this->assertFalse(get_option(TrackingHealth::BASELINE_OPTION, false));
    }

    public function test_visits_only_8_days_ago_tiny_site_is_stopped_not_quiet()
    {
        // quiet applies for 1-2 days only: a site with 2 visits a week silent for 8 days is "stopped"
        $this->site(8 * DAY_IN_SECONDS, 2);
        $this->assertSame('stopped', $this->state());
    }

    public function test_future_dated_rows_are_ignored()
    {
        $this->site(3 * DAY_IN_SECONDS, 50);
        $this->visit(-2 * DAY_IN_SECONDS); // 2 days in the future
        $this->assertSame('stopped', $this->state());
    }

    public function timezones()
    {
        return [
            'UTC'         => ['', 0],
            '+14'         => ['', 14],
            '-12'         => ['', -12],
            '+5.5'        => ['', 5.5],
            'New_York'    => ['America/New_York', 0],
            'Kiritimati'  => ['Pacific/Kiritimati', 0],
            'Lord_Howe'   => ['Australia/Lord_Howe', 0],
        ];
    }

    /**
     * @dataProvider timezones
     */
    public function test_timezone_boundaries($tz, $offset)
    {
        update_option('timezone_string', $tz);
        update_option('gmt_offset', $offset);

        $this->site(23 * HOUR_IN_SECONDS + 59 * 60, 700);
        $this->assertSame('working', $this->state(), "23h59 in $tz $offset");
        $this->setUp();
        update_option('timezone_string', $tz);
        update_option('gmt_offset', $offset);

        $this->site(24 * HOUR_IN_SECONDS + 60, 700);
        $this->assertSame('stopped', $this->state(), "24h01 in $tz $offset");
        $this->setUp();
        update_option('timezone_string', $tz);
        update_option('gmt_offset', $offset);

        // week-before window is calendar days, check 6 vs 7 survives the offset
        $this->site(30 * HOUR_IN_SECONDS, 6);
        $this->assertSame('quiet', $this->state(), "6 in $tz $offset");
        $this->setUp();
        update_option('timezone_string', $tz);
        update_option('gmt_offset', $offset);
        $this->site(30 * HOUR_IN_SECONDS, 7);
        $this->assertSame('stopped', $this->state(), "7 in $tz $offset");
    }

    public function test_query_failure_via_missing_table()
    {
        global $wpdb;
        $table = DB::table('visitor');
        $wpdb->query("DROP TABLE `{$table}`");
        $wpdb->suppress_errors(true);
        $status = (new VisitorProvider())->getRecordingStatus();
        $wpdb->suppress_errors(false);
        $this->assertTrue($status['error']);
        $this->assertSame('error', VisitorProvider::getRecordingState($status));

        // state advances nothing
        $this->assertSame('error', TrackingHealth::check($status));
        $this->assertSame(0, $this->mails());
        $this->assertFalse(TrackingHealth::isStopped());
    }

    public function test_query_failure_during_outage_keeps_outage_and_sends_nothing_more()
    {
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $this->assertSame(1, $this->mails());
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS, 700, true));
        $this->assertTrue(TrackingHealth::isStopped());
        $this->assertSame(1, $this->mails());
    }

    public function test_huge_counts()
    {
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS, 987654321012));
        $this->assertSame(1, $this->mails());
        $body = $this->mail(0)->body;
        $this->assertStringContainsString('141,093,474,430', $body); // 987654321012/7 rounded
    }

    // ---------- state machine ----------

    public function test_cycle_stopped_recovered_stopped_again()
    {
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $this->assertSame(1, $this->mails());
        for ($i = 0; $i < 3; $i++) {
            TrackingHealth::check($this->st((54 + 24 * $i) * HOUR_IN_SECONDS));
        }
        $this->assertSame(1, $this->mails());
        TrackingHealth::check($this->st(60));
        $this->assertSame(2, $this->mails());
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $this->assertSame(3, $this->mails());
        $this->assertTrue(TrackingHealth::isStopped());
        // detected_at differs => new notice id
    }

    public function test_second_outage_gets_new_notice_id_and_old_dismissal_is_cleared()
    {
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $id1 = TrackingHealth::getNoticeId();
        update_option('wp_statistics_dismissed_notices', [$id1, 'keep_me']);
        TrackingHealth::check($this->st(60));
        $this->assertSame(['keep_me'], get_option('wp_statistics_dismissed_notices'));
        sleep(1);
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $this->assertNotSame($id1, TrackingHealth::getNoticeId());
    }

    public function test_dismiss_then_still_stopped_next_day_stays_dismissed()
    {
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $id = TrackingHealth::getNoticeId();
        update_option('wp_statistics_dismissed_notices', [$id]);
        TrackingHealth::check($this->st(54 * HOUR_IN_SECONDS));
        $this->assertSame($id, TrackingHealth::getNoticeId());
        $this->assertContains($id, get_option('wp_statistics_dismissed_notices'));
    }

    public function test_race_two_overlapping_checks_send_one_email()
    {
        // A second run starts while the first one is sending: it must not send again.
        $inner = false;
        add_filter('pre_wp_mail', function ($return, $atts) use (&$inner) {
            if (!$inner) {
                $inner = true;
                TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
            }

            return null;
        }, 10, 2);
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $this->assertSame(1, $this->mails(), 'two overlapping checks sent ' . $this->mails() . ' emails');
    }

    public function test_race_loser_of_the_claim_does_not_send()
    {
        // Another check already recorded the outage and has sent its email.
        update_option(TrackingHealth::STATE_OPTION, ['stopped_since' => gmdate('Y-m-d H:i:s', time() - 30 * HOUR_IN_SECONDS), 'detected_at' => time(), 'visitors_week_before' => 700, 'notified' => true]);
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $this->assertSame(0, $this->mails());
    }

    public function test_race_sequential_second_call_is_blocked()
    {
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $this->assertSame(1, $this->mails());
    }

    public function corruptStates()
    {
        return [
            'string'            => ['garbage'],
            'int'               => [5],
            'true'              => [true],
            'empty array'       => [[]],
            'no stopped_since'  => [['detected_at' => 5]],
            'empty stopped'     => [['stopped_since' => '', 'detected_at' => 5]],
            'null'              => [null],
        ];
    }

    /**
     * @dataProvider corruptStates
     */
    public function test_corrupt_state_option_treated_as_no_outage($value)
    {
        update_option(TrackingHealth::STATE_OPTION, $value);
        $this->assertFalse(TrackingHealth::isStopped());
        $this->assertIsString(TrackingHealth::getNoticeId());
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $this->assertSame(1, $this->mails());
        $this->assertTrue(TrackingHealth::isStopped());
    }

    public function test_corrupt_state_with_garbage_date_recovers_without_crash()
    {
        update_option(TrackingHealth::STATE_OPTION, ['stopped_since' => 'not a date', 'detected_at' => 1, 'notified' => true]);
        TrackingHealth::check($this->st(60));
        $this->assertSame(1, $this->mails());
        $body = $this->mail(0)->body;
        $this->assertStringNotContainsString('1970', $body, 'garbage stopped_since shows epoch date in the email');
    }

    public function test_corrupt_state_with_array_date_does_not_fatal()
    {
        update_option(TrackingHealth::STATE_OPTION, ['stopped_since' => ['x'], 'detected_at' => ['y']]);
        $this->assertFalse(TrackingHealth::isStopped(), 'bad shape counts as no outage');
        $this->assertIsString(TrackingHealth::getNoticeId());
        $this->assertSame([], TrackingHealth::getState());

        // The notice code path and the check both survive it, and a real outage can still be recorded.
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $this->assertTrue(TrackingHealth::isStopped());
        $this->assertSame(1, $this->mails());
    }

    public function test_setting_off_at_check_time_keeps_the_outage_and_sends_nothing()
    {
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        Option::update(TrackingHealth::SETTING, '');
        $this->assertNull(TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS)));
        $this->assertNull(TrackingHealth::check($this->st(60)));
        $this->assertSame(1, $this->mails());
        $this->assertTrue(TrackingHealth::isStopped(), 'the outage is kept while the setting is off');
    }

    public function test_setting_values_off_variants()
    {
        foreach (['', '0', 0, false, null] as $v) {
            Option::update(TrackingHealth::SETTING, $v);
            $this->assertFalse(TrackingHealth::isEnabled(), 'value ' . var_export($v, true));
        }
        foreach ([true, '1', 1, 'on'] as $v) {
            Option::update(TrackingHealth::SETTING, $v);
            $this->assertTrue(TrackingHealth::isEnabled(), 'value ' . var_export($v, true));
        }
    }

    public function test_off_when_option_never_saved()
    {
        $opts = get_option('wp_statistics', []);
        unset($opts[TrackingHealth::SETTING]);
        update_option('wp_statistics', $opts);
        $this->assertFalse(TrackingHealth::isEnabled());
    }

    public function test_new_install_defaults_on()
    {
        $this->assertTrue(\WP_STATISTICS\Option::defaultOption()[TrackingHealth::SETTING]);
    }

    private function runUpdateRule()
    {
        $updater = (new \ReflectionClass(\WP_Statistics\Core\Operations\Updater::class))->newInstanceWithoutConstructor();
        $method  = new \ReflectionMethod($updater, 'keepTrackingAlertOffOnUpdate');
        $method->setAccessible(true);
        $method->invoke($updater);
    }

    public function test_update_stores_off_when_never_saved()
    {
        $opts = get_option('wp_statistics', []);
        unset($opts[TrackingHealth::SETTING]);
        update_option('wp_statistics', $opts);
        $this->runUpdateRule();
        $this->assertArrayHasKey(TrackingHealth::SETTING, get_option('wp_statistics'));
        $this->assertFalse(TrackingHealth::isEnabled());
    }

    public function test_update_keeps_saved_choice()
    {
        Option::update(TrackingHealth::SETTING, true);
        $this->runUpdateRule();
        $this->assertTrue(TrackingHealth::isEnabled());
        Option::update(TrackingHealth::SETTING, false);
        $this->runUpdateRule();
        $this->assertFalse(TrackingHealth::isEnabled());
    }

    public function test_toggle_off_while_stopped_then_on_does_not_resend_for_same_outage()
    {
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $this->assertSame(1, $this->mails());
        Option::update(TrackingHealth::SETTING, '');
        \WP_STATISTICS\Schedule::get_instance()->maybe_schedule_hooks();
        $this->assertTrue(TrackingHealth::isStopped(), 'outage kept when turned off');
        Option::update(TrackingHealth::SETTING, true);
        \WP_STATISTICS\Schedule::get_instance()->maybe_schedule_hooks();
        TrackingHealth::check($this->st(54 * HOUR_IN_SECONDS));
        $this->assertSame(1, $this->mails(), 'same outage is not emailed again after toggling off/on');
    }

    public function test_toggle_off_while_stopped_recovery_after_turning_on_sends_one_email()
    {
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        Option::update(TrackingHealth::SETTING, '');
        \WP_STATISTICS\Schedule::get_instance()->maybe_schedule_hooks();
        $this->assertNull(TrackingHealth::check($this->st(60)), 'no check, no recovery email while off');
        $this->assertSame(1, $this->mails());
        Option::update(TrackingHealth::SETTING, true);
        \WP_STATISTICS\Schedule::get_instance()->maybe_schedule_hooks();
        TrackingHealth::check($this->st(60));
        $this->assertSame(2, $this->mails(), 'one recovery email once the check runs again');
        $this->assertFalse(TrackingHealth::isStopped());
    }

    public function test_stopped_then_no_visits_clears_silently()
    {
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        TrackingHealth::check($this->st(null));
        $this->assertFalse(TrackingHealth::isStopped());
        $this->assertSame(1, $this->mails());
    }

    public function test_stopped_then_quiet_clears_silently()
    {
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS, 3));
        $this->assertFalse(TrackingHealth::isStopped());
        $this->assertSame(1, $this->mails());
    }

    public function test_deactivate_clears_hook_and_reactivate_reschedules_state_kept()
    {
        \WP_STATISTICS\Schedule::get_instance()->maybe_schedule_hooks();
        $this->assertNotFalse(wp_next_scheduled(TrackingHealth::HOOK));
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        \WP_Statistics::deactivator();
        $this->assertFalse(wp_next_scheduled(TrackingHealth::HOOK));
        $this->assertTrue(TrackingHealth::isStopped(), 'outage state survives deactivation');
        \WP_STATISTICS\Schedule::get_instance()->maybe_schedule_hooks();
        $this->assertNotFalse(wp_next_scheduled(TrackingHealth::HOOK));
        TrackingHealth::check($this->st(54 * HOUR_IN_SECONDS));
        $this->assertSame(1, $this->mails());
    }

    public function test_hook_not_duplicated_after_repeated_init()
    {
        for ($i = 0; $i < 5; $i++) {
            \WP_STATISTICS\Schedule::get_instance()->maybe_schedule_hooks();
            Option::update('email_list', 'x' . $i . '@example.com');
        }
        $crons = _get_cron_array();
        $n     = 0;
        foreach ($crons as $ts => $hooks) {
            if (isset($hooks[TrackingHealth::HOOK])) {
                $n += count($hooks[TrackingHealth::HOOK]);
            }
        }
        $this->assertSame(1, $n);
        $this->assertSame('daily', wp_get_schedule(TrackingHealth::HOOK));
    }

    public function test_cron_hook_runs_check_via_do_action()
    {
        \WP_STATISTICS\Schedule::get_instance()->maybe_schedule_hooks();
        add_filter('wp_statistics_tracking_health_status', function () {
            return $this->st(30 * HOUR_IN_SECONDS);
        });
        do_action(TrackingHealth::HOOK); // cron calls with no args
        $this->assertSame(1, $this->mails());
    }

    public function test_cron_passes_empty_string_arg_is_handled()
    {
        $this->steadySite(3 * DAY_IN_SECONDS);
        $this->assertSame('stopped', TrackingHealth::check(''));
        $this->assertSame('stopped', TrackingHealth::check(false));
        $this->assertSame(1, $this->mails());
    }

    public function test_uninstaller_removes_option_and_hook()
    {
        update_option(TrackingHealth::STATE_OPTION, ['stopped_since' => '2020-01-01 00:00:00', 'detected_at' => 1]);
        wp_schedule_event(time() + 3600, 'daily', TrackingHealth::HOOK);
        $src = file_get_contents(WP_STATISTICS_DIR . 'src/Core/Operations/Uninstaller.php');
        $this->assertStringContainsString("delete_option('wp_statistics_tracking_health')", $src);
        $this->assertStringContainsString("wp_clear_scheduled_hook('wp_statistics_tracking_health_hook')", $src);
    }

    // ---------- email ----------

    private function lastTo($i = 0)
    {
        $m = $this->mail($i);
        return $m->to;
    }

    public function test_recipients_single_multiple_spaces_duplicates_invalid()
    {
        $cases = [
            'single'   => ['a@example.com', 1],
            'several'  => ['a@example.com,b@example.com', 2],
            'spaces'   => ['a@example.com , b@example.com', 2],
            'dups'     => ['a@example.com,a@example.com', 1],
            'invalid'  => ['nope, b@example.com', 1],
            'trailing' => ['a@example.com,', 1],
        ];
        foreach ($cases as $name => [$list, $expected]) {
            reset_phpmailer_instance();
            delete_option(TrackingHealth::STATE_OPTION);
            Option::update('email_list', $list);
            TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
            $m = tests_retrieve_phpmailer_instance();
            $this->assertCount(1, $m->mock_sent, $name);
            $this->assertCount($expected, $m->get_sent(0)->to, $name . ': ' . json_encode($m->get_sent(0)->to));
        }
    }

    public function test_empty_recipient_list_falls_back_to_admin_email()
    {
        Option::update('email_list', '');
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $this->assertSame(1, $this->mails());
        $this->assertSame(get_bloginfo('admin_email'), $this->mail(0)->to[0][0]);
        $this->assertSame(get_bloginfo('admin_email'), Option::get('email_list'), 'getEmailNotification writes the admin email into email_list as a side effect');
    }

    public function test_all_invalid_recipients_keeps_outage_open_and_not_notified()
    {
        Option::update('email_list', 'nope,also nope');
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $this->assertSame(0, $this->mails());
        $this->assertTrue(TrackingHealth::isStopped(), 'outage open, so the notice still shows');
        $this->assertFalse(TrackingHealth::getState()['notified']);
    }

    public function test_receivers_filter_empty_means_no_mail_outage_not_notified()
    {
        add_filter('wp_statistics_tracking_health_email_receivers', '__return_empty_string');
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $this->assertSame(0, $this->mails());
        $this->assertTrue(TrackingHealth::isStopped());
        $this->assertFalse(TrackingHealth::getState()['notified']);
    }

    public function test_wp_mail_failure_keeps_outage_open_and_retries_next_run()
    {
        add_filter('pre_wp_mail', '__return_false');
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $this->assertTrue(TrackingHealth::isStopped(), 'outage open');
        $this->assertFalse(TrackingHealth::getState()['notified'], 'not marked as notified');
        remove_all_filters('pre_wp_mail');
        TrackingHealth::check($this->st(54 * HOUR_IN_SECONDS));
        $this->assertSame(1, $this->mails(), 'retried on the next run');
        TrackingHealth::check($this->st(78 * HOUR_IN_SECONDS));
        $this->assertSame(1, $this->mails(), 'and then not again');
    }

    public function test_site_name_special_chars_subject_and_body()
    {
        $names = [
            'html'     => '<script>alert(1)</script> & "Q" \'s\'',
            'unicode'  => 'Ünïcødé 日本語 العربية',
            'emoji'    => 'Shop 🚀🔥',
            'newline'  => "Evil\r\nBcc: attacker@example.com\r\nX-Injected: 1",
            'long'     => str_repeat('Very long site title ', 40),
            'percent'  => '100% off %s %1$s {{site_title}}',
            'dollar'   => 'Price $1 \\1',
        ];
        foreach ($names as $k => $name) {
            reset_phpmailer_instance();
            delete_option(TrackingHealth::STATE_OPTION);
            update_option('blogname', $name);
            TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
            $m = tests_retrieve_phpmailer_instance();
            $this->assertCount(1, $m->mock_sent, $k);
            $sent = $m->get_sent(0);
            $sent->body = quoted_printable_decode($sent->body);
            $this->assertDoesNotMatchRegularExpression('/^(Bcc|X-Injected):/mi', $sent->header, "$k header injection: " . $sent->header);
            $this->assertStringNotContainsString('<script>', $sent->body, "$k body unescaped script");
            $this->assertStringNotContainsString('<script>', $sent->subject === '' ? 'x' : 'x');
        }
        update_option('blogname', 'Test Blog');
    }

    public function test_subject_mustache_in_site_name_is_not_expanded()
    {
        update_option('blogname', 'My {{site_title}} site');
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $this->assertStringContainsString('{{site_title}}', $this->mail(0)->subject);
    }

    public function test_content_type_is_html_and_from_follows_wp_defaults()
    {
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $m = $this->mail(0);
        $this->assertStringContainsString('text/html', $m->header);
        $this->assertMatchesRegularExpression('/^From: .*wordpress@/mi', $m->header);
    }

    public function test_body_content_stopped_and_recovered()
    {
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS, 70));
        $stopped = $this->mail(0)->body;
        $this->assertStringContainsString('Visits have stopped being recorded', $stopped);
        $this->assertStringContainsString('about 10 visitors a day', $stopped);
        $this->assertStringContainsString('Last visit recorded', $stopped);
        $this->assertStringContainsString('Open the Tracker Debugger', $stopped);
        $this->assertDoesNotMatchRegularExpression('/%(\d+\$)?[sd]/', $stopped, 'no unfilled placeholders');
        reset_phpmailer_instance();
        TrackingHealth::check($this->st(60));
        $rec = $this->mail(0)->body;
        $this->assertStringContainsString('Visits are being recorded again', $rec);
        $this->assertStringContainsString('No visits recorded from', $rec);
        $this->assertStringContainsString('Visits recorded again', $rec);
    }

    public function test_debugger_link_with_client_side_tracking_off_uses_settings_link()
    {
        Option::update('use_cache_plugin', '');
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $b = $this->mail(0)->body;
        $this->assertStringContainsString('tracking_method_tr', $b);
        $this->assertStringContainsString('Open the tracking settings', $b);
        $this->assertStringNotContainsString('wps_tracker-debugger_page', $b);
    }

    public function test_usual_traffic_text_variants()
    {
        $this->assertSame('3 visitors in the week before', TrackingHealth::getUsualTrafficText(3));
        $this->assertSame('1 visitor in the week before', TrackingHealth::getUsualTrafficText(1));
        $this->assertSame('about 1 visitor a day', TrackingHealth::getUsualTrafficText(4));
        $this->assertSame('about 1 visitor a day', TrackingHealth::getUsualTrafficText(7));
        $this->assertSame('about 2 visitors a day', TrackingHealth::getUsualTrafficText(11));
        $this->assertSame('0 visitors in the week before', TrackingHealth::getUsualTrafficText(null));
        $this->assertStringContainsString('1,000', TrackingHealth::getUsualTrafficText(7000));
    }

    public function test_rtl_email_direction()
    {
        global $wp_locale;
        $old = $wp_locale->text_direction;
        $wp_locale->text_direction = 'rtl';
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $wp_locale->text_direction = $old;
        $this->assertStringContainsString('direction: rtl', $this->mail(0)->body);
        $this->assertStringContainsString('text-align: right', $this->mail(0)->body);
    }

    public function test_no_secrets_in_email()
    {
        Option::update('geoip_license_key', 'SECRETKEY123');
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $b = $this->mail(0)->body;
        foreach (['SECRETKEY123', 'DB_PASSWORD', 'nonce', 'user_pass'] as $s) {
            $this->assertStringNotContainsString($s, $b);
        }
        $this->assertStringNotContainsString('127.0.0.1', $b);
    }

    public function test_status_filter_returning_non_array_does_not_fatal()
    {
        add_filter('wp_statistics_tracking_health_status', '__return_null');
        try {
            $r = TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
            $this->assertIsString($r);
        } catch (\Throwable $e) {
            $this->fail('3rd-party filter returning null: ' . get_class($e) . ' ' . $e->getMessage());
        }
    }


    // ---------- notice (GeneralNotices::trackingStopped) ----------

    private function runNotices($page = 'wps_overview_page', $userRole = 'administrator')
    {
        global $pagenow;
        $pagenow           = 'admin.php';
        $_REQUEST['page']  = $page;
        $_GET['page']      = $page;
        wp_set_current_user($this->factory()->user->create(['role' => $userRole]));
        set_current_screen('dashboard');

        $ref = new \ReflectionProperty(\WP_Statistics\Service\Admin\NoticeHandler\Notice::class, 'adminNotices');
        $ref->setAccessible(true);
        $ref->setValue(null, []);
        $ds = new \ReflectionProperty(\WP_Statistics\Service\Admin\NoticeHandler\Notice::class, 'dismissedNotices');
        $ds->setAccessible(true);
        $ds->setValue(null, []);

        (new \WP_Statistics\Service\Admin\NoticeHandler\GeneralNotices())->init();

        $all = $ref->getValue();
        $out = [];
        foreach ($all as $id => $n) {
            if (strpos($id, 'tracking_stopped_') === 0) {
                $out[$id] = $n;
            }
        }
        return $out;
    }

    public function test_notice_shown_on_plugin_page_for_admin_only()
    {
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $this->assertCount(1, $this->runNotices('wps_overview_page', 'administrator'));
        $this->assertCount(0, $this->runNotices('wps_overview_page', 'editor'));
        $this->assertCount(0, $this->runNotices('wps_overview_page', 'subscriber'));
        $this->assertCount(0, $this->runNotices('some_other_page', 'administrator'));
    }

    public function test_notice_hidden_by_hide_notices_setting_and_setting_off()
    {
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        Option::update('hide_notices', true);
        $this->assertCount(0, $this->runNotices());
        Option::update('hide_notices', '');
        Option::update(TrackingHealth::SETTING, '');
        $this->assertCount(0, $this->runNotices(), 'alert setting off hides the notice even if state is still stored');
    }

    public function test_notice_escapes_date_format_and_is_dismissible_per_outage()
    {
        update_option('date_format', '\\<\\s\\c\\r\\i\\p\\t\\>\\a\\l\\e\\r\\t\\(\\1\\)\\<\\/\\s\\c\\r\\i\\p\\t\\>');
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $n = $this->runNotices();
        $this->assertCount(1, $n);
        $msg = reset($n)['message'];
        $this->assertStringNotContainsString('<script>', $msg);
        $this->assertTrue(reset($n)['is_dismissible']);
        // dismissed id hides it
        update_option('wp_statistics_dismissed_notices', [TrackingHealth::getNoticeId()]);
        $this->assertCount(0, $this->runNotices());
        update_option('date_format', 'F j, Y');
        TrackingHealth::check($this->st(60));
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $this->assertCount(1, $this->runNotices(), 'new outage shows again');
    }

    public function test_notice_with_corrupt_state_does_not_fatal_the_admin()
    {
        update_option(TrackingHealth::STATE_OPTION, ['stopped_since' => ['x'], 'detected_at' => 1]);
        try {
            $this->runNotices();
            $this->assertTrue(true);
        } catch (\Throwable $e) {
            $this->fail('admin notice crashed on corrupt state: ' . get_class($e) . ': ' . $e->getMessage());
        }
    }

    public function test_email_escapes_date_format()
    {
        update_option('date_format', '\\<\\s\\c\\r\\i\\p\\t\\>');
        TrackingHealth::check($this->st(30 * HOUR_IN_SECONDS));
        $this->assertStringNotContainsString('<script>', $this->mail(0)->body);
        update_option('date_format', 'F j, Y');
    }

    // ---------- SQL safety ----------

    public function test_recording_status_sql_is_prepared()
    {
        $src = file_get_contents(WP_STATISTICS_DIR . 'src/Service/Debugger/Provider/VisitorProvider.php');
        $this->assertSame(5, substr_count($src, '$wpdb->prepare('));
        $this->assertStringNotContainsString('$_GET', $src);
        $this->assertStringNotContainsString('$_POST', $src);
        $this->assertStringNotContainsString('$_REQUEST', $src);
    }
}
