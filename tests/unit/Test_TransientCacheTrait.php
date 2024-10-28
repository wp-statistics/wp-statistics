<?php

use WP_Statistics\Traits\TransientCacheTrait;

/**
 * Class Test_TransientCacheTrait
 *
 * Test case for the TransientCacheTrait.
 */
class Test_TransientCacheTrait extends WP_UnitTestCase
{
    /**
     * Instance of the class using TransientCacheTrait.
     */
    private $test_instance;

    public function setUp(): void
    {
        parent::setUp();
        // Create a test instance of a class that uses the TransientCacheTrait
        $this->test_instance = new class {
            use TransientCacheTrait;
        };
    }

    /**
     * Test the getCacheKey method.
     */
    public function test_get_cache_key()
    {
        // Test with an input string
        $input = 'test_input';
        $expected_key = 'wp_statistics_cache_' . substr(md5($input), 0, 10);

        // Assert cache key is generated correctly
        $this->assertEquals($expected_key, $this->test_instance->getCacheKey($input));
    }

    /**
     * Test setting and getting a cached result.
     */
    public function test_set_and_get_cached_result()
    {
        $input = 'test_input';
        $result = 'cached_result';

        // Set the transient cache
        $this->test_instance->setCachedResult($input, $result);

        // Assert the cache was set correctly by retrieving it
        $this->assertEquals($result, $this->test_instance->getCachedResult($input));
    }

    /**
     * Test that getCachedResult returns false for a non-existent cache key.
     */
    public function test_get_cached_result_non_existent()
    {
        $input = 'non_existent_input';

        // Assert that non-existent cache returns false
        $this->assertFalse($this->test_instance->getCachedResult($input));
    }

    /**
     * Test setting cached result with expiry.
     */
    public function test_set_cached_result_with_expiry()
    {
        $input = 'expire_test_input';
        $result = 'cached_expire_result';

        // Set the transient cache with a 1 second expiry
        set_transient($this->test_instance->getCacheKey($input), $result, 1);

        // Assert the cache is set initially
        $this->assertEquals($result, $this->test_instance->getCachedResult($input));

        // Sleep for 2 seconds to allow the cache to expire
        sleep(2);

        // Assert the cache has expired and returns false
        $this->assertFalse($this->test_instance->getCachedResult($input));
    }
}
