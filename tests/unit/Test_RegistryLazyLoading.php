<?php

namespace WP_Statistics\Tests;

use WP_Statistics\Service\AnalyticsQuery\Registry\FilterRegistry;
use WP_Statistics\Service\AnalyticsQuery\Registry\GroupByRegistry;
use WP_Statistics\Service\AnalyticsQuery\Registry\SourceRegistry;
use WP_Statistics\Service\AnalyticsQuery\Contracts\FilterInterface;
use WP_Statistics\Service\AnalyticsQuery\Contracts\GroupByInterface;
use WP_Statistics\Service\AnalyticsQuery\Contracts\SourceInterface;
use WP_UnitTestCase;
use ReflectionClass;

/**
 * Unit tests for Registry Lazy Loading functionality.
 *
 * Tests verify that FilterRegistry, GroupByRegistry, and SourceRegistry
 * properly implement lazy loading to defer object instantiation until needed.
 *
 * @covers \WP_Statistics\Service\AnalyticsQuery\Registry\FilterRegistry
 * @covers \WP_Statistics\Service\AnalyticsQuery\Registry\GroupByRegistry
 * @covers \WP_Statistics\Service\AnalyticsQuery\Registry\SourceRegistry
 *
 * @since 15.0.0
 */
class Test_RegistryLazyLoading extends WP_UnitTestCase
{
    /**
     * Reset singleton instances before each test.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->resetSingletons();
    }

    /**
     * Reset singleton instances after each test.
     */
    public function tearDown(): void
    {
        $this->resetSingletons();
        parent::tearDown();
    }

    /**
     * Reset all registry singletons.
     */
    private function resetSingletons(): void
    {
        $registries = [
            FilterRegistry::class,
            GroupByRegistry::class,
            SourceRegistry::class,
        ];

        foreach ($registries as $class) {
            $reflection = new ReflectionClass($class);
            $instanceProperty = $reflection->getProperty('instance');
            $instanceProperty->setAccessible(true);
            $instanceProperty->setValue(null, null);
        }
    }

    /**
     * Get private property value from registry instance.
     */
    private function getPrivateProperty(object $object, string $property)
    {
        $reflection = new ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        return $prop->getValue($object);
    }

    // =========================================================================
    // FilterRegistry Tests
    // =========================================================================

    /**
     * Test FilterRegistry doesn't instantiate filters on creation.
     */
    public function test_filter_registry_lazy_loads_on_creation()
    {
        $registry = FilterRegistry::getInstance();

        $filters = $this->getPrivateProperty($registry, 'filters');
        $filterClasses = $this->getPrivateProperty($registry, 'filterClasses');

        // No filters should be instantiated yet
        $this->assertEmpty($filters, 'No filters should be instantiated on registry creation');

        // Class names should be registered
        $this->assertNotEmpty($filterClasses, 'Filter class names should be registered');
        $this->assertArrayHasKey('country', $filterClasses, 'country filter class should be registered');
    }

    /**
     * Test FilterRegistry::has() works with lazy loading.
     */
    public function test_filter_registry_has_with_lazy_loading()
    {
        $registry = FilterRegistry::getInstance();

        // has() should work without instantiating the filter
        $this->assertTrue($registry->has('country'), 'has() should return true for registered filter');
        $this->assertTrue($registry->has('browser'), 'has() should return true for registered filter');
        $this->assertFalse($registry->has('nonexistent'), 'has() should return false for unregistered filter');

        // Filters should still not be instantiated
        $filters = $this->getPrivateProperty($registry, 'filters');
        $this->assertEmpty($filters, 'has() should not instantiate filters');
    }

    /**
     * Test FilterRegistry::get() instantiates filter on demand.
     */
    public function test_filter_registry_get_instantiates_on_demand()
    {
        $registry = FilterRegistry::getInstance();

        // Get a filter
        $countryFilter = $registry->get('country');

        $this->assertInstanceOf(FilterInterface::class, $countryFilter, 'get() should return FilterInterface');

        // Only the requested filter should be instantiated
        $filters = $this->getPrivateProperty($registry, 'filters');
        $this->assertCount(1, $filters, 'Only one filter should be instantiated');
        $this->assertArrayHasKey('country', $filters, 'country filter should be instantiated');
    }

    /**
     * Test FilterRegistry::get() caches instances.
     */
    public function test_filter_registry_get_caches_instances()
    {
        $registry = FilterRegistry::getInstance();

        $first = $registry->get('country');
        $second = $registry->get('country');

        $this->assertSame($first, $second, 'get() should return cached instance');
    }

    /**
     * Test FilterRegistry::get() returns null for invalid filters.
     */
    public function test_filter_registry_get_returns_null_for_invalid()
    {
        $registry = FilterRegistry::getInstance();

        $result = $registry->get('nonexistent');

        $this->assertNull($result, 'get() should return null for unregistered filter');
    }

    /**
     * Test FilterRegistry::getAll() returns all filter names.
     */
    public function test_filter_registry_get_all_returns_all_names()
    {
        $registry = FilterRegistry::getInstance();

        $allNames = $registry->getAll();

        $this->assertIsArray($allNames, 'getAll() should return array');
        $this->assertContains('country', $allNames, 'getAll() should contain country');
        $this->assertContains('browser', $allNames, 'getAll() should contain browser');
        $this->assertContains('referrer', $allNames, 'getAll() should contain referrer');
    }

    /**
     * Test FilterRegistry::registerClass() for third-party extensions.
     */
    public function test_filter_registry_register_class()
    {
        $registry = FilterRegistry::getInstance();

        // Register a custom filter class
        $registry->registerClass('custom_filter', \WP_Statistics\Service\AnalyticsQuery\Filters\CountryFilter::class);

        $this->assertTrue($registry->has('custom_filter'), 'Registered class should be accessible');

        $filter = $registry->get('custom_filter');
        $this->assertInstanceOf(FilterInterface::class, $filter, 'Registered class should instantiate');
    }

    /**
     * Test FilterRegistry::register() takes precedence over class registration.
     */
    public function test_filter_registry_instance_takes_precedence()
    {
        $registry = FilterRegistry::getInstance();

        // Get the country filter
        $original = $registry->get('country');

        // Create a new instance
        $newInstance = new \WP_Statistics\Service\AnalyticsQuery\Filters\BrowserFilter();

        // Register instance
        $registry->register('country', $newInstance);

        // Should now return the new instance
        $retrieved = $registry->get('country');
        $this->assertSame($newInstance, $retrieved, 'Instance registration should take precedence');
    }

    // =========================================================================
    // GroupByRegistry Tests
    // =========================================================================

    /**
     * Test GroupByRegistry doesn't instantiate group_bys on creation.
     */
    public function test_groupby_registry_lazy_loads_on_creation()
    {
        $registry = GroupByRegistry::getInstance();

        $groupBys = $this->getPrivateProperty($registry, 'groupBy');
        $groupByClasses = $this->getPrivateProperty($registry, 'groupByClasses');

        // No group_bys should be instantiated yet
        $this->assertEmpty($groupBys, 'No group_bys should be instantiated on registry creation');

        // Class names should be registered
        $this->assertNotEmpty($groupByClasses, 'GroupBy class names should be registered');
        $this->assertArrayHasKey('date', $groupByClasses, 'date group_by class should be registered');
    }

    /**
     * Test GroupByRegistry::has() works with lazy loading.
     */
    public function test_groupby_registry_has_with_lazy_loading()
    {
        $registry = GroupByRegistry::getInstance();

        $this->assertTrue($registry->has('date'), 'has() should return true for registered group_by');
        $this->assertTrue($registry->has('country'), 'has() should return true for registered group_by');
        $this->assertFalse($registry->has('nonexistent'), 'has() should return false for unregistered group_by');

        // Group_bys should still not be instantiated
        $groupBys = $this->getPrivateProperty($registry, 'groupBy');
        $this->assertEmpty($groupBys, 'has() should not instantiate group_bys');
    }

    /**
     * Test GroupByRegistry::get() instantiates on demand.
     */
    public function test_groupby_registry_get_instantiates_on_demand()
    {
        $registry = GroupByRegistry::getInstance();

        $dateGroupBy = $registry->get('date');

        $this->assertInstanceOf(GroupByInterface::class, $dateGroupBy, 'get() should return GroupByInterface');

        $groupBys = $this->getPrivateProperty($registry, 'groupBy');
        $this->assertCount(1, $groupBys, 'Only one group_by should be instantiated');
        $this->assertArrayHasKey('date', $groupBys, 'date group_by should be instantiated');
    }

    /**
     * Test GroupByRegistry::get() caches instances.
     */
    public function test_groupby_registry_get_caches_instances()
    {
        $registry = GroupByRegistry::getInstance();

        $first = $registry->get('date');
        $second = $registry->get('date');

        $this->assertSame($first, $second, 'get() should return cached instance');
    }

    /**
     * Test GroupByRegistry::getAll() returns all names.
     */
    public function test_groupby_registry_get_all_returns_all_names()
    {
        $registry = GroupByRegistry::getInstance();

        $allNames = $registry->getAll();

        $this->assertIsArray($allNames, 'getAll() should return array');
        $this->assertContains('date', $allNames, 'getAll() should contain date');
        $this->assertContains('country', $allNames, 'getAll() should contain country');
        $this->assertContains('browser', $allNames, 'getAll() should contain browser');
    }

    /**
     * Test GroupByRegistry::registerClass() for third-party extensions.
     */
    public function test_groupby_registry_register_class()
    {
        $registry = GroupByRegistry::getInstance();

        $registry->registerClass('custom_groupby', \WP_Statistics\Service\AnalyticsQuery\GroupBy\DateGroupBy::class);

        $this->assertTrue($registry->has('custom_groupby'), 'Registered class should be accessible');

        $groupBy = $registry->get('custom_groupby');
        $this->assertInstanceOf(GroupByInterface::class, $groupBy, 'Registered class should instantiate');
    }

    // =========================================================================
    // SourceRegistry Tests
    // =========================================================================

    /**
     * Test SourceRegistry doesn't instantiate sources on creation.
     */
    public function test_source_registry_lazy_loads_on_creation()
    {
        $registry = SourceRegistry::getInstance();

        $sources = $this->getPrivateProperty($registry, 'sources');
        $sourceClasses = $this->getPrivateProperty($registry, 'sourceClasses');

        // No sources should be instantiated yet
        $this->assertEmpty($sources, 'No sources should be instantiated on registry creation');

        // Class names should be registered
        $this->assertNotEmpty($sourceClasses, 'Source class names should be registered');
        $this->assertArrayHasKey('visitors', $sourceClasses, 'visitors source class should be registered');
    }

    /**
     * Test SourceRegistry::has() works with lazy loading.
     */
    public function test_source_registry_has_with_lazy_loading()
    {
        $registry = SourceRegistry::getInstance();

        $this->assertTrue($registry->has('visitors'), 'has() should return true for registered source');
        $this->assertTrue($registry->has('views'), 'has() should return true for registered source');
        $this->assertFalse($registry->has('nonexistent'), 'has() should return false for unregistered source');

        // Sources should still not be instantiated
        $sources = $this->getPrivateProperty($registry, 'sources');
        $this->assertEmpty($sources, 'has() should not instantiate sources');
    }

    /**
     * Test SourceRegistry::get() instantiates on demand.
     */
    public function test_source_registry_get_instantiates_on_demand()
    {
        $registry = SourceRegistry::getInstance();

        $visitorsSource = $registry->get('visitors');

        $this->assertInstanceOf(SourceInterface::class, $visitorsSource, 'get() should return SourceInterface');

        $sources = $this->getPrivateProperty($registry, 'sources');
        $this->assertCount(1, $sources, 'Only one source should be instantiated');
        $this->assertArrayHasKey('visitors', $sources, 'visitors source should be instantiated');
    }

    /**
     * Test SourceRegistry::get() caches instances.
     */
    public function test_source_registry_get_caches_instances()
    {
        $registry = SourceRegistry::getInstance();

        $first = $registry->get('visitors');
        $second = $registry->get('visitors');

        $this->assertSame($first, $second, 'get() should return cached instance');
    }

    /**
     * Test SourceRegistry::getAll() returns all names.
     */
    public function test_source_registry_get_all_returns_all_names()
    {
        $registry = SourceRegistry::getInstance();

        $allNames = $registry->getAll();

        $this->assertIsArray($allNames, 'getAll() should return array');
        $this->assertContains('visitors', $allNames, 'getAll() should contain visitors');
        $this->assertContains('views', $allNames, 'getAll() should contain views');
        $this->assertContains('sessions', $allNames, 'getAll() should contain sessions');
        $this->assertContains('online_visitors', $allNames, 'getAll() should contain online_visitors');
    }

    /**
     * Test SourceRegistry::registerClass() for third-party extensions.
     */
    public function test_source_registry_register_class()
    {
        $registry = SourceRegistry::getInstance();

        $registry->registerClass('custom_source', \WP_Statistics\Service\AnalyticsQuery\Sources\VisitorsSource::class);

        $this->assertTrue($registry->has('custom_source'), 'Registered class should be accessible');

        $source = $registry->get('custom_source');
        $this->assertInstanceOf(SourceInterface::class, $source, 'Registered class should instantiate');
    }

    /**
     * Test SourceRegistry has online_visitors source.
     */
    public function test_source_registry_has_online_visitors()
    {
        $registry = SourceRegistry::getInstance();

        $this->assertTrue($registry->has('online_visitors'), 'online_visitors should be registered');

        $source = $registry->get('online_visitors');
        $this->assertInstanceOf(SourceInterface::class, $source, 'online_visitors should be valid source');
        $this->assertEquals('online_visitors', $source->getName(), 'source name should be online_visitors');
    }

    // =========================================================================
    // Integration Tests
    // =========================================================================

    /**
     * Test that multiple get() calls only instantiate requested items.
     */
    public function test_selective_instantiation_across_registries()
    {
        $filterRegistry = FilterRegistry::getInstance();
        $sourceRegistry = SourceRegistry::getInstance();
        $groupByRegistry = GroupByRegistry::getInstance();

        // Request specific items
        $filterRegistry->get('country');
        $sourceRegistry->get('visitors');
        $sourceRegistry->get('views');
        $groupByRegistry->get('date');

        // Verify only requested items are instantiated
        $filters = $this->getPrivateProperty($filterRegistry, 'filters');
        $sources = $this->getPrivateProperty($sourceRegistry, 'sources');
        $groupBys = $this->getPrivateProperty($groupByRegistry, 'groupBy');

        $this->assertCount(1, $filters, 'Only 1 filter should be instantiated');
        $this->assertCount(2, $sources, 'Only 2 sources should be instantiated');
        $this->assertCount(1, $groupBys, 'Only 1 group_by should be instantiated');
    }

    /**
     * Test helper methods work correctly with lazy loading.
     */
    public function test_registry_helper_methods_with_lazy_loading()
    {
        $filterRegistry = FilterRegistry::getInstance();
        $sourceRegistry = SourceRegistry::getInstance();
        $groupByRegistry = GroupByRegistry::getInstance();

        // Test FilterRegistry helper methods
        $this->assertNotNull($filterRegistry->getColumn('country'), 'getColumn() should work');
        $this->assertIsString($filterRegistry->getType('country'), 'getType() should work');

        // Test SourceRegistry helper methods
        $this->assertNotNull($sourceRegistry->getExpression('visitors'), 'getExpression() should work');
        $this->assertNotNull($sourceRegistry->getTable('visitors'), 'getTable() should work');

        // Test GroupByRegistry helper methods
        $this->assertIsArray($groupByRegistry->getSelectColumns('date'), 'getSelectColumns() should work');
        $this->assertNotNull($groupByRegistry->getGroupBy('date'), 'getGroupBy() should work');
    }
}
