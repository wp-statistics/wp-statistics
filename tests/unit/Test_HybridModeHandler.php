<?php

namespace WP_Statistics\Tests\HybridModeHandler;

use WP_UnitTestCase;

/**
 * Tests for HybridModeHandler tracker template baking.
 */
class Test_HybridModeHandler extends WP_UnitTestCase
{
    public function test_tracker_template_contains_placeholders()
    {
        $templatePath = WP_STATISTICS_DIR . 'src/Service/Tracking/Methods/HybridMode/tracker.php';
        $content      = file_get_contents($templatePath);

        $this->assertStringContainsString('{{ABSPATH}}', $content);
        $this->assertStringContainsString('{{PLUGIN_DIR}}', $content);
        $this->assertStringContainsString('{{VERSION}}', $content);
    }

    public function test_tracker_returns_early_when_abspath_defined()
    {
        $templatePath = WP_STATISTICS_DIR . 'src/Service/Tracking/Methods/HybridMode/tracker.php';

        ob_start();
        include $templatePath;
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }
}
