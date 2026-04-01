<?php

namespace WP_Statistics\Tests\Entity;

use WP_UnitTestCase;
use WP_Statistics\Entity\Session;
use WP_Statistics\Service\Tracking\Core\Visitor;
use WP_Statistics\Entity\EntityFactory;
use ReflectionClass;

/**
 * Tests that Session entity correctly handles duration.
 *
 * Duration is managed exclusively by batch tracking (atomic accumulation).
 * Session::update() must NOT touch duration, and
 * Session::create() must initialize duration to 0.
 *
 * @since 15.0.0
 */
class Test_SessionDuration extends WP_UnitTestCase
{
    public function test_calculate_duration_method_removed()
    {
        $this->assertFalse(
            method_exists(Session::class, 'calculateDuration'),
            'calculateDuration should be removed — duration is managed by batch tracking'
        );
    }

    public function test_session_class_exists()
    {
        $this->assertTrue(class_exists(Session::class));
    }

    public function test_update_session_method_exists()
    {
        $this->assertTrue(
            method_exists(Session::class, 'update'),
            'update should exist'
        );
    }

    public function test_create_session_method_exists()
    {
        $this->assertTrue(
            method_exists(Session::class, 'create'),
            'create method should exist'
        );
    }

    public function test_update_session_does_not_reference_duration()
    {
        $reflection = new ReflectionClass(Session::class);
        $method = $reflection->getMethod('update');

        $filename = $method->getFileName();
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();

        $source = implode('', array_slice(file($filename), $startLine - 1, $endLine - $startLine + 1));

        $this->assertStringNotContainsString(
            'calculateDuration',
            $source,
            'update should not call calculateDuration'
        );

        $this->assertDoesNotMatchRegularExpression(
            '/[\'"]duration[\'"]\s*=>/',
            $source,
            'update should not set duration in the updates array'
        );
    }

    public function test_create_session_initializes_duration_to_zero()
    {
        $reflection = new ReflectionClass(Session::class);
        $method = $reflection->getMethod('create');

        $filename = $method->getFileName();
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();

        $source = implode('', array_slice(file($filename), $startLine - 1, $endLine - $startLine + 1));

        $this->assertMatchesRegularExpression(
            '/[\'"]duration[\'"]\s*=>\s*0/',
            $source,
            'create() should initialize duration to 0 in the insert array'
        );
    }

    // ── updateEngagement ────────────────────────────────────────

    public function test_update_engagement_method_exists()
    {
        $this->assertTrue(
            method_exists(Session::class, 'updateEngagement'),
            'updateEngagement should exist on Session entity'
        );
    }

    public function test_update_engagement_returns_false_for_zero_ms()
    {
        $visitor = new Visitor();
        $session = EntityFactory::session($visitor);

        $this->assertFalse($session->updateEngagement(0));
    }

    public function test_update_engagement_returns_false_for_sub_second()
    {
        $visitor = new Visitor();
        $session = EntityFactory::session($visitor);

        $this->assertFalse($session->updateEngagement(400));
    }

    public function test_update_engagement_returns_false_when_no_session()
    {
        $visitor = new Visitor();
        $session = EntityFactory::session($visitor);

        // No session exists for this visitor — returns false
        $this->assertFalse($session->updateEngagement(5000));
    }

    public function test_update_engagement_uses_atomic_coalesce_pattern()
    {
        $reflection = new ReflectionClass(Session::class);
        $method = $reflection->getMethod('updateEngagement');

        $filename = $method->getFileName();
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();

        $source = implode('', array_slice(file($filename), $startLine - 1, $endLine - $startLine + 1));

        $this->assertStringContainsString(
            'COALESCE',
            $source,
            'updateEngagement should use COALESCE for atomic increment'
        );
    }
}
