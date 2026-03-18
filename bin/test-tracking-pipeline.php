#!/usr/bin/env php
<?php
/**
 * WP Statistics — Comprehensive Tracking Pipeline Integration Test
 *
 * Tests all 3 tracking methods (REST, AJAX, Direct File), verifies DB storage,
 * exclusion logic, session continuation, UTM params, batch engagement, and more.
 *
 * Usage:
 *   cd /path/to/wp-content/plugins/wp-statistics
 *   wp eval-file bin/test-tracking-pipeline.php
 *
 * @package WP_Statistics
 * @since   15.0.0
 */

if (!defined('ABSPATH')) {
    define('WP_USE_THEMES', false);
    require_once __DIR__ . '/../../../../wp-load.php';
}

if (!class_exists('WP_Statistics\Bootstrap')) {
    die("\033[31mError: WP Statistics plugin not found.\033[0m\n");
}

use WP_Statistics\Components\Option;
use WP_Statistics\Components\Ip;
use WP_Statistics\Service\Database\DatabaseSchema;
use WP_Statistics\Utils\Signature;

// ═══════════════════════════════════════════════════════════════════════
// Test Runner
// ═══════════════════════════════════════════════════════════════════════

class TrackingPipelineTest
{
    private string $siteUrl;
    private int $passed = 0;
    private int $failed = 0;
    private int $skipped = 0;
    private array $failures = [];

    /** @var array Original option state for restore */
    private array $originalOptions;

    /** @var array Primary resource for hits: {id, uri_id, uri, type} */
    private array $primaryResource;

    /** @var array Secondary resource for multi-page tests: {id, uri_id, uri, type} */
    private array $secondaryResource;

    public function __construct()
    {
        $this->siteUrl         = rtrim(get_option('home'), '/');
        $this->originalOptions = Option::get();

        $this->primaryResource   = $this->resolveResource(0);
        $this->secondaryResource = $this->resolveResource(1);
    }

    /**
     * Resolve a published post and its resource_uri_id from the DB.
     *
     * Falls back to creating a synthetic resource_uri entry if needed.
     *
     * @param int $offset Which post to pick (0 = first, 1 = second, etc.)
     */
    private function resolveResource(int $offset = 0): array
    {
        global $wpdb;

        // Find a published post
        $post = $wpdb->get_row($wpdb->prepare(
            "SELECT ID, post_name, post_type FROM {$wpdb->posts}
             WHERE post_status = 'publish' AND post_type IN ('post','page')
             ORDER BY ID ASC LIMIT 1 OFFSET %d",
            $offset
        ));

        if (!$post) {
            // Fallback: use synthetic values
            $uri      = '/test-tracking-' . ($offset + 1) . '/';
            $postId   = 0;
            $postType = 'page';
        } else {
            $uri      = '/' . trim(str_replace(home_url(), '', get_permalink($post->ID)), '/') . '/';
            $postId   = (int) $post->ID;
            $postType = $post->post_type;
        }

        // Find or create resource_uri entry
        $uriTable = DatabaseSchema::table('resource_uris');
        $uriRow   = $wpdb->get_row($wpdb->prepare(
            "SELECT ID FROM `{$uriTable}` WHERE uri = %s LIMIT 1",
            $uri
        ));

        if ($uriRow) {
            $uriId = (int) $uriRow->ID;
        } else {
            $wpdb->insert($uriTable, ['uri' => $uri, 'resource_id' => $postId]);
            $uriId = (int) $wpdb->insert_id;
        }

        return [
            'id'      => $postId,
            'uri_id'  => $uriId,
            'uri'     => $uri,
            'type'    => $postType === 'post' ? 'post' : 'page',
        ];
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    /**
     * Send an HTTP POST to a tracking endpoint.
     */
    private function sendHit(string $url, array $params = [], array $headers = []): array
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER     => array_merge([
                'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
                'Accept-Language: en-US,en;q=0.9',
            ], $headers),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $body       = curl_exec($ch);
        $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError  = curl_error($ch);
        curl_close($ch);

        $decoded = json_decode($body, true);

        return [
            'http_code'  => $httpCode,
            'body'       => $body,
            'json'       => $decoded,
            'curl_error' => $curlError,
        ];
    }

    /**
     * Build hit parameters with a valid signature.
     */
    private function buildHitParams(array $overrides = []): array
    {
        $defaults = [
            'resource_type'   => $this->primaryResource['type'],
            'resource_id'     => $this->primaryResource['id'],
            'resource_uri_id' => $this->primaryResource['uri_id'],
            'resource_uri'    => base64_encode($this->primaryResource['uri']),
            'timezone'        => 'America/New_York',
            'language_code'   => 'en',
            'language_name'   => 'English',
            'screen_width'    => '1920',
            'screen_height'   => '1080',
            'referrer'        => base64_encode('https://google.com/search?q=test'),
            'user_id'         => 0,
        ];

        $params = array_merge($defaults, $overrides);

        // Generate valid signature from [resource_type, resource_id, user_id]
        $signaturePayload = [
            $params['resource_type'],
            (int) $params['resource_id'],
            (int) $params['user_id'],
        ];
        $params['signature'] = Signature::generate($signaturePayload);

        return $params;
    }

    /**
     * Get the latest row from a statistics table.
     */
    private function getLatestRow(string $tableKey): ?object
    {
        global $wpdb;
        $table = DatabaseSchema::table($tableKey);
        return $wpdb->get_row("SELECT * FROM `{$table}` ORDER BY ID DESC LIMIT 1");
    }

    /**
     * Get row count from a statistics table.
     */
    private function getRowCount(string $tableKey): int
    {
        global $wpdb;
        $table = DatabaseSchema::table($tableKey);
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM `{$table}`");
    }

    /**
     * Get a row matching conditions.
     */
    private function getRow(string $tableKey, array $conditions): ?object
    {
        global $wpdb;
        $table  = DatabaseSchema::table($tableKey);
        $wheres = [];
        $values = [];
        foreach ($conditions as $col => $val) {
            $wheres[] = "`{$col}` = %s";
            $values[] = $val;
        }
        $sql = "SELECT * FROM `{$table}` WHERE " . implode(' AND ', $wheres) . " LIMIT 1";
        return $wpdb->get_row($wpdb->prepare($sql, ...$values));
    }

    /**
     * Get all rows from a statistics table matching conditions.
     */
    private function getAllRows(string $tableKey, array $conditions = []): array
    {
        global $wpdb;
        $table = DatabaseSchema::table($tableKey);
        if (empty($conditions)) {
            return $wpdb->get_results("SELECT * FROM `{$table}`");
        }
        $wheres = [];
        $values = [];
        foreach ($conditions as $col => $val) {
            $wheres[] = "`{$col}` = %s";
            $values[] = $val;
        }
        $sql = "SELECT * FROM `{$table}` WHERE " . implode(' AND ', $wheres);
        return $wpdb->get_results($wpdb->prepare($sql, ...$values));
    }

    /**
     * Truncate all tracking tables to start fresh.
     */
    private function clearTables(): void
    {
        $tables = [
            'visitors', 'sessions', 'views', 'parameters', 'exclusions',
            'device_types', 'device_browsers', 'device_oss',
            'device_browser_versions', 'resolutions',
            'countries', 'cities', 'languages', 'timezones', 'referrers',
        ];

        foreach ($tables as $key) {
            DatabaseSchema::truncateTable($key);
        }

        // Reset singleton caches
        $this->resetExclusionsCache();
    }

    /**
     * Reset Exclusions singleton cache so each test group starts clean.
     */
    private function resetExclusionsCache(): void
    {
        $ref = new ReflectionClass(\WP_Statistics\Service\Tracking\Core\Exclusions::class);

        foreach (['exclusionResult', 'exclusionMap', 'options', 'excludedUrlPatterns'] as $prop) {
            if ($ref->hasProperty($prop)) {
                $p = $ref->getProperty($prop);
                $p->setAccessible(true);
                $p->setValue(null, null);
            }
        }

        // Also reset the singleton instance
        $parent = $ref->getParentClass();
        if ($parent && $parent->hasProperty('instances')) {
            $p = $parent->getProperty('instances');
            $p->setAccessible(true);
            $instances = $p->getValue();
            unset($instances[\WP_Statistics\Service\Tracking\Core\Exclusions::class]);
            $p->setValue(null, $instances);
        }
    }

    /**
     * Update a single option value.
     */
    private function setOption(string $key, $value): void
    {
        Option::updateValue($key, $value);
        // Reset Exclusions cache so it re-reads options
        $this->resetExclusionsCache();
    }

    /**
     * Restore original options after tests.
     */
    private function restoreOptions(): void
    {
        Option::update($this->originalOptions);
    }

    /**
     * Log a test result.
     */
    private function logResult(string $testName, bool $pass, string $details = ''): void
    {
        if ($pass) {
            $this->passed++;
            echo "\033[32m  ✓ PASS\033[0m {$testName}\n";
        } else {
            $this->failed++;
            $this->failures[] = "{$testName}: {$details}";
            echo "\033[31m  ✗ FAIL\033[0m {$testName}";
            if ($details) {
                echo " — {$details}";
            }
            echo "\n";
        }
    }

    /**
     * Log a skipped test.
     */
    private function logSkip(string $testName, string $reason): void
    {
        $this->skipped++;
        echo "\033[33m  ⊘ SKIP\033[0m {$testName} — {$reason}\n";
    }

    /**
     * Assert a condition, log result.
     */
    private function assert(bool $condition, string $testName, string $failDetails = ''): void
    {
        $this->logResult($testName, $condition, $failDetails);
    }

    /**
     * Get the tracking endpoint URL for a method.
     */
    private function getEndpointUrl(string $method): string
    {
        return match ($method) {
            'rest'        => $this->siteUrl . '/wp-json/wp-statistics/v2/hit',
            'ajax'        => $this->siteUrl . '/wp-admin/admin-ajax.php?action=wp_statistics_hit_record',
            'direct_file' => $this->siteUrl . '/wp-content/mu-plugins/wp-statistics-tracker.php',
            default       => throw new \InvalidArgumentException("Unknown method: {$method}"),
        };
    }

    /**
     * Get the batch endpoint URL for a method.
     */
    private function getBatchUrl(string $method): string
    {
        return match ($method) {
            'rest'        => $this->siteUrl . '/wp-json/wp-statistics/v2/batch',
            'ajax'        => $this->siteUrl . '/wp-admin/admin-ajax.php?action=wp_statistics_batch',
            'direct_file' => $this->siteUrl . '/wp-content/mu-plugins/wp-statistics-tracker.php',
            default       => throw new \InvalidArgumentException("Unknown method: {$method}"),
        };
    }

    /**
     * Send a hit and return the result, with debug on failure.
     */
    private function sendTestHit(string $method, array $overrides = []): array
    {
        $url    = $this->getEndpointUrl($method);
        $params = $this->buildHitParams($overrides);
        return $this->sendHit($url, $params);
    }

    // ═══════════════════════════════════════════════════════════════════
    // Test Groups
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Run all test groups.
     */
    public function run(): void
    {
        echo "\n\033[1m══════════════════════════════════════════════════════\033[0m\n";
        echo "\033[1m  WP Statistics — Tracking Pipeline Integration Tests\033[0m\n";
        echo "\033[1m══════════════════════════════════════════════════════\033[0m\n\n";
        echo "Site:     {$this->siteUrl}\n";
        echo "Transport: " . (Option::getValue('direct_file_tracking') ? 'direct_file' : 'ajax') . " (current)\n";
        echo "Resource: {$this->primaryResource['uri']} (uri_id={$this->primaryResource['uri_id']}, post_id={$this->primaryResource['id']})\n";
        echo "Resource: {$this->secondaryResource['uri']} (uri_id={$this->secondaryResource['uri_id']}, post_id={$this->secondaryResource['id']})\n\n";

        $this->testSignatureValidation();
        $this->testRequiredParams();
        $this->testPerMethodSmokeTest();
        $this->testReturningVisitor();
        $this->testNewSessionAfterTimeout();
        $this->testUpsertCorrectness();
        $this->testTimezoneDstUpdate();
        $this->testExclusions();
        $this->testExclusionRecordingToggle();
        $this->testUtmParameters();
        $this->testBatchEngagement();
        $this->testDirectFileSpecific();

        $this->restoreOptions();
        $this->printSummary();
    }

    // ─── Step 3c: Signature Validation ────────────────────────────────

    private function testSignatureValidation(): void
    {
        echo "\033[1m─── Signature Validation ───────────────────────────\033[0m\n";

        $this->setOption('direct_file_tracking', '');
        $url = $this->getEndpointUrl('rest');

        // 1. Invalid signature
        $params = $this->buildHitParams();
        $params['signature'] = 'invalid_signature_12345';
        $resp = $this->sendHit($url, $params);
        $this->assert(
            $resp['http_code'] === 403 || ($resp['json']['status'] ?? true) === false,
            'Invalid signature rejected',
            "HTTP {$resp['http_code']}, body: " . substr($resp['body'], 0, 200)
        );

        // 2. Missing signature
        $params = $this->buildHitParams();
        unset($params['signature']);
        $resp = $this->sendHit($url, $params);
        $this->assert(
            $resp['http_code'] === 403 || ($resp['json']['status'] ?? true) === false,
            'Missing signature rejected',
            "HTTP {$resp['http_code']}, body: " . substr($resp['body'], 0, 200)
        );

        // 3. Valid signature
        $this->clearTables();
        $params = $this->buildHitParams();
        $resp   = $this->sendHit($url, $params);
        $this->assert(
            ($resp['json']['status'] ?? false) === true,
            'Valid signature accepted',
            "HTTP {$resp['http_code']}, body: " . substr($resp['body'], 0, 200)
        );

        echo "\n";
    }

    // ─── Step 3d: Required Params Validation ──────────────────────────

    private function testRequiredParams(): void
    {
        echo "\033[1m─── Required Params Validation ─────────────────────\033[0m\n";

        $this->setOption('direct_file_tracking', '');
        $url = $this->getEndpointUrl('rest');

        // Missing resource_id
        $params = $this->buildHitParams();
        unset($params['resource_id']);
        // Regenerate signature without resource_id — but the signature still needs resource_type, resource_id, user_id
        // Since resource_id is missing from the POST, the payload parse should fail
        $resp = $this->sendHit($url, $params);
        $this->assert(
            ($resp['json']['status'] ?? true) === false,
            'Missing resource_id rejected',
            "HTTP {$resp['http_code']}, body: " . substr($resp['body'], 0, 200)
        );

        // Missing timezone (required string)
        $params = $this->buildHitParams();
        unset($params['timezone']);
        $resp = $this->sendHit($url, $params);
        $this->assert(
            ($resp['json']['status'] ?? true) === false,
            'Missing timezone rejected',
            "HTTP {$resp['http_code']}, body: " . substr($resp['body'], 0, 200)
        );

        echo "\n";
    }

    // ─── Step 2a: Per-Method Smoke Test ───────────────────────────────

    private function testPerMethodSmokeTest(): void
    {
        echo "\033[1m─── Per-Method Smoke Test ──────────────────────────\033[0m\n";

        $methods = ['rest', 'ajax', 'direct_file'];

        foreach ($methods as $method) {
            echo "\n  \033[36m▸ Method: {$method}\033[0m\n";

            // Configure transport: direct_file_tracking toggle controls mu-plugin endpoint
            // REST and AJAX are always registered
            $this->setOption('direct_file_tracking', $method === 'direct_file' ? '1' : '');
            $this->clearTables();

            // Send a hit
            $resp = $this->sendTestHit($method);

            if (empty($resp['json']) || ($resp['json']['status'] ?? false) !== true) {
                $this->logResult(
                    "[{$method}] Hit accepted",
                    false,
                    "HTTP {$resp['http_code']}, body: " . substr($resp['body'] ?? '', 0, 300)
                );
                continue;
            }

            $this->logResult("[{$method}] Hit accepted", true);

            // Verify visitors table
            $visitor = $this->getLatestRow('visitors');
            $this->assert(
                $visitor !== null && !empty($visitor->hash),
                "[{$method}] visitors: row created with hash",
                $visitor ? "hash={$visitor->hash}" : 'no row'
            );

            // Verify sessions table
            $session = $this->getLatestRow('sessions');
            $this->assert(
                $session !== null && (int) $session->total_views === 1,
                "[{$method}] sessions: row created with total_views=1",
                $session ? "total_views={$session->total_views}" : 'no row'
            );

            if ($session && $visitor) {
                $this->assert(
                    (int) $session->visitor_id === (int) $visitor->ID,
                    "[{$method}] sessions: visitor_id matches visitor.ID",
                    "session.visitor_id={$session->visitor_id}, visitor.ID={$visitor->ID}"
                );
            }

            // Verify views table
            $expectedUriId = $this->primaryResource['uri_id'];
            $view = $this->getLatestRow('views');
            $this->assert(
                $view !== null && (int) $view->resource_uri_id === $expectedUriId,
                "[{$method}] views: row with correct resource_uri_id",
                $view ? "resource_uri_id={$view->resource_uri_id}, expected={$expectedUriId}" : 'no row'
            );

            if ($view && $session) {
                $this->assert(
                    (int) $view->session_id === (int) $session->ID,
                    "[{$method}] views: session_id matches session.ID"
                );
            }

            // Verify session links to view
            if ($session && $view) {
                // Re-read session since it's updated after view insert
                $session = $this->getRow('sessions', ['ID' => $session->ID]);
                $this->assert(
                    (int) $session->initial_view_id === (int) $view->ID &&
                    (int) $session->last_view_id === (int) $view->ID,
                    "[{$method}] sessions: initial/last view IDs set",
                    "initial={$session->initial_view_id}, last={$session->last_view_id}, view={$view->ID}"
                );
            }

            // Verify device tables
            $deviceType = $this->getLatestRow('device_types');
            $this->assert(
                $deviceType !== null && !empty($deviceType->name),
                "[{$method}] device_types: row created",
                $deviceType ? "name={$deviceType->name}" : 'no row'
            );

            $browser = $this->getLatestRow('device_browsers');
            $this->assert(
                $browser !== null && !empty($browser->name),
                "[{$method}] device_browsers: row created",
                $browser ? "name={$browser->name}" : 'no row'
            );

            $os = $this->getLatestRow('device_oss');
            $this->assert(
                $os !== null && !empty($os->name),
                "[{$method}] device_oss: row created",
                $os ? "name={$os->name}" : 'no row'
            );

            $bv = $this->getLatestRow('device_browser_versions');
            $this->assert(
                $bv !== null && (int) $bv->browser_id > 0,
                "[{$method}] device_browser_versions: row created",
                $bv ? "browser_id={$bv->browser_id}, version={$bv->version}" : 'no row'
            );

            // Verify resolution
            $res = $this->getLatestRow('resolutions');
            $this->assert(
                $res !== null && (int) $res->width === 1920,
                "[{$method}] resolutions: row with width=1920",
                $res ? "width={$res->width}, height={$res->height}" : 'no row'
            );

            // Verify language
            $lang = $this->getLatestRow('languages');
            $this->assert(
                $lang !== null && $lang->code === 'en',
                "[{$method}] languages: row with code=en",
                $lang ? "code={$lang->code}" : 'no row'
            );

            // Verify timezone
            $tz = $this->getLatestRow('timezones');
            $this->assert(
                $tz !== null && $tz->name === 'America/New_York',
                "[{$method}] timezones: row with name=America/New_York",
                $tz ? "name={$tz->name}" : 'no row'
            );

            // Verify country (depends on GeoIP being configured)
            $country = $this->getLatestRow('countries');
            if ($country) {
                $this->assert(
                    !empty($country->code),
                    "[{$method}] countries: row created with code",
                    "code={$country->code}"
                );
            } else {
                $this->logSkip("[{$method}] countries", "No GeoIP data (localhost)");
            }

            // Verify referrer
            $referrer = $this->getLatestRow('referrers');
            if ($referrer) {
                $this->assert(
                    !empty($referrer->channel) && strpos($referrer->domain, 'google') !== false,
                    "[{$method}] referrers: row with google domain",
                    "channel={$referrer->channel}, domain={$referrer->domain}"
                );
            } else {
                // Referrer might be null if the request is from localhost (internal URL check)
                $this->logSkip("[{$method}] referrers", "Referrer may be filtered as internal");
            }

            // Verify session FK IDs populated
            if ($session) {
                $session = $this->getRow('sessions', ['ID' => $session->ID]);
                $this->assert(
                    (int) $session->device_type_id > 0,
                    "[{$method}] sessions: device_type_id populated",
                    "device_type_id={$session->device_type_id}"
                );
                $this->assert(
                    (int) $session->language_id > 0,
                    "[{$method}] sessions: language_id populated",
                    "language_id={$session->language_id}"
                );
                $this->assert(
                    (int) $session->timezone_id > 0,
                    "[{$method}] sessions: timezone_id populated",
                    "timezone_id={$session->timezone_id}"
                );
            }
        }

        echo "\n";
    }

    // ─── Step 2b: Returning Visitor (Session Continuation) ────────────

    private function testReturningVisitor(): void
    {
        echo "\033[1m─── Returning Visitor (Session Continuation) ───────\033[0m\n";

        $this->setOption('direct_file_tracking', '');
        $this->clearTables();

        // Hit #1 — new session
        $resp1 = $this->sendTestHit('rest');
        $this->assert(
            ($resp1['json']['status'] ?? false) === true,
            'Hit #1 accepted'
        );

        $session1  = $this->getLatestRow('sessions');
        $visitor1  = $this->getLatestRow('visitors');
        $view1     = $this->getLatestRow('views');

        $this->assert($session1 !== null, 'Hit #1: session created');
        $this->assert($view1 !== null, 'Hit #1: view created');

        // Hit #2 — same visitor, should continue session
        // Small sleep to get different viewed_at for duration calculation
        usleep(500_000); // 0.5s

        $resp2 = $this->sendTestHit('rest', [
            'resource_uri_id' => $this->secondaryResource['uri_id'],
            'resource_uri'    => base64_encode($this->secondaryResource['uri']),
            'resource_type'   => $this->secondaryResource['type'],
            'resource_id'     => $this->secondaryResource['id'],
        ]);
        $this->assert(
            ($resp2['json']['status'] ?? false) === true,
            'Hit #2 accepted'
        );

        $session2 = $this->getLatestRow('sessions');
        $visitor2 = $this->getLatestRow('visitors');
        $view2    = $this->getLatestRow('views');

        // Same visitor_id reused
        if ($visitor1 && $visitor2) {
            $this->assert(
                (int) $visitor1->ID === (int) $visitor2->ID,
                'Same visitor_id reused',
                "v1={$visitor1->ID}, v2={$visitor2->ID}"
            );
        }

        // Same session_id reused
        if ($session1 && $session2) {
            $this->assert(
                (int) $session1->ID === (int) $session2->ID,
                'Same session_id reused',
                "s1={$session1->ID}, s2={$session2->ID}"
            );

            // total_views incremented
            $this->assert(
                (int) $session2->total_views === 2,
                'Session total_views incremented to 2',
                "total_views={$session2->total_views}"
            );
        }

        // View chain: previous view's next_view_id should point to new view
        if ($view1 && $view2) {
            $updatedView1 = $this->getRow('views', ['ID' => $view1->ID]);
            $this->assert(
                (int) $updatedView1->next_view_id === (int) $view2->ID,
                'Previous view next_view_id links to new view',
                "view1.next_view_id={$updatedView1->next_view_id}, view2.ID={$view2->ID}"
            );

            // Duration should be calculated
            $this->assert(
                (int) $updatedView1->duration > 0,
                'Previous view duration calculated',
                "duration={$updatedView1->duration}ms"
            );
        }

        echo "\n";
    }

    // ─── Step 2c: New Session After Timeout ───────────────────────────

    private function testNewSessionAfterTimeout(): void
    {
        echo "\033[1m─── New Session After Timeout ──────────────────────\033[0m\n";

        $this->setOption('direct_file_tracking', '');
        $this->clearTables();

        // Hit #1 — create a session
        $resp1 = $this->sendTestHit('rest');
        $this->assert(($resp1['json']['status'] ?? false) === true, 'Initial hit accepted');

        $session1 = $this->getLatestRow('sessions');
        $this->assert($session1 !== null, 'Session created');

        if ($session1) {
            // Manipulate ended_at to >30 min ago
            global $wpdb;
            $table = DatabaseSchema::table('sessions');
            $wpdb->update(
                $table,
                ['ended_at' => gmdate('Y-m-d H:i:s', time() - 31 * 60)],
                ['ID' => $session1->ID]
            );

            // Hit #2 — should create new session
            $resp2 = $this->sendTestHit('rest');
            $this->assert(($resp2['json']['status'] ?? false) === true, 'Post-timeout hit accepted');

            $session2 = $this->getLatestRow('sessions');
            $this->assert(
                $session2 !== null && (int) $session2->ID !== (int) $session1->ID,
                'New session created after timeout',
                $session2 ? "s1={$session1->ID}, s2={$session2->ID}" : 'no new session'
            );

            if ($session2) {
                $this->assert(
                    (int) $session2->total_views === 1,
                    'New session has total_views=1',
                    "total_views={$session2->total_views}"
                );
            }
        }

        echo "\n";
    }

    // ─── Step 2d: Upsert Correctness ─────────────────────────────────

    private function testUpsertCorrectness(): void
    {
        echo "\033[1m─── Upsert Correctness ────────────────────────────\033[0m\n";

        $this->setOption('direct_file_tracking', '');
        $this->clearTables();

        // Send two identical hits (same device/geo/locale)
        $resp1 = $this->sendTestHit('rest');
        $this->assert(($resp1['json']['status'] ?? false) === true, 'Upsert hit #1 accepted');

        // Expire session so we get a new one (to test that lookup IDs are shared)
        $session1 = $this->getLatestRow('sessions');
        if ($session1) {
            global $wpdb;
            $table = DatabaseSchema::table('sessions');
            $wpdb->update(
                $table,
                ['ended_at' => gmdate('Y-m-d H:i:s', time() - 31 * 60)],
                ['ID' => $session1->ID]
            );
        }

        $resp2 = $this->sendTestHit('rest');
        $this->assert(($resp2['json']['status'] ?? false) === true, 'Upsert hit #2 accepted');

        // Lookup tables should have exactly 1 row each
        $this->assert(
            $this->getRowCount('device_types') === 1,
            'device_types: exactly 1 row (no duplication)',
            "count={$this->getRowCount('device_types')}"
        );
        $this->assert(
            $this->getRowCount('device_browsers') === 1,
            'device_browsers: exactly 1 row (no duplication)',
            "count={$this->getRowCount('device_browsers')}"
        );
        $this->assert(
            $this->getRowCount('device_oss') === 1,
            'device_oss: exactly 1 row (no duplication)',
            "count={$this->getRowCount('device_oss')}"
        );
        $this->assert(
            $this->getRowCount('resolutions') === 1,
            'resolutions: exactly 1 row (no duplication)',
            "count={$this->getRowCount('resolutions')}"
        );
        $this->assert(
            $this->getRowCount('languages') === 1,
            'languages: exactly 1 row (no duplication)',
            "count={$this->getRowCount('languages')}"
        );
        $this->assert(
            $this->getRowCount('timezones') === 1,
            'timezones: exactly 1 row (no duplication)',
            "count={$this->getRowCount('timezones')}"
        );

        // Both sessions should reference the same device_type_id
        $sessions = $this->getAllRows('sessions');
        if (count($sessions) >= 2) {
            $this->assert(
                (int) $sessions[0]->device_type_id === (int) $sessions[1]->device_type_id,
                'Both sessions share same device_type_id',
                "s1={$sessions[0]->device_type_id}, s2={$sessions[1]->device_type_id}"
            );
            $this->assert(
                (int) $sessions[0]->language_id === (int) $sessions[1]->language_id,
                'Both sessions share same language_id',
                "s1={$sessions[0]->language_id}, s2={$sessions[1]->language_id}"
            );
        }

        echo "\n";
    }

    // ─── Step 2e: Timezone DST Update ─────────────────────────────────

    private function testTimezoneDstUpdate(): void
    {
        echo "\033[1m─── Timezone DST Update ────────────────────────────\033[0m\n";

        $this->setOption('direct_file_tracking', '');
        $this->clearTables();

        // Send a hit with America/New_York timezone
        $resp = $this->sendTestHit('rest');
        $this->assert(($resp['json']['status'] ?? false) === true, 'Timezone hit accepted');

        $tz = $this->getRow('timezones', ['name' => 'America/New_York']);
        $this->assert($tz !== null, 'Timezone row created');

        if ($tz) {
            $originalOffset = $tz->offset;
            $originalDst    = $tz->is_dst;

            // Manipulate DB to have stale offset
            global $wpdb;
            $table = DatabaseSchema::table('timezones');
            $wpdb->update(
                $table,
                ['offset' => '+99:00', 'is_dst' => 99],
                ['ID' => $tz->ID]
            );

            // Expire session to force new session
            $session = $this->getLatestRow('sessions');
            if ($session) {
                $sTable = DatabaseSchema::table('sessions');
                $wpdb->update($sTable, ['ended_at' => gmdate('Y-m-d H:i:s', time() - 31 * 60)], ['ID' => $session->ID]);
            }

            // Send another hit
            $resp2 = $this->sendTestHit('rest');
            $this->assert(($resp2['json']['status'] ?? false) === true, 'Timezone update hit accepted');

            // Verify offset was updated back to correct value
            $tzUpdated = $this->getRow('timezones', ['name' => 'America/New_York']);
            $this->assert(
                $tzUpdated !== null && $tzUpdated->offset !== '+99:00',
                'Timezone offset updated via upsert',
                $tzUpdated ? "offset={$tzUpdated->offset} (was +99:00)" : 'no row'
            );

            // Verify still exactly 1 row (no duplication)
            $this->assert(
                $this->getRowCount('timezones') === 1,
                'Timezone table: still 1 row after upsert',
                "count={$this->getRowCount('timezones')}"
            );
        }

        echo "\n";
    }

    // ─── Step 3a: Exclusions ──────────────────────────────────────────

    private function testExclusions(): void
    {
        echo "\033[1m─── Exclusion Tests ───────────────────────────────\033[0m\n";

        $this->setOption('direct_file_tracking', '');

        // Enable exclusion recording for these tests
        $this->setOption('record_exclusions', '1');

        $exclusionTests = [
            [
                'name'       => 'Login page exclusion',
                'option_key' => 'exclude_loginpage',
                'option_val' => '1',
                'overrides'  => ['resource_type' => 'loginpage'],
                'reset_key'  => 'exclude_loginpage',
            ],
            [
                'name'       => 'Feed exclusion',
                'option_key' => 'exclude_feeds',
                'option_val' => '1',
                'overrides'  => ['resource_type' => 'feed'],
                'reset_key'  => 'exclude_feeds',
            ],
            [
                'name'       => '404 exclusion',
                'option_key' => 'exclude_404s',
                'option_val' => '1',
                'overrides'  => ['resource_type' => '404'],
                'reset_key'  => 'exclude_404s',
            ],
            [
                'name'       => 'User role exclusion (administrator)',
                'option_key' => 'exclude_administrator',
                'option_val' => '1',
                'overrides'  => ['user_id' => 1],
                'reset_key'  => 'exclude_administrator',
            ],
        ];

        foreach ($exclusionTests as $test) {
            $this->clearTables();
            $this->setOption($test['option_key'], $test['option_val']);

            $resp = $this->sendTestHit('rest', $test['overrides']);

            // Hit should be rejected (status false)
            $this->assert(
                ($resp['json']['status'] ?? true) === false,
                "{$test['name']}: hit rejected",
                "HTTP {$resp['http_code']}, body: " . substr($resp['body'], 0, 200)
            );

            // No session/view created
            $sessionCount = $this->getRowCount('sessions');
            $this->assert(
                $sessionCount === 0,
                "{$test['name']}: no session created",
                "sessions={$sessionCount}"
            );

            // Exclusion recorded
            $exclusion = $this->getLatestRow('exclusions');
            $this->assert(
                $exclusion !== null && (int) $exclusion->count >= 1,
                "{$test['name']}: exclusion recorded",
                $exclusion ? "reason={$exclusion->reason}, count={$exclusion->count}" : 'no row'
            );

            // Reset option
            $this->setOption($test['reset_key'], '');
        }

        // IP exclusion
        $this->clearTables();
        $currentIp = Ip::getCurrent();
        $this->setOption('exclude_ip', $currentIp);

        $resp = $this->sendTestHit('rest');
        $this->assert(
            ($resp['json']['status'] ?? true) === false,
            'IP exclusion: hit rejected',
            "IP={$currentIp}, HTTP {$resp['http_code']}"
        );
        $this->setOption('exclude_ip', '');

        // Excluded URL pattern
        $this->clearTables();
        $urlPattern = trim($this->primaryResource['uri'], '/');
        // Use a wildcard that matches the primary resource URI
        $this->setOption('excluded_urls', substr($urlPattern, 0, -1) . '*');

        $resp = $this->sendTestHit('rest');
        $this->assert(
            ($resp['json']['status'] ?? true) === false,
            'Excluded URL pattern: hit rejected',
            "HTTP {$resp['http_code']}, body: " . substr($resp['body'], 0, 200)
        );
        $this->setOption('excluded_urls', '');

        echo "\n";
    }

    // ─── Step 3b: Exclusion Recording Toggle ──────────────────────────

    private function testExclusionRecordingToggle(): void
    {
        echo "\033[1m─── Exclusion Recording Toggle ─────────────────────\033[0m\n";

        $this->setOption('direct_file_tracking', '');
        $this->setOption('exclude_feeds', '1');

        // Test with recording OFF
        $this->clearTables();
        $this->setOption('record_exclusions', '');

        $resp = $this->sendTestHit('rest', ['resource_type' => 'feed']);
        $exclusionCount = $this->getRowCount('exclusions');
        $this->assert(
            $exclusionCount === 0,
            'record_exclusions=0: exclusions table NOT updated',
            "count={$exclusionCount}"
        );

        // Test with recording ON
        $this->clearTables();
        $this->setOption('record_exclusions', '1');

        $resp = $this->sendTestHit('rest', ['resource_type' => 'feed']);
        $exclusionCount = $this->getRowCount('exclusions');
        $this->assert(
            $exclusionCount === 1,
            'record_exclusions=1: exclusions table updated',
            "count={$exclusionCount}"
        );

        $exclusion = $this->getLatestRow('exclusions');
        if ($exclusion) {
            $this->assert(
                $exclusion->reason === 'feed',
                'Exclusion reason is "feed"',
                "reason={$exclusion->reason}"
            );
        }

        $this->setOption('exclude_feeds', '');
        echo "\n";
    }

    // ─── Step 4: UTM Parameter Recording ──────────────────────────────

    private function testUtmParameters(): void
    {
        echo "\033[1m─── UTM Parameter Recording ────────────────────────\033[0m\n";

        $this->setOption('direct_file_tracking', '');
        $this->clearTables();

        // Send hit with UTM params in the resource_uri
        $uriWithUtm = rtrim($this->primaryResource['uri'], '/') . '/?utm_source=google&utm_medium=cpc&utm_campaign=spring';
        $resp = $this->sendTestHit('rest', [
            'resource_uri' => base64_encode($uriWithUtm),
        ]);
        $this->assert(
            ($resp['json']['status'] ?? false) === true,
            'UTM hit accepted'
        );

        $session = $this->getLatestRow('sessions');
        $this->assert($session !== null, 'Session created for UTM hit');

        if ($session) {
            // Check parameters table
            $params = $this->getAllRows('parameters', ['session_id' => $session->ID]);
            $paramMap = [];
            foreach ($params as $p) {
                $paramMap[$p->parameter] = $p->value;
            }

            $this->assert(
                isset($paramMap['utm_source']) && $paramMap['utm_source'] === 'google',
                'UTM: utm_source=google recorded',
                isset($paramMap['utm_source']) ? "value={$paramMap['utm_source']}" : 'not found'
            );
            $this->assert(
                isset($paramMap['utm_medium']) && $paramMap['utm_medium'] === 'cpc',
                'UTM: utm_medium=cpc recorded',
                isset($paramMap['utm_medium']) ? "value={$paramMap['utm_medium']}" : 'not found'
            );
            $this->assert(
                isset($paramMap['utm_campaign']) && $paramMap['utm_campaign'] === 'spring',
                'UTM: utm_campaign=spring recorded',
                isset($paramMap['utm_campaign']) ? "value={$paramMap['utm_campaign']}" : 'not found'
            );

            // Hit #2 in same session — should NOT create new parameter rows (first-touch only)
            $paramCountBefore = $this->getRowCount('parameters');
            $resp2 = $this->sendTestHit('rest', [
                'resource_uri'    => base64_encode($this->secondaryResource['uri'] . '?utm_source=bing&utm_medium=organic'),
                'resource_uri_id' => $this->secondaryResource['uri_id'],
                'resource_type'   => $this->secondaryResource['type'],
                'resource_id'     => $this->secondaryResource['id'],
            ]);
            $paramCountAfter = $this->getRowCount('parameters');

            $this->assert(
                $paramCountAfter === $paramCountBefore,
                'Session continuation: no new UTM params recorded',
                "before={$paramCountBefore}, after={$paramCountAfter}"
            );
        }

        echo "\n";
    }

    // ─── Step 5: Batch Engagement ─────────────────────────────────────

    private function testBatchEngagement(): void
    {
        echo "\033[1m─── Batch Engagement ──────────────────────────────\033[0m\n";

        $methods = ['rest', 'ajax'];

        foreach ($methods as $method) {
            echo "\n  \033[36m▸ Method: {$method}\033[0m\n";

            // REST and AJAX are always registered — no toggle needed
            $this->setOption('direct_file_tracking', '');
            $this->clearTables();

            // First, create a session via a hit
            $resp = $this->sendTestHit($method);
            $this->assert(
                ($resp['json']['status'] ?? false) === true,
                "[{$method}] Batch: initial hit accepted"
            );

            $session = $this->getLatestRow('sessions');
            if (!$session) {
                $this->logResult("[{$method}] Batch: session found", false, 'no session');
                continue;
            }

            // Send batch with engagement_time
            $batchUrl  = $this->getBatchUrl($method);
            $batchData = json_encode(['engagement_time' => 5000]);

            $batchResp = $this->sendHit($batchUrl, ['batch_data' => $batchData]);

            $this->assert(
                ($batchResp['json']['status'] ?? false) === true,
                "[{$method}] Batch: request accepted",
                "HTTP {$batchResp['http_code']}, body: " . substr($batchResp['body'], 0, 200)
            );

            // Verify session updated
            $updatedSession = $this->getRow('sessions', ['ID' => $session->ID]);
            if ($updatedSession) {
                $this->assert(
                    (int) $updatedSession->duration === 5,
                    "[{$method}] Batch: session duration updated to 5s",
                    "duration={$updatedSession->duration}"
                );

                // ended_at should be updated
                $this->assert(
                    !empty($updatedSession->ended_at),
                    "[{$method}] Batch: session ended_at updated",
                    "ended_at={$updatedSession->ended_at}"
                );
            } else {
                $this->logResult("[{$method}] Batch: session re-read", false, 'could not re-read');
            }
        }

        echo "\n";
    }

    // ─── Step 6: Direct File Specific ─────────────────────────────────

    private function testDirectFileSpecific(): void
    {
        echo "\033[1m─── Direct File Specific Tests ─────────────────────\033[0m\n";

        $muPluginPath = WP_CONTENT_DIR . '/mu-plugins/wp-statistics-tracker.php';
        $this->assert(
            file_exists($muPluginPath),
            'Mu-plugin file exists',
            $muPluginPath
        );

        $dfUrl = $this->getEndpointUrl('direct_file');

        // GET request should be rejected (POST only)
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $dfUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => [
                'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            ],
        ]);
        $getBody    = curl_exec($ch);
        $getCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $getJson = json_decode($getBody, true);
        $this->assert(
            $getCode === 405 || ($getJson['status'] ?? true) === false,
            'Direct File: GET request rejected',
            "HTTP {$getCode}, body: " . substr($getBody, 0, 200)
        );

        // POST with valid payload
        $this->setOption('direct_file_tracking', '1');
        $this->clearTables();

        $resp = $this->sendTestHit('direct_file');
        $this->assert(
            ($resp['json']['status'] ?? false) === true,
            'Direct File: POST with valid payload succeeds',
            "HTTP {$resp['http_code']}, body: " . substr($resp['body'], 0, 200)
        );

        // Verify response is valid JSON (not HTML error)
        $this->assert(
            $resp['json'] !== null,
            'Direct File: response is valid JSON',
            'body: ' . substr($resp['body'], 0, 100)
        );

        // Verify data recorded
        $session = $this->getLatestRow('sessions');
        $this->assert(
            $session !== null && (int) $session->total_views === 1,
            'Direct File: session + view recorded',
            $session ? "total_views={$session->total_views}" : 'no session'
        );

        echo "\n";
    }

    // ─── Summary ─────────────────────────────────────────────────────

    private function printSummary(): void
    {
        $total = $this->passed + $this->failed + $this->skipped;

        echo "\n\033[1m══════════════════════════════════════════════════════\033[0m\n";
        echo "\033[1m  Summary\033[0m\n";
        echo "\033[1m══════════════════════════════════════════════════════\033[0m\n\n";
        echo "  Total:   {$total}\n";
        echo "  \033[32mPassed:  {$this->passed}\033[0m\n";
        echo "  \033[31mFailed:  {$this->failed}\033[0m\n";
        echo "  \033[33mSkipped: {$this->skipped}\033[0m\n\n";

        if (!empty($this->failures)) {
            echo "\033[31m  Failures:\033[0m\n";
            foreach ($this->failures as $i => $f) {
                echo "    " . ($i + 1) . ". {$f}\n";
            }
            echo "\n";
        }

        if ($this->failed === 0) {
            echo "\033[32m  All tests passed!\033[0m\n\n";
        }
    }
}

// ═══════════════════════════════════════════════════════════════════════
// Run
// ═══════════════════════════════════════════════════════════════════════

(new TrackingPipelineTest())->run();
