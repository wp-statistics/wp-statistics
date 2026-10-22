<?php

namespace WP_Statistics\Tests\Core\Operations;

use WP_Statistics\Core\Operations\Updater;
use WP_UnitTestCase;

/**
 * Covers release cleanup that must run even when the stored version already
 * matches the current prerelease build.
 */
class Test_TrackerDebuggerUpgradeCleanup extends WP_UnitTestCase
{
    public function test_same_version_installation_clears_legacy_tracker_errors()
    {
        update_option('wp_statistics_plugin_version', WP_STATISTICS_VERSION);
        update_option('wp_statistics_tracker_js_errors', [['message' => 'legacy error']]);

        $updater = new Updater();

        try {
            $updater->execute();
        } finally {
            remove_action('init', [$updater, 'execute']);
        }

        $this->assertFalse(get_option('wp_statistics_tracker_js_errors', false));
    }
}
