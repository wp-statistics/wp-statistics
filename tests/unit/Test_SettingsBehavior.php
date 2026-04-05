<?php

namespace WP_Statistics\Tests\SettingsBehavior;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use WP_UnitTestCase;
use WP_Statistics\Components\Ip;
use WP_Statistics\Components\Option;
use WP_Statistics\Service\Assets\Handlers\FrontendHandler;
use WP_Statistics\Service\Consent\TrackingLevel;
use WP_Statistics\Service\Cron\Events\DatabaseMaintenanceEvent;
use WP_Statistics\Service\Cron\Events\EmailReportEvent;
use WP_Statistics\Service\Tracking\Core\Exclusions;
use WP_Statistics\Service\Tracking\Core\Payload;
use WP_Statistics\Service\Tracking\Core\RateLimiter;
use WP_Statistics\Service\Tracking\Core\Visitor;
use WP_Statistics\Entity\Visitor as VisitorEntity;
use WP_Statistics\Entity\Session as SessionEntity;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Service\Database\DatabaseSchema;

/**
 * Behavioral tests for WP Statistics settings.
 *
 * Each test toggles a setting ON or OFF, calls the code that is gated by
 * that setting, and asserts the downstream behavioral change — not just
 * that the option was stored, but that it actually affects plugin behavior.
 */
class Test_SettingsBehavior extends WP_UnitTestCase
{
    private $originalServer = [];

    public function setUp(): void
    {
        parent::setUp();

        update_option('wp_statistics', []);

        $this->originalServer = $_SERVER;
        $_SERVER['REMOTE_ADDR']     = '1.2.3.4';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36';

        $this->resetExclusionsState();
        $this->resetIpCache();
    }

    public function tearDown(): void
    {
        $_SERVER = $this->originalServer;
        remove_all_filters('the_content');
        parent::tearDown();
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    private function resetExclusionsState(): void
    {
        $rc = new ReflectionClass(Exclusions::class);

        $statics = [
            'exclusionMap'        => null,
            'options'             => [],
            'exclusionResult'     => null,
            'excludedUrlPatterns' => null,
            'excludedCountries'   => null,
            'includedCountries'   => null,
        ];

        foreach ($statics as $name => $default) {
            $prop = $rc->getProperty($name);
            $prop->setValue(null, $default);
        }

        // Reset singleton instance so check() can be called fresh
        if ($rc->hasProperty('instance')) {
            $prop = $rc->getProperty('instance');
            $prop->setValue(null, null);
        }
    }

    private function setExclusionsOptions(array $opts): void
    {
        $prop = new ReflectionProperty(Exclusions::class, 'options');
        $prop->setValue(null, $opts);
    }

    private function resetIpCache(): void
    {
        $rc = new ReflectionClass(Ip::class);

        foreach (['cachedIpMethod', 'currentIp'] as $name) {
            if ($rc->hasProperty($name)) {
                $prop = $rc->getProperty($name);
                $prop->setValue(null, null);
            }
        }
    }

    private function createVisitorWithCountry(string $countryCode): Visitor
    {
        $visitor = new Visitor(null);
        $prop    = new ReflectionProperty(Visitor::class, 'cache');
        $prop->setValue($visitor, [
            'location' => ['country_code' => $countryCode],
        ]);

        return $visitor;
    }

    private function createVisitorWithPayload(array $overrides = []): Visitor
    {
        $defaults = [
            'resourceType'  => 'page',
            'resourceUri'   => '/',
            'resourceUriId' => 1,
            'resourceId'    => 1,
            'referrer'      => '',
            'timezone'      => 'UTC',
            'languageCode'  => 'en',
            'languageName'  => 'English',
            'screenWidth'   => '1920',
            'screenHeight'  => '1080',
            'userId'        => 0,
            'trackingLevel' => TrackingLevel::FULL,
        ];

        $data = array_merge($defaults, $overrides);

        $payload = (new ReflectionClass(Payload::class))->newInstanceWithoutConstructor();

        $rc = new ReflectionClass(Payload::class);
        foreach ($data as $prop => $value) {
            if ($rc->hasProperty($prop)) {
                $rp = $rc->getProperty($prop);
                $rp->setValue($payload, $value);
            }
        }

        return new Visitor($payload);
    }

    // ═════════════════════════════════════════════════════════════════
    // GROUP 1: TRACKING / DATA COLLECTION
    // ═════════════════════════════════════════════════════════════════

    public function test_store_ip_on_returns_ip()
    {
        Option::updateValue('store_ip', true);
        $this->resetIpCache();

        $ip = Ip::getStorableIp();

        $this->assertNotNull($ip);
        $this->assertSame('1.2.3.4', $ip);
    }

    public function test_store_ip_off_returns_null()
    {
        Option::updateValue('store_ip', false);
        $this->resetIpCache();

        $this->assertNull(Ip::getStorableIp());
    }

    public function test_visitors_log_on_returns_user_id()
    {
        Option::updateValue('visitors_log', true);

        $userId  = 42;
        $visitor = $this->createVisitorWithPayload([
            'userId'        => $userId,
            'trackingLevel' => TrackingLevel::FULL,
        ]);

        $this->assertSame($userId, $visitor->getUserId());
    }

    public function test_visitors_log_off_returns_null()
    {
        Option::updateValue('visitors_log', false);

        $visitor = $this->createVisitorWithPayload([
            'userId'        => 42,
            'trackingLevel' => TrackingLevel::FULL,
        ]);

        $this->assertNull($visitor->getUserId());
    }

    public function test_visitors_log_on_but_anonymous_level_returns_null()
    {
        Option::updateValue('visitors_log', true);

        $visitor = $this->createVisitorWithPayload([
            'userId'        => 42,
            'trackingLevel' => TrackingLevel::ANONYMOUS,
        ]);

        $this->assertNull($visitor->getUserId());
    }

    public function test_rate_limiter_enabled_when_setting_on()
    {
        Option::updateValue('tracker_rate_limit', true);

        $this->assertTrue(RateLimiter::isEnabled());
    }

    public function test_rate_limiter_disabled_when_setting_off()
    {
        Option::updateValue('tracker_rate_limit', false);

        $this->assertFalse(RateLimiter::isEnabled());
    }

    public function test_rate_limiter_threshold_reads_setting()
    {
        Option::updateValue('tracker_rate_limit_threshold', 50);

        $method = new ReflectionMethod(RateLimiter::class, 'getThreshold');

        $this->assertSame(50, $method->invoke(null));
    }

    // ═════════════════════════════════════════════════════════════════
    // GROUP 2: EXCLUSIONS — Feeds
    // ═════════════════════════════════════════════════════════════════

    public function test_exclude_feeds_on_excludes_feed_resource()
    {
        $this->setExclusionsOptions(['exclude_feeds' => true]);
        $visitor = $this->createVisitorWithPayload(['resourceType' => 'feed']);

        $this->assertTrue(Exclusions::exclusionFeed($visitor));
    }

    public function test_exclude_feeds_off_allows_feed_resource()
    {
        $this->setExclusionsOptions(['exclude_feeds' => false]);
        $visitor = $this->createVisitorWithPayload(['resourceType' => 'feed']);

        $this->assertFalse(Exclusions::exclusionFeed($visitor));
    }

    // ═════════════════════════════════════════════════════════════════
    // GROUP 2: EXCLUSIONS — 404s
    // ═════════════════════════════════════════════════════════════════

    public function test_exclude_404s_on_excludes_404_resource()
    {
        $this->setExclusionsOptions(['exclude_404s' => true]);
        $visitor = $this->createVisitorWithPayload(['resourceType' => '404']);

        $this->assertTrue(Exclusions::exclusion404($visitor));
    }

    public function test_exclude_404s_off_allows_404_resource()
    {
        $this->setExclusionsOptions(['exclude_404s' => false]);
        $visitor = $this->createVisitorWithPayload(['resourceType' => '404']);

        $this->assertFalse(Exclusions::exclusion404($visitor));
    }

    // ═════════════════════════════════════════════════════════════════
    // GROUP 2: EXCLUSIONS — URL Patterns
    // ═════════════════════════════════════════════════════════════════

    public function test_excluded_urls_matches_pattern()
    {
        $this->setExclusionsOptions(['excluded_urls' => "test-page\nadmin/dashboard"]);
        $visitor = $this->createVisitorWithPayload(['resourceUri' => '/test-page']);

        $this->assertTrue(Exclusions::exclusionExcludedUrl($visitor));
    }

    public function test_excluded_urls_empty_allows_all()
    {
        $this->setExclusionsOptions(['excluded_urls' => '']);
        $visitor = $this->createVisitorWithPayload(['resourceUri' => '/some-page']);

        $this->assertFalse(Exclusions::exclusionExcludedUrl($visitor));
    }

    // ═════════════════════════════════════════════════════════════════
    // GROUP 2: EXCLUSIONS — Countries
    // ═════════════════════════════════════════════════════════════════

    public function test_excluded_countries_excludes_matching_country()
    {
        $this->setExclusionsOptions(['excluded_countries' => "US\nDE", 'included_countries' => '']);
        $visitor = $this->createVisitorWithCountry('US');

        $this->assertTrue(Exclusions::exclusionGeoIp($visitor));
    }

    public function test_excluded_countries_allows_non_matching_country()
    {
        $this->setExclusionsOptions(['excluded_countries' => "US\nDE", 'included_countries' => '']);
        $visitor = $this->createVisitorWithCountry('FR');

        $this->assertFalse(Exclusions::exclusionGeoIp($visitor));
    }

    public function test_included_countries_rejects_non_whitelisted()
    {
        $this->resetExclusionsState();
        $this->setExclusionsOptions(['excluded_countries' => '', 'included_countries' => "US\nCA"]);
        $visitor = $this->createVisitorWithCountry('DE');

        $this->assertTrue(Exclusions::exclusionGeoIp($visitor));
    }

    public function test_included_countries_allows_whitelisted()
    {
        $this->resetExclusionsState();
        $this->setExclusionsOptions(['excluded_countries' => '', 'included_countries' => "US\nCA"]);
        $visitor = $this->createVisitorWithCountry('US');

        $this->assertFalse(Exclusions::exclusionGeoIp($visitor));
    }

    // ═════════════════════════════════════════════════════════════════
    // GROUP 2: EXCLUSIONS — User Role / Anonymous
    // ═════════════════════════════════════════════════════════════════

    public function test_exclude_anonymous_on_excludes_logged_out()
    {
        $this->setExclusionsOptions(['exclude_anonymous_users' => true]);
        $visitor = $this->createVisitorWithPayload(['userId' => 0]);

        $this->assertTrue(Exclusions::exclusionUserRole($visitor));
    }

    public function test_exclude_anonymous_off_allows_logged_out()
    {
        $this->setExclusionsOptions(['exclude_anonymous_users' => false]);
        $visitor = $this->createVisitorWithPayload(['userId' => 0]);

        $this->assertFalse(Exclusions::exclusionUserRole($visitor));
    }

    // ═════════════════════════════════════════════════════════════════
    // GROUP 2: EXCLUSIONS — IP Match
    // ═════════════════════════════════════════════════════════════════

    public function test_exclude_ip_matches_current_ip()
    {
        $this->setExclusionsOptions(['exclude_ip' => "1.2.3.4\n10.0.0.1"]);

        $visitor = new Visitor(null);

        $this->assertTrue(Exclusions::exclusionIpMatch($visitor));
    }

    public function test_exclude_ip_empty_allows_all()
    {
        $this->setExclusionsOptions(['exclude_ip' => '']);

        $visitor = new Visitor(null);

        $this->assertFalse(Exclusions::exclusionIpMatch($visitor));
    }

    // ═════════════════════════════════════════════════════════════════
    // GROUP 2: EXCLUSIONS — Record Exclusions
    // ═════════════════════════════════════════════════════════════════

    public function test_record_exclusions_on_enables_recording()
    {
        $this->setExclusionsOptions(['record_exclusions' => true]);

        $this->assertTrue(Exclusions::isRecordActive());
    }

    public function test_record_exclusions_off_disables_recording()
    {
        $this->setExclusionsOptions(['record_exclusions' => false]);

        $this->assertFalse(Exclusions::isRecordActive());
    }

    // ═════════════════════════════════════════════════════════════════
    // GROUP 2: EXCLUSIONS — Robot Threshold
    // ═════════════════════════════════════════════════════════════════

    public function test_robot_threshold_zero_does_not_exclude()
    {
        $this->setExclusionsOptions(['robot_threshold' => 0]);
        $visitor = new Visitor(null);

        $this->assertFalse(Exclusions::exclusionRobotThreshold($visitor));
    }

    // ═════════════════════════════════════════════════════════════════
    // GROUP 3: EMAIL REPORTS & CRON
    // ═════════════════════════════════════════════════════════════════

    public function test_email_report_schedules_when_enabled()
    {
        Option::updateValue('email_reports_enabled', true);

        $event = new EmailReportEvent();

        $this->assertTrue($event->shouldSchedule());
    }

    public function test_email_report_skips_when_disabled()
    {
        Option::updateValue('email_reports_enabled', false);

        $event = new EmailReportEvent();

        $this->assertFalse($event->shouldSchedule());
    }

    public function test_email_report_frequency_reads_setting()
    {
        Option::updateValue('email_reports_frequency', 'monthly');

        $event = new EmailReportEvent();

        $this->assertSame('monthly', $event->getRecurrence());
    }

    public function test_email_report_frequency_defaults_to_weekly()
    {
        // Don't set the option — should fall back to default
        $event = new EmailReportEvent();

        $this->assertSame('weekly', $event->getRecurrence());
    }

    public function test_email_report_frequency_rejects_invalid_value()
    {
        Option::updateValue('email_reports_frequency', 'every_hour');

        $event = new EmailReportEvent();

        $this->assertSame('weekly', $event->getRecurrence(), 'Invalid frequency should fall back to weekly');
    }

    // ═════════════════════════════════════════════════════════════════
    // GROUP 4: DATA MANAGEMENT
    // ═════════════════════════════════════════════════════════════════

    public function test_retention_forever_does_not_schedule_maintenance()
    {
        Option::updateValue('data_retention_mode', 'forever');

        $event = new DatabaseMaintenanceEvent();

        $this->assertFalse($event->shouldSchedule());
    }

    public function test_retention_days_schedules_maintenance()
    {
        Option::updateValue('data_retention_mode', 'days');
        Option::updateValue('schedule_dbmaint', true);

        $event = new DatabaseMaintenanceEvent();

        $this->assertTrue($event->shouldSchedule());
    }

    public function test_retention_days_without_dbmaint_does_not_schedule()
    {
        Option::updateValue('data_retention_mode', 'days');
        Option::updateValue('schedule_dbmaint', false);

        $event = new DatabaseMaintenanceEvent();

        $this->assertFalse($event->shouldSchedule());
    }

    // ═════════════════════════════════════════════════════════════════
    // GROUP 5: FRONTEND HANDLER — Tracker Options
    // ═════════════════════════════════════════════════════════════════

    public function test_frontend_event_tracking_always_true()
    {
        $args = $this->callBuildOptionArgs();

        $this->assertTrue($args['eventTracking']);
    }

    public function test_frontend_anonymous_tracking_on()
    {
        Option::updateValue('anonymous_tracking', true);

        $args = $this->callBuildOptionArgs();

        $this->assertTrue($args['anonymousTracking']);
    }

    public function test_frontend_anonymous_tracking_off()
    {
        Option::updateValue('anonymous_tracking', false);

        $args = $this->callBuildOptionArgs();

        $this->assertFalse($args['anonymousTracking']);
    }

    private function callBuildOptionArgs(): array
    {
        $rc      = new ReflectionClass(FrontendHandler::class);
        $handler = $rc->newInstanceWithoutConstructor();

        $method = $rc->getMethod('buildOptionArgs');

        $provider = new class implements \WP_Statistics\Service\Consent\ConsentProviderInterface {
            public function getKey(): string { return 'none'; }
            public function getName(): string { return 'None'; }
            public function isAvailable(): bool { return false; }
            public function register(): void {}
            public function getJsDependencies(): array { return []; }
            public function getJsConfig(): array { return ['provider' => 'none']; }
            public function getInlineScript(): string { return ''; }
        };

        return $method->invoke($handler, $provider);
    }

    // ═════════════════════════════════════════════════════════════════
    // GROUP 6: DISPLAY — Show Hits
    // ═════════════════════════════════════════════════════════════════

    public function test_show_hits_on_attaches_content_filter()
    {
        Option::updateValue('show_hits', true);

        $handler = new FrontendHandler();

        $this->assertNotFalse(has_filter('the_content', [$handler, 'showHits']));
    }

    public function test_show_hits_off_does_not_attach_content_filter()
    {
        Option::updateValue('show_hits', false);

        $handler = new FrontendHandler();

        $this->assertFalse(has_filter('the_content', [$handler, 'showHits']));
    }

    // ═════════════════════════════════════════════════════════════════
    // GROUP 7: DATABASE STORAGE VERIFICATION
    //
    // These tests go through the Entity write layer and then SELECT
    // the row to verify the actual stored data reflects the setting.
    // ═════════════════════════════════════════════════════════════════

    public function test_stored_visitor_has_ip_when_store_ip_on()
    {
        Option::updateValue('store_ip', true);
        $this->resetIpCache();

        $visitor = $this->createVisitorWithPayload(['trackingLevel' => TrackingLevel::FULL]);
        $entity  = new VisitorEntity($visitor);
        $id      = $entity->record();

        $this->assertGreaterThan(0, $id);

        $row = $this->getVisitorRow($id);
        $this->assertNotNull($row, 'Visitor row should exist in DB');
        $this->assertSame('1.2.3.4', $row->ip, 'IP should be stored when store_ip is enabled');
    }

    public function test_stored_visitor_has_no_ip_when_store_ip_off()
    {
        Option::updateValue('store_ip', false);
        $this->resetIpCache();

        $visitor = $this->createVisitorWithPayload(['trackingLevel' => TrackingLevel::FULL]);
        $entity  = new VisitorEntity($visitor);
        $id      = $entity->record();

        $this->assertGreaterThan(0, $id);

        $row = $this->getVisitorRow($id);
        $this->assertNotNull($row, 'Visitor row should exist in DB');
        $this->assertEmpty($row->ip, 'IP should not be stored when store_ip is disabled');
        $this->assertNotSame('1.2.3.4', $row->ip, 'Real IP must not appear in DB');
    }

    public function test_stored_session_has_user_id_when_visitors_log_on()
    {
        Option::updateValue('visitors_log', true);

        $userId  = 42;
        $visitor = $this->createVisitorWithPayload([
            'userId'        => $userId,
            'trackingLevel' => TrackingLevel::FULL,
        ]);

        $entity    = new VisitorEntity($visitor);
        $visitorId = $entity->record();
        $this->assertGreaterThan(0, $visitorId);

        $sessionId = $this->insertSession($visitor, $visitorId);
        $this->assertGreaterThan(0, $sessionId);

        $row = $this->getSessionRow($sessionId);
        $this->assertNotNull($row, 'Session row should exist in DB');
        $this->assertEquals($userId, (int) $row->user_id, 'user_id should be stored when visitors_log is enabled');
    }

    public function test_stored_session_has_no_user_id_when_visitors_log_off()
    {
        Option::updateValue('visitors_log', false);

        $visitor = $this->createVisitorWithPayload([
            'userId'        => 42,
            'trackingLevel' => TrackingLevel::FULL,
        ]);

        $entity    = new VisitorEntity($visitor);
        $visitorId = $entity->record();
        $this->assertGreaterThan(0, $visitorId);

        $sessionId = $this->insertSession($visitor, $visitorId);
        $this->assertGreaterThan(0, $sessionId);

        $row = $this->getSessionRow($sessionId);
        $this->assertNotNull($row, 'Session row should exist in DB');
        $this->assertEmpty((int) $row->user_id, 'user_id should be 0/empty when visitors_log is disabled');
        $this->assertNotEquals(42, (int) $row->user_id, 'Real user ID must not appear in DB');
    }

    public function test_stored_session_has_no_user_id_when_anonymous_tracking_level()
    {
        Option::updateValue('visitors_log', true);

        $visitor = $this->createVisitorWithPayload([
            'userId'        => 42,
            'trackingLevel' => TrackingLevel::ANONYMOUS,
        ]);

        $entity    = new VisitorEntity($visitor);
        $visitorId = $entity->record();
        $this->assertGreaterThan(0, $visitorId);

        $sessionId = $this->insertSession($visitor, $visitorId);
        $this->assertGreaterThan(0, $sessionId);

        $row = $this->getSessionRow($sessionId);
        $this->assertNotNull($row, 'Session row should exist in DB');
        $this->assertEmpty((int) $row->user_id, 'user_id should be 0/empty when tracking level is anonymous');
        $this->assertNotEquals(42, (int) $row->user_id, 'Real user ID must not appear in DB');
    }

    public function test_stored_visitor_has_no_ip_when_anonymous_tracking_level()
    {
        Option::updateValue('store_ip', true);
        $this->resetIpCache();

        $visitor = $this->createVisitorWithPayload(['trackingLevel' => TrackingLevel::ANONYMOUS]);
        $entity  = new VisitorEntity($visitor);
        $id      = $entity->record();

        $this->assertGreaterThan(0, $id);

        $row = $this->getVisitorRow($id);
        $this->assertNotNull($row);
        $this->assertEmpty($row->ip, 'IP should not be stored when tracking level is anonymous even if store_ip is on');
        $this->assertNotSame('1.2.3.4', $row->ip, 'Real IP must not appear in DB');
    }

    public function test_exclusion_record_stored_when_record_exclusions_on()
    {
        global $wpdb;

        Option::updateValue('record_exclusions', true);
        $this->setExclusionsOptions(['record_exclusions' => true]);

        $table     = DatabaseSchema::table('exclusions');
        $countBefore = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");

        Exclusions::record([
            'exclusion_match'  => true,
            'exclusion_reason' => 'ip_match',
        ]);

        $countAfter = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");

        $this->assertGreaterThan($countBefore, $countAfter, 'Exclusion record should be stored when record_exclusions is on');
    }

    public function test_exclusion_record_not_stored_when_record_exclusions_off()
    {
        global $wpdb;

        Option::updateValue('record_exclusions', false);
        $this->setExclusionsOptions(['record_exclusions' => false]);

        $table       = DatabaseSchema::table('exclusions');
        $countBefore = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");

        Exclusions::record([
            'exclusion_match'  => true,
            'exclusion_reason' => 'ip_match',
        ]);

        $countAfter = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");

        $this->assertSame($countBefore, $countAfter, 'Exclusion record should NOT be stored when record_exclusions is off');
    }

    // ─── DB Helpers ──────────────────────────────────────────────────

    private function getVisitorRow(int $id): ?object
    {
        global $wpdb;
        $table = DatabaseSchema::table('visitors');
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE ID = %d", $id));
    }

    private function getSessionRow(int $id): ?object
    {
        global $wpdb;
        $table = DatabaseSchema::table('sessions');
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE ID = %d", $id));
    }

    private function insertSession(Visitor $visitor, int $visitorId): int
    {
        $sessionEntity = new SessionEntity($visitor);
        return $sessionEntity->create(
            $visitorId,
            ['type_id' => 0, 'os_id' => 0, 'browser_id' => 0, 'browser_version_id' => 0, 'resolution_id' => 0],
            ['country_id' => 0, 'city_id' => 0],
            ['language_id' => 0, 'timezone_id' => 0],
            0
        );
    }
}
