<?php

namespace WP_Statistics\Tests\Service\Debugger;

use WP_Statistics\Option;
use WP_Statistics\Service\Debugger\DebuggerFactory;
use WP_UnitTestCase;

class Test_TrackerDebuggerPhp85 extends WP_UnitTestCase
{
    /**
     * Ensure the debugger's tracker checks remain free of PHP deprecations.
     */
    public function test_tracker_checks_do_not_emit_deprecations()
    {
        add_filter('pre_http_request', static function () {
            return [
                'headers'  => [],
                'body'     => '{"status":true}',
                'response' => ['code' => 200, 'message' => 'OK'],
                'cookies'  => [],
                'filename' => null,
            ];
        });

        $previousOptions = Option::getOptions();
        $deprecations    = [];

        set_error_handler(static function ($severity, $message, $file, $line) use (&$deprecations) {
            if (in_array($severity, [E_DEPRECATED, E_USER_DEPRECATED], true)) {
                $deprecations[] = sprintf('%s:%d %s', $file, $line, $message);
                return true;
            }

            return false;
        });

        try {
            foreach ([false, true] as $bypassAdBlockers) {
                Option::update('bypass_ad_blockers', $bypassAdBlockers);
                (new DebuggerFactory())->getAllProviders();
            }
        } finally {
            restore_error_handler();
            Option::save_options($previousOptions);
        }

        $this->assertSame([], $deprecations, implode("\n", $deprecations));
    }
}
