<?php

namespace WP_Statistics\Tests\DirectEndpointManager;

use WP_UnitTestCase;

/**
 * Tests for DirectEndpointManager endpoint template baking.
 */
class Test_DirectEndpointManager extends WP_UnitTestCase
{
    public function test_endpoint_template_contains_placeholders()
    {
        $templatePath = WP_STATISTICS_DIR . 'src/Service/Tracking/DirectEndpoint/endpoint.php';
        $content      = file_get_contents($templatePath);

        $this->assertStringContainsString('{{ABSPATH}}', $content);
        $this->assertStringContainsString('{{PLUGIN_DIR}}', $content);
        $this->assertStringContainsString('{{VERSION}}', $content);
    }

    public function test_polyfills_file_exists()
    {
        $polyfillsPath = WP_STATISTICS_DIR . 'src/Service/Tracking/DirectEndpoint/polyfills.php';

        $this->assertFileExists($polyfillsPath);
    }

    public function test_endpoint_returns_early_when_abspath_defined()
    {
        $templatePath = WP_STATISTICS_DIR . 'src/Service/Tracking/DirectEndpoint/endpoint.php';

        ob_start();
        include $templatePath;
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }
}
