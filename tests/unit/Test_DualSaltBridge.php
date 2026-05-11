<?php

namespace WP_Statistics\Tests;

use ReflectionClass;
use WP_UnitTestCase;
use WP_Statistics\Components\Ip;
use WP_Statistics\Components\Option;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Entity\Visitor as VisitorEntity;
use WP_Statistics\Models\SessionModel;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Service\Consent\TrackingLevel;
use WP_Statistics\Service\Database\DatabaseSchema;
use WP_Statistics\Service\Tracking\Core\Payload;
use WP_Statistics\Service\Tracking\Core\Visitor;

/**
 * Tests the dual-salt midnight bridge — keeping yesterday's salt as
 * `previous_salt` so visitor identity survives the daily salt rotation.
 *
 * Tests that exercise Ip::getSalt() rotation logic must run in isolated
 * processes — the method-local static `$cached` salt persists across calls
 * within a process and would contaminate sibling tests.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * @since 15.1.0
 */
class Test_DualSaltBridge extends WP_UnitTestCase
{
    private $originalServer = [];

    public function setUp(): void
    {
        parent::setUp();

        update_option('wp_statistics', []);
        delete_option('wp_statistics_previous_salt');

        $this->originalServer       = $_SERVER;
        $_SERVER['REMOTE_ADDR']     = '1.2.3.4';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 Test';

        $this->resetIpStaticCaches();
    }

    public function tearDown(): void
    {
        $_SERVER = $this->originalServer;
        parent::tearDown();
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    /**
     * Reset Ip class-level static caches. The method-local static `$cached`
     * inside getSalt() / getPreviousSalt() can only be reset by running tests
     * in separate processes (see @runTestsInSeparateProcesses on this class).
     */
    private function resetIpStaticCaches(): void
    {
        $rc   = new ReflectionClass(Ip::class);
        $prop = $rc->getProperty('cachedIpMethod');
        // setAccessible() became no-op in PHP 8.1 and emits a deprecation
        // warning in 8.5 — only call it on older runtimes where it's still
        // required for non-public properties.
        if (PHP_VERSION_ID < 80100) {
            $prop->setAccessible(true);
        }
        $prop->setValue(null, null);
    }

    private function createVisitorWithPayload(): Visitor
    {
        $payload = (new ReflectionClass(Payload::class))->newInstanceWithoutConstructor();
        $rc      = new ReflectionClass(Payload::class);

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

        foreach ($defaults as $prop => $value) {
            if ($rc->hasProperty($prop)) {
                $rp = $rc->getProperty($prop);
                if (PHP_VERSION_ID < 80100) {
                    $rp->setAccessible(true);
                }
                $rp->setValue($payload, $value);
            }
        }

        return new Visitor($payload);
    }

    // ═══════════════════════════════════════════════════════════════════
    // Fresh state — no previous salt, no rotation has happened.
    // ═══════════════════════════════════════════════════════════════════

    public function test_get_previous_salt_returns_null_when_option_absent()
    {
        $this->assertNull(Ip::getPreviousSalt());
    }

    public function test_get_previous_salt_returns_null_when_option_malformed()
    {
        Option::updateValue('previous_salt', 'not-an-array');
        $this->assertNull(Ip::getPreviousSalt());

        Option::updateValue('previous_salt', ['date' => '2026-01-01']); // missing salt
        $this->assertNull(Ip::getPreviousSalt());
    }

    public function test_hash_with_previous_salt_returns_null_when_no_previous_salt()
    {
        $this->assertNull(Ip::hashWithPreviousSalt());
        $this->assertNull(Ip::hashWithPreviousSalt('1.2.3.4'));
    }

    // ═══════════════════════════════════════════════════════════════════
    // Rotation — getSalt() detects period mismatch and preserves prior salt.
    // ═══════════════════════════════════════════════════════════════════

    public function test_rotation_copies_old_salt_to_previous_salt()
    {
        $oldSalt = str_repeat('a', 64);
        Option::updateValue('daily_salt', ['date' => '2020-01-01', 'salt' => $oldSalt]); // stale date

        Ip::getSalt(); // triggers rotation

        $previous = Option::getValue('previous_salt', []);
        $this->assertSame('2020-01-01', $previous['date']);
        $this->assertSame($oldSalt, $previous['salt']);

        $current = Option::getValue('daily_salt', []);
        $this->assertNotSame($oldSalt, $current['salt']);
        $this->assertNotSame('2020-01-01', $current['date']);
    }

    public function test_rotation_does_not_preserve_when_daily_salt_is_empty()
    {
        // Fresh install: daily_salt option absent.
        Ip::getSalt();

        $previous = Option::getValue('previous_salt', []);
        $this->assertEmpty($previous, 'previous_salt should remain unset on first init');

        $current = Option::getValue('daily_salt', []);
        $this->assertNotEmpty($current['salt']);
        $this->assertNotEmpty($current['date']);
    }

    public function test_hash_with_previous_salt_differs_from_current_after_rotation()
    {
        $oldSalt = str_repeat('b', 64);
        Option::updateValue('daily_salt', ['date' => '2020-01-01', 'salt' => $oldSalt]);

        Ip::getSalt(); // rotates

        $current  = Ip::hash('1.2.3.4');
        $previous = Ip::hashWithPreviousSalt('1.2.3.4');

        $this->assertNotNull($previous);
        $this->assertNotSame($current, $previous);
    }

    public function test_hash_with_previous_salt_equals_pre_rotation_hash()
    {
        // Pre-populate previous_salt as if rotation already happened.
        $oldSalt = str_repeat('c', 64);
        Option::updateValue('previous_salt', ['date' => '2020-01-01', 'salt' => $oldSalt]);

        // Compute the hash as Ip::hash would have, using the old salt directly.
        $expected = substr(
            hash('sha256', $oldSalt . wp_privacy_anonymize_ip('1.2.3.4') . 'Mozilla/5.0 Test'),
            0,
            20
        );
        $expected = apply_filters('wp_statistics_hash_ip', $expected);

        $this->assertSame($expected, Ip::hashWithPreviousSalt('1.2.3.4'));
    }

    // ═══════════════════════════════════════════════════════════════════
    // Rotation interval — disabled means no rotation past first init.
    // ═══════════════════════════════════════════════════════════════════

    public function test_disabled_interval_uses_permanent_period()
    {
        Option::updateValue('hash_rotation_interval', 'disabled');
        Ip::getSalt();

        $current = Option::getValue('daily_salt', []);
        $this->assertSame('permanent', $current['date']);

        $previous = Option::getValue('previous_salt', []);
        $this->assertEmpty($previous, 'No prior salt to preserve on first init');
    }

    // ═══════════════════════════════════════════════════════════════════
    // Visitor bridge — Entity\Visitor::record() falls back to previous hash.
    // ═══════════════════════════════════════════════════════════════════

    public function test_visitor_record_reuses_row_via_previous_hash()
    {
        Option::updateValue('store_ip', false);

        // Simulate a post-rotation state by pre-populating previous_salt with a
        // known salt, then inserting a visitor row keyed by what *that* salt
        // would produce for the test IP/UA.
        $oldSalt = str_repeat('d', 64);
        Option::updateValue('previous_salt', ['date' => '2020-01-01', 'salt' => $oldSalt]);

        $previousHash = Ip::hashWithPreviousSalt(); // current request's IP+UA, old salt
        $this->assertNotNull($previousHash);

        $visitorId = RecordFactory::visitor()->insert([
            'hash'       => $previousHash,
            'ip'         => null,
            'created_at' => DateTime::getUtc(),
        ]);
        $this->assertGreaterThan(0, $visitorId);

        $visitor = $this->createVisitorWithPayload();
        $entity  = new VisitorEntity($visitor);
        $reused  = $entity->record();

        $this->assertSame($visitorId, $reused, 'record() must reuse the existing row via the previous-hash lookup');

        // No additional row should have been inserted.
        global $wpdb;
        $table = DatabaseSchema::table('visitors');
        $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        $this->assertSame(1, $count, 'No new visitor row should be created when previous-hash matches');
    }

    public function test_visitor_record_inserts_new_row_when_no_previous_hash_match()
    {
        // No previous_salt set → fallback path is a no-op.
        Option::updateValue('store_ip', false);

        $visitor = $this->createVisitorWithPayload();
        $entity  = new VisitorEntity($visitor);
        $id      = $entity->record();

        $this->assertGreaterThan(0, $id);

        global $wpdb;
        $table = DatabaseSchema::table('visitors');
        $row   = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE ID = %d", $id));
        $this->assertSame(Ip::hash(), $row->hash, 'New row must use the current hash');
    }

    // ═══════════════════════════════════════════════════════════════════
    // Session bridge — getActiveSessionByHash accepts an optional previous hash.
    // ═══════════════════════════════════════════════════════════════════

    public function test_session_lookup_finds_session_via_previous_hash()
    {
        global $wpdb;

        $previousHash = 'prev_hash_12345_abcd';
        $currentHash  = 'curr_hash_12345_efgh';

        $visitorId = RecordFactory::visitor()->insert([
            'hash'       => $previousHash,
            'ip'         => null,
            'created_at' => DateTime::getUtc(),
        ]);
        $this->assertGreaterThan(0, $visitorId);

        $sessionsTable = DatabaseSchema::table('sessions');
        $now           = DateTime::getUtc();
        $wpdb->insert($sessionsTable, [
            'visitor_id' => $visitorId,
            'started_at' => $now,
            'ended_at'   => $now,
            'duration'   => 0,
        ]);
        $sessionId = (int) $wpdb->insert_id;
        $this->assertGreaterThan(0, $sessionId);

        $session = (new SessionModel())->getActiveSessionByHash($currentHash, $previousHash);

        $this->assertNotNull($session, 'Should find session via the previous-hash side of the IN clause');
        $this->assertSame($sessionId, (int) $session->ID);
    }

    public function test_session_lookup_returns_null_when_neither_hash_matches()
    {
        $session = (new SessionModel())->getActiveSessionByHash('no_match_xxx', 'no_match_yyy');
        $this->assertNull($session);
    }

    public function test_session_lookup_works_with_only_current_hash()
    {
        global $wpdb;

        $hash      = 'only_current_hash_12';
        $visitorId = RecordFactory::visitor()->insert([
            'hash'       => $hash,
            'ip'         => null,
            'created_at' => DateTime::getUtc(),
        ]);

        $sessionsTable = DatabaseSchema::table('sessions');
        $now           = DateTime::getUtc();
        $wpdb->insert($sessionsTable, [
            'visitor_id' => $visitorId,
            'started_at' => $now,
            'ended_at'   => $now,
            'duration'   => 0,
        ]);
        $sessionId = (int) $wpdb->insert_id;

        // Pass null as previous hash — array_filter drops it, single-hash IN clause.
        $session = (new SessionModel())->getActiveSessionByHash($hash, null);

        $this->assertNotNull($session);
        $this->assertSame($sessionId, (int) $session->ID);
    }

    public function test_session_lookup_returns_null_when_both_hashes_empty()
    {
        $this->assertNull((new SessionModel())->getActiveSessionByHash('', null));
        $this->assertNull((new SessionModel())->getActiveSessionByHash(null, null));
    }

    // ═══════════════════════════════════════════════════════════════════
    // Request-scoped static cache — same value across calls in one request.
    // ═══════════════════════════════════════════════════════════════════

    public function test_get_salt_returns_same_value_within_request()
    {
        $first = Ip::getSalt();

        // Mutate the underlying option mid-request — the static cache should
        // shield in-flight code from observing a different salt.
        Option::updateValue('daily_salt', ['date' => '2099-12-31', 'salt' => str_repeat('z', 64)]);

        $second = Ip::getSalt();

        $this->assertSame($first, $second, 'Static cache must keep one request consistent across rotation mid-request');
    }

    public function test_hash_returns_consistent_value_within_request()
    {
        $first  = Ip::hash('1.2.3.4');
        $second = Ip::hash('1.2.3.4');

        $this->assertSame($first, $second);
    }
}
