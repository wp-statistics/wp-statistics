<?php

namespace WP_Statistics\Tests\Hits;

use WP_UnitTestCase;
use WP_Statistics\Service\Tracking\Core\Hits;
use WP_Statistics\Service\Tracking\TrackingFactory;
use WP_Statistics\Abstracts\BaseTracking;

/**
 * Tests for the Hits tracker after dead code removal.
 *
 * Verifies that the constructor has no side effects, factory works,
 * and record() validates resource identifiers.
 *
 * @since 15.0.0
 */
class Test_Hits extends WP_UnitTestCase
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
     * After cleanup, Hits constructor should not register any WordPress hooks.
     */
    public function test_constructor_does_not_register_init_hooks()
    {
        $beforeInitCount = has_action('init');

        new Hits();

        $afterInitCount = has_action('init');
        $this->assertSame($beforeInitCount, $afterInitCount, 'Hits constructor should not register init hooks');
    }

    /**
     * Hits should not have loginpage callback method anymore.
     */
    public function test_login_page_callback_removed()
    {
        $hits = new Hits();
        $this->assertFalse(method_exists($hits, 'trackLoginPageCallback'));
    }

    /**
     * Hits should not have REST-specific methods anymore.
     */
    public function test_rest_methods_removed()
    {
        $hits = new Hits();
        $this->assertFalse(method_exists($hits, 'isRestHit'));
        $this->assertFalse(method_exists($hits, 'getRestParams'));
    }

    /**
     * BaseTracking should not have getRestHitsKey anymore.
     */
    public function test_base_tracking_rest_key_removed()
    {
        $this->assertFalse(method_exists(BaseTracking::class, 'getRestHitsKey'));
    }

    /**
     * TrackingFactory::hits() should return a Hits instance.
     */
    public function test_tracking_factory_returns_hits_instance()
    {
        $hits = TrackingFactory::hits();
        $this->assertInstanceOf(Hits::class, $hits);
    }

    /**
     * record() should throw when resourceUriId is missing.
     */
    public function test_record_throws_when_resource_uri_id_missing()
    {
        // Provide enough to pass exclusion, but no resource identifiers
        unset($_REQUEST['resourceUriId']);
        $_REQUEST['resource_id'] = '1';

        $hits = new Hits();
        $this->expectException(\Exception::class);
        $hits->record();
    }

    /**
     * record() should throw when resource_id is missing.
     */
    public function test_record_throws_when_resource_id_missing()
    {
        $_REQUEST['resourceUriId'] = '1';
        unset($_REQUEST['resource_id']);

        $hits = new Hits();
        $this->expectException(\Exception::class);
        $hits->record();
    }
}
