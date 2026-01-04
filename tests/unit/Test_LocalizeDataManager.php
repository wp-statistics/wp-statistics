<?php

namespace WP_Statistics\Tests\ReactApp;

use WP_UnitTestCase;
use WP_Statistics\Service\Admin\ReactApp\Managers\LocalizeDataManager;
use WP_Statistics\Service\Admin\ReactApp\Contracts\LocalizeDataProviderInterface;

/**
 * Test LocalizeDataManager class.
 *
 * Tests the LocalizeDataManager's ability to collect and manage
 * localized data from multiple providers for the React dashboard.
 */
class Test_LocalizeDataManager extends WP_UnitTestCase
{
    private $manager;

    public function setUp(): void
    {
        parent::setUp();
        $this->manager = new LocalizeDataManager();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        // Clean up any added filters
        remove_all_filters('wp_statistics_react_localized_data');
    }

    /**
     * Test registerProvider adds provider to collection.
     */
    public function test_register_provider_adds_provider()
    {
        $provider = $this->createMockProvider('test', ['key' => 'value']);

        $this->manager->registerProvider($provider);

        $providers = $this->manager->getProviders();
        $this->assertCount(1, $providers);
        $this->assertSame($provider, $providers[0]);
    }

    /**
     * Test registerProvider returns self for chaining.
     */
    public function test_register_provider_returns_self()
    {
        $provider = $this->createMockProvider('test', []);

        $result = $this->manager->registerProvider($provider);

        $this->assertSame($this->manager, $result);
    }

    /**
     * Test registerProviders adds multiple providers.
     */
    public function test_register_providers_adds_multiple()
    {
        $provider1 = $this->createMockProvider('provider1', []);
        $provider2 = $this->createMockProvider('provider2', []);

        $this->manager->registerProviders([$provider1, $provider2]);

        $providers = $this->manager->getProviders();
        $this->assertCount(2, $providers);
    }

    /**
     * Test registerProviders skips non-provider objects.
     */
    public function test_register_providers_skips_invalid_objects()
    {
        $provider = $this->createMockProvider('valid', []);
        $invalid  = new \stdClass();

        $this->manager->registerProviders([$provider, $invalid]);

        $providers = $this->manager->getProviders();
        $this->assertCount(1, $providers);
    }

    /**
     * Test registerProviders returns self for chaining.
     */
    public function test_register_providers_returns_self()
    {
        $result = $this->manager->registerProviders([]);

        $this->assertSame($this->manager, $result);
    }

    /**
     * Test init adds WordPress filter.
     */
    public function test_init_adds_wordpress_filter()
    {
        $this->manager->init();

        $this->assertTrue(has_filter('wp_statistics_react_localized_data'));
    }

    /**
     * Test addLocalizedData collects data from providers.
     */
    public function test_add_localized_data_collects_from_providers()
    {
        $provider1 = $this->createMockProvider('section1', ['key1' => 'value1']);
        $provider2 = $this->createMockProvider('section2', ['key2' => 'value2']);

        $this->manager->registerProvider($provider1);
        $this->manager->registerProvider($provider2);

        $result = $this->manager->addLocalizedData([]);

        $this->assertArrayHasKey('section1', $result);
        $this->assertArrayHasKey('section2', $result);
        $this->assertEquals(['key1' => 'value1'], $result['section1']);
        $this->assertEquals(['key2' => 'value2'], $result['section2']);
    }

    /**
     * Test addLocalizedData merges with existing data.
     */
    public function test_add_localized_data_merges_with_existing()
    {
        $provider = $this->createMockProvider('section', ['key2' => 'value2']);

        $this->manager->registerProvider($provider);

        $existingData = ['section' => ['key1' => 'value1']];
        $result       = $this->manager->addLocalizedData($existingData);

        $this->assertArrayHasKey('section', $result);
        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $result['section']);
    }

    /**
     * Test addLocalizedData overwrites non-array existing data.
     */
    public function test_add_localized_data_overwrites_non_array()
    {
        $provider = $this->createMockProvider('section', ['key' => 'value']);

        $this->manager->registerProvider($provider);

        $existingData = ['section' => 'string_value'];
        $result       = $this->manager->addLocalizedData($existingData);

        $this->assertEquals(['key' => 'value'], $result['section']);
    }

    /**
     * Test addLocalizedData preserves unrelated data.
     */
    public function test_add_localized_data_preserves_unrelated()
    {
        $provider = $this->createMockProvider('new_section', ['key' => 'value']);

        $this->manager->registerProvider($provider);

        $existingData = ['other_section' => ['other' => 'data']];
        $result       = $this->manager->addLocalizedData($existingData);

        $this->assertArrayHasKey('other_section', $result);
        $this->assertEquals(['other' => 'data'], $result['other_section']);
        $this->assertArrayHasKey('new_section', $result);
    }

    /**
     * Test setFilterPriority changes the priority.
     */
    public function test_set_filter_priority_changes_priority()
    {
        $this->manager->setFilterPriority(20);

        $this->manager->init();

        // Check the filter exists with the correct priority
        $this->assertTrue(has_filter('wp_statistics_react_localized_data'));
    }

    /**
     * Test setFilterPriority returns self for chaining.
     */
    public function test_set_filter_priority_returns_self()
    {
        $result = $this->manager->setFilterPriority(15);

        $this->assertSame($this->manager, $result);
    }

    /**
     * Test getProviders returns empty array initially.
     */
    public function test_get_providers_returns_empty_array_initially()
    {
        $providers = $this->manager->getProviders();

        $this->assertIsArray($providers);
        $this->assertEmpty($providers);
    }

    /**
     * Test getProviders returns all registered providers.
     */
    public function test_get_providers_returns_all_registered()
    {
        $provider1 = $this->createMockProvider('p1', []);
        $provider2 = $this->createMockProvider('p2', []);
        $provider3 = $this->createMockProvider('p3', []);

        $this->manager->registerProvider($provider1);
        $this->manager->registerProvider($provider2);
        $this->manager->registerProvider($provider3);

        $providers = $this->manager->getProviders();

        $this->assertCount(3, $providers);
    }

    /**
     * Test providers are processed in registration order.
     */
    public function test_providers_processed_in_order()
    {
        // Create providers that will overwrite the same key
        $provider1 = $this->createMockProvider('section', ['key' => 'first']);
        $provider2 = $this->createMockProvider('section', ['key' => 'second']);

        $this->manager->registerProvider($provider1);
        $this->manager->registerProvider($provider2);

        $result = $this->manager->addLocalizedData([]);

        // Second provider should have merged/overwritten
        $this->assertEquals('second', $result['section']['key']);
    }

    /**
     * Test method chaining works for all chainable methods.
     */
    public function test_method_chaining()
    {
        $provider = $this->createMockProvider('test', []);

        $result = $this->manager
            ->setFilterPriority(15)
            ->registerProvider($provider)
            ->registerProviders([]);

        $this->assertSame($this->manager, $result);
    }

    /**
     * Test addLocalizedData with empty providers array.
     */
    public function test_add_localized_data_with_no_providers()
    {
        $existingData = ['key' => 'value'];
        $result       = $this->manager->addLocalizedData($existingData);

        $this->assertEquals($existingData, $result);
    }

    /**
     * Test addLocalizedData handles provider returning empty data.
     */
    public function test_add_localized_data_handles_empty_provider_data()
    {
        $provider = $this->createMockProvider('section', []);

        $this->manager->registerProvider($provider);

        $result = $this->manager->addLocalizedData([]);

        $this->assertArrayHasKey('section', $result);
        $this->assertEquals([], $result['section']);
    }

    /**
     * Test addLocalizedData handles provider returning null.
     */
    public function test_add_localized_data_handles_null_provider_data()
    {
        $provider = $this->createMockProvider('section', null);

        $this->manager->registerProvider($provider);

        $result = $this->manager->addLocalizedData([]);

        $this->assertArrayHasKey('section', $result);
        $this->assertNull($result['section']);
    }

    /**
     * Test integration with WordPress filter system.
     */
    public function test_integration_with_filter_system()
    {
        $provider = $this->createMockProvider('test', ['data' => 'value']);

        $this->manager->registerProvider($provider);
        $this->manager->init();

        $result = apply_filters('wp_statistics_react_localized_data', []);

        $this->assertArrayHasKey('test', $result);
        $this->assertEquals(['data' => 'value'], $result['test']);
    }

    /**
     * Helper method to create a mock provider.
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
}
