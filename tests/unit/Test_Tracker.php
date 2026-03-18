<?php

namespace WP_Statistics\Tests\Tracker;

use WP_UnitTestCase;
use WP_Statistics\Service\Tracking\Core\Tracker;

/**
 * Tests for the Tracker class after dead code removal.
 *
 * Verifies that the constructor has no side effects,
 * and record() validates resource identifiers.
 *
 * @since 15.0.0
 */
class Test_Tracker extends WP_UnitTestCase
{
    public function tearDown(): void
    {
        unset(
            $_REQUEST['resourceUriId'],
            $_REQUEST['resource_id'],
            $_REQUEST['resourceUri']
        );
        parent::tearDown();
    }

    /**
     * After cleanup, Tracker constructor should not register any WordPress hooks.
     */
    public function test_constructor_does_not_register_init_hooks()
    {
        $beforeInitCount = has_action('init');

        new Tracker();

        $afterInitCount = has_action('init');
        $this->assertSame($beforeInitCount, $afterInitCount, 'Tracker constructor should not register init hooks');
    }

    /**
     * Tracker should not have loginpage callback method anymore.
     */
    public function test_login_page_callback_removed()
    {
        $tracker = new Tracker();
        $this->assertFalse(method_exists($tracker, 'trackLoginPageCallback'));
    }

    /**
     * Tracker should not have REST-specific methods anymore.
     */
    public function test_rest_methods_removed()
    {
        $tracker = new Tracker();
        $this->assertFalse(method_exists($tracker, 'isRestHit'));
        $this->assertFalse(method_exists($tracker, 'getRestParams'));
    }

    /**
     * Tracker can be directly instantiated.
     */
    public function test_tracker_can_be_instantiated()
    {
        $tracker = new Tracker();
        $this->assertInstanceOf(Tracker::class, $tracker);
    }

    /**
     * record() should throw when resourceUriId is missing.
     */
    public function test_record_throws_when_resource_uri_id_missing()
    {
        unset($_REQUEST['resourceUriId']);
        $_REQUEST['resource_id'] = '1';

        $tracker = new Tracker();
        $this->expectException(\Exception::class);
        $tracker->record();
    }

    /**
     * record() should throw when resource_id is missing.
     */
    public function test_record_throws_when_resource_id_missing()
    {
        $_REQUEST['resourceUriId'] = '1';
        unset($_REQUEST['resource_id']);

        $tracker = new Tracker();
        $this->expectException(\Exception::class);
        $tracker->record();
    }
}
