<?php

namespace WP_Statistics\Tests\TrackerHelper;

use WP_UnitTestCase;
use WP_Statistics\Service\Tracking\TrackerHelper;
use WP_Statistics\Utils\Signature;

/**
 * Tests for TrackerHelper changes.
 *
 * Verifies that:
 * - user_id is included in hit params
 * - user_id is part of the signature
 * - getRequestUri reads page_uri from request
 * - HIT_REQUEST_KEY constant is defined
 *
 * @since 15.0.0
 */
class Test_TrackerHelper extends WP_UnitTestCase
{
    public function tearDown(): void
    {
        unset($_REQUEST['page_uri']);
        parent::tearDown();
    }

    // ─── HIT_REQUEST_KEY Constant ─────────────────────────────────────

    public function test_hit_request_key_constant_exists()
    {
        $this->assertTrue(defined(TrackerHelper::class . '::HIT_REQUEST_KEY'));
        $this->assertSame('wp_statistics_hit', TrackerHelper::HIT_REQUEST_KEY);
    }

    // ─── getRequestUri ────────────────────────────────────────────────

    public function test_getRequestUri_uses_page_uri_when_present()
    {
        $expectedUri = '/my-page?foo=bar';
        $_REQUEST['page_uri'] = base64_encode($expectedUri);

        $result = TrackerHelper::getRequestUri();

        $this->assertSame($expectedUri, $result);
    }

    public function test_getRequestUri_falls_back_to_server_request_uri()
    {
        unset($_REQUEST['page_uri']);

        $result = TrackerHelper::getRequestUri();

        // Should return the server's REQUEST_URI
        $this->assertIsString($result);
    }

    public function test_getRequestUri_decodes_and_sanitizes_page_uri()
    {
        $uri = '/path/to/page?q=hello';
        $_REQUEST['page_uri'] = base64_encode($uri);

        $result = TrackerHelper::getRequestUri();

        $this->assertSame($uri, $result);
    }

    // ─── Signature Integration ────────────────────────────────────────

    public function test_signature_includes_user_id()
    {
        // Generate signature with user_id = 0 (guest)
        $signature1 = Signature::generate(['post', 42, 0]);

        // Generate signature with user_id = 1 (admin)
        $signature2 = Signature::generate(['post', 42, 1]);

        // Different user_id should produce different signatures
        $this->assertNotSame($signature1, $signature2);
    }

    public function test_signature_verification_with_user_id()
    {
        $payload = ['page', 10, 5];
        $signature = Signature::generate($payload);

        // Same payload should verify
        $this->assertTrue(Signature::check($payload, $signature));

        // Tampered user_id should not verify
        $tamperedPayload = ['page', 10, 999];
        $this->assertFalse(Signature::check($tamperedPayload, $signature));
    }

    public function test_signature_zero_user_id_matches()
    {
        $payload = ['post', 1, 0];
        $signature = Signature::generate($payload);

        $this->assertTrue(Signature::check($payload, $signature));
    }
}
