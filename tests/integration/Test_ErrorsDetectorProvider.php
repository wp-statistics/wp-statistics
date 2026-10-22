<?php

namespace WP_Statistics\Tests\Service\Debugger;

use WP_Statistics\Option;
use WP_Statistics\Service\Debugger\Provider\ErrorsDetectorProvider;
use WP_UnitTestCase;

/**
 * The tracker debugger lists PHP errors raised while recording a visit (#1170).
 *
 * error_get_last() returns the last error of the whole request, so a deprecation from a
 * theme or another plugin was reported as "PHP Errors Detected in tracker.js".
 */
class Test_ErrorsDetectorProvider extends WP_UnitTestCase
{
    /** @var string[] */
    private $files = [];

    public function setUp(): void
    {
        parent::setUp();
        Option::updateGroupOptions(ErrorsDetectorProvider::ERROR_LOG_OPTION, []);
        error_clear_last();
    }

    public function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
            @rmdir(dirname($file));
        }
        error_clear_last();
        parent::tearDown();
    }

    /**
     * Raise a deprecation from a file at the given path, the way a theme or plugin would.
     */
    private function raiseFrom($path, $message)
    {
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        file_put_contents($path, '<?php @trigger_error(' . var_export($message, true) . ', E_USER_DEPRECATED);');
        $this->files[] = $path;

        include $path;
    }

    private function loggedMessages()
    {
        return array_column((new ErrorsDetectorProvider())->getErrors(), 'message');
    }

    public function test_an_error_from_another_plugin_is_not_blamed_on_the_tracker()
    {
        $this->raiseFrom(WP_PLUGIN_DIR . '/kirki-test-fixture/field.php', 'Creation of dynamic property Kirki\Field\Repeater::$row_label is deprecated');

        (new ErrorsDetectorProvider())->errorListener();

        $this->assertSame([], $this->loggedMessages());
    }

    public function test_an_error_from_another_plugin_is_not_cleared()
    {
        $message = 'An error owned by another plugin';
        $this->raiseFrom(WP_PLUGIN_DIR . '/kirki-test-fixture/field.php', $message);

        (new ErrorsDetectorProvider())->errorListener();

        $lastError = error_get_last();
        $this->assertNotNull($lastError);
        $this->assertSame($message, $lastError['message']);
    }

    public function test_an_error_from_wp_statistics_is_logged()
    {
        $this->raiseFrom(WP_STATISTICS_DIR . 'tests/tmp-error-fixture/source.php', 'An error inside WP Statistics');

        (new ErrorsDetectorProvider())->errorListener();

        $this->assertSame(['An error inside WP Statistics'], $this->loggedMessages());
    }

    public function test_an_error_from_an_add_on_is_logged()
    {
        $this->raiseFrom(WP_PLUGIN_DIR . '/wp-statistics-test-addon/source.php', 'An error inside an add-on');

        (new ErrorsDetectorProvider())->errorListener();

        $this->assertSame(['An error inside an add-on'], $this->loggedMessages());
    }

    public function test_the_same_error_is_logged_once_per_request()
    {
        $this->raiseFrom(WP_STATISTICS_DIR . 'tests/tmp-error-fixture/source.php', 'Raised once');

        (new ErrorsDetectorProvider())->errorListener();
        (new ErrorsDetectorProvider())->errorListener();

        $this->assertSame(['Raised once'], $this->loggedMessages());
    }

    public function test_a_filter_can_log_errors_from_anywhere()
    {
        $logEverything = static function () {
            return true;
        };
        add_filter('wp_statistics_tracker_debugger_log_error', $logEverything);

        $this->raiseFrom(WP_PLUGIN_DIR . '/kirki-test-fixture/field.php', 'Logged by request');
        (new ErrorsDetectorProvider())->errorListener();

        remove_filter('wp_statistics_tracker_debugger_log_error', $logEverything);

        $this->assertSame(['Logged by request'], $this->loggedMessages());
    }
}
