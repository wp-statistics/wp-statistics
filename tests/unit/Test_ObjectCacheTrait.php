<?php

use WP_Statistics\Traits\ObjectCacheTrait;

/**
 * Class Test_ObjectCacheTrait
 *
 * Test case for the ObjectCacheTrait.
 */
class Test_ObjectCacheTrait extends WP_UnitTestCase
{
    /**
     * Instance of the class using ObjectCacheTrait.
     */
    private $test_instance;

    public function setUp(): void
    {
        parent::setUp();
        // Create a test instance of a class that uses the ObjectCacheTrait
        $this->test_instance = new class {
            use ObjectCacheTrait;
        };
    }

    /**
     * Test setting and getting cache values.
     */
    public function test_set_and_get_cache()
    {
        // Set a cache value
        $this->test_instance->setCache('key1', 'value1');

        // Assert cache value is returned correctly
        $this->assertEquals('value1', $this->test_instance->getCache('key1'));
    }

    /**
     * Test if cache is set correctly.
     */
    public function test_is_cache_set()
    {
        // Set a cache value
        $this->test_instance->setCache('key1', 'value1');

        // Assert cache is set
        $this->assertTrue($this->test_instance->isCacheSet('key1'));

        // Assert cache is not set for a different key
        $this->assertFalse($this->test_instance->isCacheSet('key2'));
    }

    /**
     * Test resetting the cache.
     */
    public function test_reset_cache()
    {
        // Set a cache value
        $this->test_instance->setCache('key1', 'value1');

        // Reset the cache
        $this->test_instance->resetCache();

        // Assert cache is reset and no longer set
        $this->assertFalse($this->test_instance->isCacheSet('key1'));
    }

    /**
     * Test getCachedData with a callback.
     */
    public function test_get_cached_data()
    {
        // Callback function to fetch data
        $callback = function () {
            return 'dynamic_value';
        };

        // Assert cache initially empty and callback is used to set cache
        $this->assertEquals('dynamic_value', $this->test_instance->getCachedData('key1', $callback));

        // Assert cache now contains the value and callback isn't called again
        $this->assertEquals('dynamic_value', $this->test_instance->getCache('key1'));
    }

    /**
     * Test getting cache with default value if not set.
     */
    public function test_get_cache_with_default()
    {
        // Set a default value
        $default_value = 'default_value';

        // Assert default value is returned when cache key is not set
        $this->assertEquals($default_value, $this->test_instance->getCache('non_existing_key', $default_value));
    }
}
