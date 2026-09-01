<?php

class Test_ExternalPageTrackingDocs extends WP_UnitTestCase
{
    private $guide;

    public function setUp(): void
    {
        parent::setUp();

        $path = dirname(__DIR__, 2) . '/docs/same-domain-external-page-tracking.md';

        $this->assertFileExists($path);
        $this->guide = file_get_contents($path);
    }

    public function test_guide_uses_the_existing_hit_route()
    {
        $this->assertStringContainsString('POST /wp-json/wp-statistics/v2/hit', $this->guide);
        $this->assertStringNotContainsString('external-page', $this->guide);
        $this->assertStringContainsString('Enable **Cache Compatibility**', $this->guide);
    }

    public function test_guide_prefers_server_generated_signatures()
    {
        $this->assertStringContainsString('Signature::generate([$sourceType, $sourceId])', $this->guide);
        $this->assertStringContainsString("rest_url('wp-statistics/v2/hit')", $this->guide);
        $this->assertStringContainsString('Never copy the WordPress salt into browser JavaScript', $this->guide);
    }

    public function test_unsigned_example_is_scoped_to_trusted_requests()
    {
        $this->assertStringContainsString("'wp_statistics_request_signature_enabled'", $this->guide);
        $this->assertStringContainsString("'POST' !== \$method", $this->guide);
        $this->assertStringContainsString("'/wp-statistics/v2/hit' !== untrailingslashit(\$route)", $this->guide);
        $this->assertStringContainsString("\$_SERVER['HTTP_ORIGIN']", $this->guide);
        $this->assertStringContainsString('$allowedPaths', $this->guide);
        $this->assertStringContainsString('can be spoofed by a direct HTTP client', $this->guide);
        $this->assertStringContainsString('open hit-injection endpoint', $this->guide);
    }

    public function test_guide_routes_interactions_to_custom_events()
    {
        $this->assertStringContainsString(
            'https://wp-statistics.com/resources/custom-event-tracking/',
            $this->guide
        );
    }
}
