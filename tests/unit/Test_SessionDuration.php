<?php

namespace WP_Statistics\Tests\Entity;

use WP_UnitTestCase;
use WP_Statistics\Entity\Session;
use ReflectionClass;

/**
 * Tests that Session entity correctly handles duration.
 *
 * Duration is now managed exclusively by BatchTracking (atomic accumulation).
 * Session::updateInitialView() must NOT touch duration, and
 * Session::record() must initialize duration to 0.
 *
 * @since 15.0.0
 */
class Test_SessionDuration extends WP_UnitTestCase
{
    public function test_calculate_duration_method_removed()
    {
        $this->assertFalse(
            method_exists(Session::class, 'calculateDuration'),
            'calculateDuration should be removed — duration is managed by BatchTracking'
        );
    }

    public function test_session_class_exists()
    {
        $this->assertTrue(class_exists(Session::class));
    }

    public function test_update_initial_view_method_exists()
    {
        $this->assertTrue(
            method_exists(Session::class, 'updateInitialView'),
            'updateInitialView should still exist'
        );
    }

    public function test_record_method_exists()
    {
        $this->assertTrue(
            method_exists(Session::class, 'record'),
            'record method should exist'
        );
    }

    public function test_update_initial_view_does_not_reference_duration_or_calculate()
    {
        $reflection = new ReflectionClass(Session::class);
        $method = $reflection->getMethod('updateInitialView');

        $filename = $method->getFileName();
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();

        $source = implode('', array_slice(file($filename), $startLine - 1, $endLine - $startLine + 1));

        $this->assertStringNotContainsString(
            'calculateDuration',
            $source,
            'updateInitialView should not call calculateDuration'
        );

        // Duration should not appear as a key being set in updates
        $this->assertDoesNotMatchRegularExpression(
            '/[\'"]duration[\'"]\s*=>/',
            $source,
            'updateInitialView should not set duration in the updates array'
        );
    }

    public function test_record_method_initializes_duration_to_zero()
    {
        $reflection = new ReflectionClass(Session::class);
        $method = $reflection->getMethod('record');

        $filename = $method->getFileName();
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();

        $source = implode('', array_slice(file($filename), $startLine - 1, $endLine - $startLine + 1));

        $this->assertMatchesRegularExpression(
            '/[\'"]duration[\'"]\s*=>\s*0/',
            $source,
            'record() should initialize duration to 0 in the insert array'
        );
    }
}
