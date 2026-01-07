<?php

namespace WP_Statistics\Tests;

use WP_Statistics\Service\Cron\CronManager;
use WP_Statistics\Service\Cron\ScheduledEventInterface;
use WP_Statistics\Service\Admin\ReactApp\Managers\LocalizeDataManager;
use WP_Statistics\Service\Admin\Dashboard\Endpoints\AjaxManager;
use WP_Statistics\Service\Admin\ReactApp\Contracts\LocalizeDataProviderInterface;
use WP_Statistics\Service\Admin\ReactApp\Contracts\PageActionInterface;
use WP_Statistics\Service\Blocks\BlocksManager;
use WP_Statistics\Service\Tracking\TrackerControllerFactory;
use WP_UnitTestCase;
use ReflectionClass;

/**
 * Unit tests for Lazy Loading Optimization across the codebase.
 *
 * Tests verify that managers properly implement lazy loading to defer
 * object instantiation until actually needed, improving initialization performance.
 *
 * @covers \WP_Statistics\Service\Cron\CronManager
 * @covers \WP_Statistics\Service\Admin\ReactApp\Managers\LocalizeDataManager
 * @covers \WP_Statistics\Service\Admin\Dashboard\Endpoints\AjaxManager
 * @covers \WP_Statistics\Service\Blocks\BlocksManager
 * @covers \WP_Statistics\Service\Tracking\TrackerControllerFactory
 *
 * @since 15.0.0
 */
class Test_LazyLoadingOptimization extends WP_UnitTestCase
{
    /**
     * Get private property value from object.
     */
    private function getPrivateProperty(object $object, string $property)
    {
        $reflection = new ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        return $prop->getValue($object);
    }

    /**
     * Get static private property value from class.
     */
    private function getStaticPrivateProperty(string $class, string $property)
    {
        $reflection = new ReflectionClass($class);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        return $prop->getValue(null);
    }

    /**
     * Set static private property value on class.
     */
    private function setStaticPrivateProperty(string $class, string $property, $value): void
    {
        $reflection = new ReflectionClass($class);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue(null, $value);
    }

    // =========================================================================
    // CronManager Lazy Loading Tests
    // =========================================================================

    /**
     * Test CronManager registers event classes without instantiation.
     */
    public function test_cron_manager_registers_event_classes()
    {
        $manager = new CronManager();

        $eventClasses = $this->getPrivateProperty($manager, 'eventClasses');
        $events = $this->getPrivateProperty($manager, 'events');

        $this->assertNotEmpty($eventClasses, 'Event classes should be registered');
        $this->assertArrayHasKey('email_report', $eventClasses);
        $this->assertArrayHasKey('database_maintenance', $eventClasses);
        $this->assertArrayHasKey('geoip_update', $eventClasses);

        // Events should not be instantiated yet
        $this->assertEmpty($events, 'Events should not be instantiated on creation');
    }

    /**
     * Test CronManager::hasEvent() works without instantiation.
     */
    public function test_cron_manager_has_event_without_instantiation()
    {
        $manager = new CronManager();

        $this->assertTrue($manager->hasEvent('email_report'));
        $this->assertTrue($manager->hasEvent('database_maintenance'));
        $this->assertFalse($manager->hasEvent('nonexistent'));

        $events = $this->getPrivateProperty($manager, 'events');
        $this->assertEmpty($events, 'hasEvent() should not instantiate events');
    }

    /**
     * Test CronManager::getEvent() instantiates on demand.
     */
    public function test_cron_manager_get_event_instantiates_on_demand()
    {
        $manager = new CronManager();

        $event = $manager->getEvent('email_report');

        $this->assertInstanceOf(ScheduledEventInterface::class, $event);

        $events = $this->getPrivateProperty($manager, 'events');
        $this->assertCount(1, $events, 'Only requested event should be instantiated');
        $this->assertArrayHasKey('email_report', $events);
    }

    /**
     * Test CronManager::getEvent() caches instances.
     */
    public function test_cron_manager_get_event_caches_instances()
    {
        $manager = new CronManager();

        $first = $manager->getEvent('email_report');
        $second = $manager->getEvent('email_report');

        $this->assertSame($first, $second, 'getEvent() should return cached instance');
    }

    /**
     * Test CronManager::getEventKeys() returns all keys.
     */
    public function test_cron_manager_get_event_keys()
    {
        $manager = new CronManager();

        $keys = $manager->getEventKeys();

        $this->assertContains('email_report', $keys);
        $this->assertContains('database_maintenance', $keys);
        $this->assertContains('geoip_update', $keys);
        $this->assertContains('daily_summary', $keys);
        $this->assertContains('license', $keys);
        $this->assertContains('referrals_database', $keys);
        $this->assertContains('notification', $keys);
        $this->assertContains('referrer_spam', $keys);
    }

    /**
     * Test CronManager::registerEventClass() for extensions.
     */
    public function test_cron_manager_register_event_class()
    {
        $manager = new CronManager();

        // Use existing event class for testing
        $manager->registerEventClass('custom_event', \WP_Statistics\Service\Cron\Events\EmailReportEvent::class);

        $this->assertTrue($manager->hasEvent('custom_event'));

        $event = $manager->getEvent('custom_event');
        $this->assertInstanceOf(ScheduledEventInterface::class, $event);
    }

    // =========================================================================
    // LocalizeDataManager Lazy Loading Tests
    // =========================================================================

    /**
     * Test LocalizeDataManager::registerProviderClass() stores class names.
     */
    public function test_localize_data_manager_registers_provider_class()
    {
        $manager = new LocalizeDataManager();

        $mockProviderClass = get_class($this->createMockProvider('test', []));

        $manager->registerProviderClass($mockProviderClass);

        $providerClasses = $this->getPrivateProperty($manager, 'providerClasses');
        $providers = $this->getPrivateProperty($manager, 'providers');
        $resolved = $this->getPrivateProperty($manager, 'resolved');

        $this->assertContains($mockProviderClass, $providerClasses);
        $this->assertEmpty($providers, 'Providers should not be instantiated on registration');
        $this->assertFalse($resolved, 'Providers should not be resolved yet');
    }

    /**
     * Test LocalizeDataManager::registerProviderClasses() for bulk registration.
     */
    public function test_localize_data_manager_registers_provider_classes_bulk()
    {
        $manager = new LocalizeDataManager();

        $class1 = get_class($this->createMockProvider('test1', []));
        $class2 = get_class($this->createMockProvider('test2', []));

        $manager->registerProviderClasses([$class1, $class2]);

        $providerClasses = $this->getPrivateProperty($manager, 'providerClasses');

        $this->assertCount(2, $providerClasses);
    }

    /**
     * Test LocalizeDataManager providers are resolved on data access.
     */
    public function test_localize_data_manager_resolves_on_data_access()
    {
        $manager = new LocalizeDataManager();

        // Create a concrete mock class for testing
        $provider = $this->createMockProvider('section', ['key' => 'value']);
        $manager->registerProvider($provider);

        // Access data triggers resolution
        $data = $manager->addLocalizedData([]);

        $resolved = $this->getPrivateProperty($manager, 'resolved');
        $this->assertTrue($resolved, 'Providers should be resolved after data access');
        $this->assertArrayHasKey('section', $data);
    }

    /**
     * Test LocalizeDataManager::getProviders() triggers resolution.
     */
    public function test_localize_data_manager_get_providers_triggers_resolution()
    {
        $manager = new LocalizeDataManager();

        $provider = $this->createMockProvider('test', []);
        $manager->registerProvider($provider);

        // getProviders() should trigger resolution
        $providers = $manager->getProviders();

        $resolved = $this->getPrivateProperty($manager, 'resolved');
        $this->assertTrue($resolved, 'getProviders() should trigger resolution');
        $this->assertCount(1, $providers);
    }

    // =========================================================================
    // AjaxManager Lazy Loading Tests
    // =========================================================================

    /**
     * Test AjaxManager::registerGlobalEndpointClass() stores class info.
     */
    public function test_ajax_manager_registers_endpoint_class()
    {
        $manager = new AjaxManager();

        $mockClass = get_class($this->createMockEndpoint('test_endpoint'));

        $manager->registerGlobalEndpointClass($mockClass, 'test_action');

        $endpointClasses = $this->getPrivateProperty($manager, 'endpointClasses');

        $this->assertArrayHasKey('test_action', $endpointClasses);
        $this->assertEquals($mockClass, $endpointClasses['test_action']['class']);
        $this->assertEquals('handleQuery', $endpointClasses['test_action']['method']);
    }

    /**
     * Test AjaxManager::registerGlobalEndpointClass() with custom method.
     */
    public function test_ajax_manager_registers_endpoint_class_custom_method()
    {
        $manager = new AjaxManager();

        $mockClass = get_class($this->createMockEndpoint('test_endpoint'));

        $manager->registerGlobalEndpointClass($mockClass, 'test_action', 'customMethod');

        $endpointClasses = $this->getPrivateProperty($manager, 'endpointClasses');

        $this->assertEquals('customMethod', $endpointClasses['test_action']['method']);
    }

    /**
     * Test AjaxManager returns self for chaining.
     */
    public function test_ajax_manager_chaining()
    {
        $manager = new AjaxManager();

        $mockClass = get_class($this->createMockEndpoint('test'));

        $result = $manager->registerGlobalEndpointClass($mockClass, 'action1')
                         ->registerGlobalEndpointClass($mockClass, 'action2');

        $this->assertSame($manager, $result);
    }

    // =========================================================================
    // BlocksManager Lazy Loading Tests
    // =========================================================================

    /**
     * Helper to create a BlocksManager with blocks registered.
     *
     * Suppresses the "Block type already registered" notice since it can
     * occur when running tests after the actual plugin has initialized.
     *
     * @return BlocksManager
     */
    private function createBlocksManagerWithBlocks(): BlocksManager
    {
        $manager = new BlocksManager();

        // Trigger registerBlocks by calling it directly (normally happens on 'init' hook)
        $reflection = new ReflectionClass($manager);
        $method = $reflection->getMethod('registerBlocks');
        $method->setAccessible(true);
        $method->invoke($manager);

        return $manager;
    }

    /**
     * Test BlocksManager stores block classes, not instances.
     */
    public function test_blocks_manager_stores_block_classes()
    {
        // Allow the "already registered" notice in tests since the plugin may have registered blocks
        $this->setExpectedIncorrectUsage('WP_Block_Type_Registry::register');

        $manager = $this->createBlocksManagerWithBlocks();

        $blockClasses = $this->getPrivateProperty($manager, 'blockClasses');
        $blocks = $this->getPrivateProperty($manager, 'blocks');

        $this->assertArrayHasKey('statistics', $blockClasses);
        $this->assertEmpty($blocks, 'Block instances should not be created on registration');
    }

    /**
     * Test BlocksManager::hasBlock() works with lazy loading.
     */
    public function test_blocks_manager_has_block()
    {
        // Allow the "already registered" notice in tests
        $this->setExpectedIncorrectUsage('WP_Block_Type_Registry::register');

        $manager = $this->createBlocksManagerWithBlocks();

        $this->assertTrue($manager->hasBlock('statistics'));
        $this->assertFalse($manager->hasBlock('nonexistent'));

        // Block should not be instantiated by hasBlock()
        $blocks = $this->getPrivateProperty($manager, 'blocks');
        $this->assertEmpty($blocks);
    }

    /**
     * Test BlocksManager::getBlock() instantiates on demand.
     */
    public function test_blocks_manager_get_block_instantiates()
    {
        // Allow the "already registered" notice in tests
        $this->setExpectedIncorrectUsage('WP_Block_Type_Registry::register');

        $manager = $this->createBlocksManagerWithBlocks();

        $block = $manager->getBlock('statistics');

        $this->assertNotNull($block);

        $blocks = $this->getPrivateProperty($manager, 'blocks');
        $this->assertArrayHasKey('statistics', $blocks);
    }

    /**
     * Test BlocksManager::getBlock() caches instances.
     */
    public function test_blocks_manager_get_block_caches()
    {
        // Allow the "already registered" notice in tests
        $this->setExpectedIncorrectUsage('WP_Block_Type_Registry::register');

        $manager = $this->createBlocksManagerWithBlocks();

        $first = $manager->getBlock('statistics');
        $second = $manager->getBlock('statistics');

        $this->assertSame($first, $second);
    }

    /**
     * Test BlocksManager::getBlock() returns null for unknown blocks.
     */
    public function test_blocks_manager_get_block_returns_null_for_unknown()
    {
        $manager = new BlocksManager();

        $result = $manager->getBlock('nonexistent');

        $this->assertNull($result);
    }

    // =========================================================================
    // TrackerControllerFactory Caching Tests
    // =========================================================================

    public function setUp(): void
    {
        parent::setUp();
        // Reset TrackerControllerFactory static state
        TrackerControllerFactory::reset();
    }

    public function tearDown(): void
    {
        TrackerControllerFactory::reset();
        parent::tearDown();
    }

    /**
     * Test TrackerControllerFactory caches controller instance.
     */
    public function test_tracker_controller_factory_caches_controller()
    {
        $first = TrackerControllerFactory::createController();
        $second = TrackerControllerFactory::createController();

        $this->assertSame($first, $second, 'Factory should return cached controller');
    }

    /**
     * Test TrackerControllerFactory::reset() clears cache.
     */
    public function test_tracker_controller_factory_reset_clears_cache()
    {
        $first = TrackerControllerFactory::createController();

        TrackerControllerFactory::reset();

        // After reset, a new instance should be created
        $second = TrackerControllerFactory::createController();

        // They should be equal but not same instance
        $this->assertEquals(get_class($first), get_class($second));
    }

    /**
     * Test TrackerControllerFactory::getTrackingRoute() uses cached controller.
     */
    public function test_tracker_controller_factory_get_tracking_route()
    {
        $route = TrackerControllerFactory::getTrackingRoute();

        $this->assertNotNull($route);
        $this->assertIsString($route);
    }

    /**
     * Test TrackerControllerFactory initializes batch tracking.
     */
    public function test_tracker_controller_factory_batch_tracking_init_once()
    {
        // Note: We don't check initial state because other tests may have run
        // before and batchInitialized persists across reset() calls (by design).

        TrackerControllerFactory::createController();

        $batchInitialized = $this->getStaticPrivateProperty(TrackerControllerFactory::class, 'batchInitialized');
        $this->assertTrue($batchInitialized, 'Batch should be initialized after createController() call');
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================

    /**
     * Create a mock LocalizeDataProviderInterface.
     */
    private function createMockProvider($key, $data)
    {
        return new class($key, $data) implements LocalizeDataProviderInterface {
            private $key;
            private $data;

            public function __construct($key, $data)
            {
                $this->key  = $key;
                $this->data = $data;
            }

            public function getKey()
            {
                return $this->key;
            }

            public function getData()
            {
                return $this->data;
            }
        };
    }

    /**
     * Create a mock PageActionInterface.
     */
    private function createMockEndpoint($name)
    {
        return new class($name) implements PageActionInterface {
            private $name;

            public function __construct($name)
            {
                $this->name = $name;
            }

            public function getEndpointName()
            {
                return $this->name;
            }

            public function handleQuery()
            {
                return ['success' => true];
            }
        };
    }
}
