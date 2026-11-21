<?php

namespace WP_Statistics\Tests\Service\TrackerDebugger;

use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\TrackerDebugger\TrackingHealth;
use WP_Statistics\Service\Debugger\Provider\VisitorProvider;
use WP_UnitTestCase;

/**
 * The daily "visits stopped" check: one email when visits stop, none while they stay
 * stopped, one when they come back. Mail is caught with pre_wp_mail, nothing is sent.
 */
class Test_TrackingHealth extends WP_UnitTestCase
{
    /** @var array[] */
    private $mails = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->mails = [];
        add_filter('pre_wp_mail', [$this, 'catchMail'], 10, 2);

        Option::update('email_list', 'alerts@example.com');
        Option::update(TrackingHealth::SETTING, true);
        Option::update('use_cache_plugin', true);
        delete_option(TrackingHealth::STATE_OPTION);
        delete_option('wp_statistics_dismissed_notices');
    }

    public function tearDown(): void
    {
        remove_filter('pre_wp_mail', [$this, 'catchMail'], 10);
        delete_option(TrackingHealth::STATE_OPTION);
        wp_clear_scheduled_hook(TrackingHealth::HOOK);

        parent::tearDown();
    }

    public function catchMail($return, $atts)
    {
        $this->mails[] = $atts;

        return true;
    }

    private function status($secondsSince, $visitorsWeekBefore = 700, $error = false)
    {
        $now = current_time('timestamp');

        return [
            'last_visit'           => $secondsSince === null ? null : gmdate('Y-m-d H:i:s', $now - $secondsSince),
            'seconds_since'        => $secondsSince,
            'visitors_24h'         => $secondsSince !== null && $secondsSince <= DAY_IN_SECONDS ? 5 : 0,
            'visitors_week_before' => $secondsSince !== null && $secondsSince > DAY_IN_SECONDS ? $visitorsWeekBefore : null,
            'error'                => $error,
        ];
    }

    public function test_state_rule_matches_the_debugger_card()
    {
        $this->assertSame('error', VisitorProvider::getRecordingState($this->status(3 * DAY_IN_SECONDS, 700, true)));
        $this->assertSame('no_visits', VisitorProvider::getRecordingState($this->status(null)));
        $this->assertSame('working', VisitorProvider::getRecordingState($this->status(HOUR_IN_SECONDS)));
        $this->assertSame('quiet', VisitorProvider::getRecordingState($this->status(30 * HOUR_IN_SECONDS, 3)));
        $this->assertSame('stopped', VisitorProvider::getRecordingState($this->status(30 * HOUR_IN_SECONDS, 70)));
        $this->assertSame('stopped', VisitorProvider::getRecordingState($this->status(3 * DAY_IN_SECONDS, 3)));
    }

    public function test_one_email_per_outage_and_one_on_recovery()
    {
        // Visits stop: one email.
        $this->assertSame('stopped', TrackingHealth::check($this->status(30 * HOUR_IN_SECONDS)));
        $this->assertCount(1, $this->mails);
        $this->assertSame('alerts@example.com', is_array($this->mails[0]['to']) ? implode(',', $this->mails[0]['to']) : $this->mails[0]['to']);
        $this->assertStringContainsString('No visits recorded since', $this->mails[0]['subject']);
        $this->assertStringContainsString('about 100 visitors a day', $this->mails[0]['message']);
        $this->assertStringContainsString('wps_tracker-debugger_page', $this->mails[0]['message']);
        $this->assertStringContainsString('Settings → Email Reports', $this->mails[0]['message']);
        $this->assertStringNotContainsString('Settings → Notifications', $this->mails[0]['message']);
        $this->assertTrue(TrackingHealth::isStopped());

        $stoppedSince = TrackingHealth::getState()['stopped_since'];

        // Still stopped the next days: no more email, outage start unchanged.
        TrackingHealth::check($this->status(54 * HOUR_IN_SECONDS));
        TrackingHealth::check($this->status(78 * HOUR_IN_SECONDS));
        $this->assertCount(1, $this->mails);
        $this->assertSame($stoppedSince, TrackingHealth::getState()['stopped_since']);

        // Visits come back: one email, state cleared.
        $this->assertSame('working', TrackingHealth::check($this->status(MINUTE_IN_SECONDS)));
        $this->assertCount(2, $this->mails);
        $this->assertStringContainsString('Visits are being recorded again', $this->mails[1]['subject']);
        $this->assertFalse(TrackingHealth::isStopped());

        // Still working: nothing new.
        TrackingHealth::check($this->status(MINUTE_IN_SECONDS));
        $this->assertCount(2, $this->mails);
    }

    public function test_failed_query_changes_nothing()
    {
        TrackingHealth::check($this->status(3 * DAY_IN_SECONDS, 700, true));
        $this->assertCount(0, $this->mails);
        $this->assertFalse(TrackingHealth::isStopped());

        // During an outage, a failed check neither recovers nor re-sends.
        TrackingHealth::check($this->status(30 * HOUR_IN_SECONDS));
        TrackingHealth::check($this->status(3 * DAY_IN_SECONDS, 700, true));
        $this->assertCount(1, $this->mails);
        $this->assertTrue(TrackingHealth::isStopped());
    }

    public function test_quiet_and_empty_sites_get_no_alert()
    {
        TrackingHealth::check($this->status(null));
        TrackingHealth::check($this->status(30 * HOUR_IN_SECONDS, 3));
        $this->assertCount(0, $this->mails);
        $this->assertFalse(TrackingHealth::isStopped());
    }

    public function test_setting_off_stops_checking_but_keeps_the_outage()
    {
        TrackingHealth::check($this->status(30 * HOUR_IN_SECONDS));
        $this->assertCount(1, $this->mails);

        Option::update(TrackingHealth::SETTING, '');

        // Off: no check, no email, the open outage is kept.
        $this->assertNull(TrackingHealth::check($this->status(MINUTE_IN_SECONDS)));
        $this->assertCount(1, $this->mails);
        $this->assertTrue(TrackingHealth::isStopped());

        // On again while visits are still stopped: still one email for this outage.
        Option::update(TrackingHealth::SETTING, true);
        TrackingHealth::check($this->status(54 * HOUR_IN_SECONDS));
        $this->assertCount(1, $this->mails);

        // Visits back: the recovery email goes out, even though the setting was off in between.
        TrackingHealth::check($this->status(MINUTE_IN_SECONDS));
        $this->assertCount(2, $this->mails);
        $this->assertFalse(TrackingHealth::isStopped());
    }

    public function test_failed_stopped_email_is_retried_on_the_next_run_only()
    {
        remove_filter('pre_wp_mail', [$this, 'catchMail'], 10);
        add_filter('pre_wp_mail', '__return_false');

        TrackingHealth::check($this->status(30 * HOUR_IN_SECONDS));
        $this->assertTrue(TrackingHealth::isStopped(), 'the outage is open, so the notice shows');
        $this->assertFalse(TrackingHealth::getState()['notified']);

        // The next run tries again.
        remove_filter('pre_wp_mail', '__return_false');
        add_filter('pre_wp_mail', [$this, 'catchMail'], 10, 2);
        TrackingHealth::check($this->status(30 * HOUR_IN_SECONDS));
        $this->assertCount(1, $this->mails, 'retry sent the stopped email');
        $this->assertTrue(TrackingHealth::getState()['notified']);

        TrackingHealth::check($this->status(54 * HOUR_IN_SECONDS));
        $this->assertCount(1, $this->mails, 'no second email after a successful retry');
    }

    public function test_outage_that_was_never_emailed_ends_without_a_recovery_email()
    {
        remove_filter('pre_wp_mail', [$this, 'catchMail'], 10);
        add_filter('pre_wp_mail', '__return_false');
        TrackingHealth::check($this->status(30 * HOUR_IN_SECONDS));
        remove_filter('pre_wp_mail', '__return_false');
        add_filter('pre_wp_mail', [$this, 'catchMail'], 10, 2);

        TrackingHealth::check($this->status(MINUTE_IN_SECONDS));

        $this->assertCount(0, $this->mails);
        $this->assertFalse(TrackingHealth::isStopped());
    }

    public function test_a_check_that_overlaps_another_one_sends_nothing()
    {
        $inner = false;
        remove_filter('pre_wp_mail', [$this, 'catchMail'], 10);
        add_filter('pre_wp_mail', function ($return, $atts) use (&$inner) {
            $this->mails[] = $atts;

            // A second daily run starts while the first is still sending.
            if (!$inner) {
                $inner = true;
                TrackingHealth::check($this->status(30 * HOUR_IN_SECONDS));
            }

            return true;
        }, 10, 2);

        TrackingHealth::check($this->status(30 * HOUR_IN_SECONDS));

        $this->assertCount(1, $this->mails);
        $this->assertTrue(TrackingHealth::getState()['notified']);
        $this->assertFalse(get_option(TrackingHealth::LOCK_OPTION, false), 'lock released');
    }

    public function test_corrupt_state_is_treated_as_no_state()
    {
        update_option(TrackingHealth::STATE_OPTION, ['stopped_since' => ['x'], 'detected_at' => ['y']]);

        $this->assertFalse(TrackingHealth::isStopped());
        $this->assertSame([], TrackingHealth::getState());
        $this->assertIsString(TrackingHealth::getNoticeId());
    }

    public function test_dismissed_notice_is_per_outage()
    {
        TrackingHealth::check($this->status(30 * HOUR_IN_SECONDS));
        $noticeId = TrackingHealth::getNoticeId();
        update_option('wp_statistics_dismissed_notices', ['other_notice', $noticeId]);

        TrackingHealth::check($this->status(MINUTE_IN_SECONDS));

        $this->assertSame(['other_notice'], get_option('wp_statistics_dismissed_notices'));
    }

    public function test_daily_event_follows_the_setting()
    {
        wp_clear_scheduled_hook(TrackingHealth::HOOK);

        \WP_STATISTICS\Schedule::get_instance()->maybe_schedule_hooks();
        $this->assertSame('daily', wp_get_schedule(TrackingHealth::HOOK));

        Option::update(TrackingHealth::SETTING, '');
        \WP_STATISTICS\Schedule::get_instance()->maybe_schedule_hooks();
        $this->assertFalse(wp_next_scheduled(TrackingHealth::HOOK));
    }
}
