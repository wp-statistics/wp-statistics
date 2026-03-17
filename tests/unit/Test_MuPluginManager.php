<?php

namespace WP_Statistics\Tests\MuPluginManager;

use WP_UnitTestCase;

/**
 * Tests for MuPluginManager endpoint template baking.
 */
class Test_MuPluginManager extends WP_UnitTestCase
{
    public function test_endpoint_template_contains_placeholders()
    {
        $templatePath = WP_STATISTICS_DIR . 'src/Service/Tracking/MuPlugin/endpoint.php';
        $content      = file_get_contents($templatePath);

        $this->assertStringContainsString('{{ABSPATH}}', $content);
        $this->assertStringContainsString('{{PLUGIN_DIR}}', $content);
        $this->assertStringContainsString('{{VERSION}}', $content);
    }

    public function test_polyfills_file_exists()
    {
        $polyfillsPath = WP_STATISTICS_DIR . 'src/Service/Tracking/MuPlugin/polyfills.php';

        $this->assertFileExists($polyfillsPath);
    }

    public function test_endpoint_returns_early_when_abspath_defined()
    {
        $templatePath = WP_STATISTICS_DIR . 'src/Service/Tracking/MuPlugin/endpoint.php';

        ob_start();
        include $templatePath;
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }
}
