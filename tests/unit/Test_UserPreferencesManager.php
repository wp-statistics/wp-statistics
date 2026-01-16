<?php

namespace WP_Statistics\Tests;

use WP_Statistics\Service\Admin\UserPreferences\UserPreferencesManager;
use WP_UnitTestCase;

/**
 * Test UserPreferencesManager class.
 */
class Test_UserPreferencesManager extends WP_UnitTestCase
{
    /**
     * Test user ID.
     *
     * @var int
     */
    private $testUserId;

    /**
     * Set up test fixtures.
     */
    public function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->testUserId = $this->factory->user->create([
            'role' => 'administrator',
        ]);

        // Set current user
        wp_set_current_user($this->testUserId);
    }

    /**
     * Tear down test fixtures.
     */
    public function tearDown(): void
    {
        // Clean up user meta
        delete_user_meta($this->testUserId, UserPreferencesManager::META_KEY);

        parent::tearDown();
    }

    /**
     * Test save and get preferences for a context.
     */
    public function test_save_and_get_preferences()
    {
        $manager = new UserPreferencesManager($this->testUserId);

        $data = [
            'columns'      => ['date', 'visitors', 'views'],
            'column_order' => ['date', 'visitors', 'views'],
        ];

        $result = $manager->save('visitors_overview', $data);

        $this->assertTrue($result);

        $preferences = $manager->get('visitors_overview');

        $this->assertIsArray($preferences);
        $this->assertArrayHasKey('columns', $preferences);
        $this->assertArrayHasKey('column_order', $preferences);
        $this->assertArrayHasKey('updated_at', $preferences);
        $this->assertEquals(['date', 'visitors', 'views'], $preferences['columns']);
    }

    /**
     * Test get returns null for non-existent context.
     */
    public function test_get_returns_null_for_non_existent_context()
    {
        $manager = new UserPreferencesManager($this->testUserId);

        $preferences = $manager->get('non_existent_context');

        $this->assertNull($preferences);
    }

    /**
     * Test reset preferences for a context.
     */
    public function test_reset_preferences()
    {
        $manager = new UserPreferencesManager($this->testUserId);

        // First save some preferences
        $manager->save('visitors_overview', [
            'columns' => ['date', 'visitors'],
        ]);

        // Verify they exist
        $this->assertTrue($manager->exists('visitors_overview'));

        // Reset them
        $result = $manager->reset('visitors_overview');

        $this->assertTrue($result);
        $this->assertFalse($manager->exists('visitors_overview'));
        $this->assertNull($manager->get('visitors_overview'));
    }

    /**
     * Test reset non-existent context returns true.
     */
    public function test_reset_non_existent_context_returns_true()
    {
        $manager = new UserPreferencesManager($this->testUserId);

        $result = $manager->reset('non_existent_context');

        $this->assertTrue($result);
    }

    /**
     * Test exists method.
     */
    public function test_exists()
    {
        $manager = new UserPreferencesManager($this->testUserId);

        $this->assertFalse($manager->exists('visitors_overview'));

        $manager->save('visitors_overview', ['columns' => ['date']]);

        $this->assertTrue($manager->exists('visitors_overview'));
    }

    /**
     * Test getAll returns all contexts.
     */
    public function test_get_all()
    {
        $manager = new UserPreferencesManager($this->testUserId);

        $manager->save('visitors_overview', ['columns' => ['date', 'visitors']]);
        $manager->save('top_pages', ['columns' => ['page', 'views']]);

        $all = $manager->getAll();

        $this->assertIsArray($all);
        $this->assertArrayHasKey('visitors_overview', $all);
        $this->assertArrayHasKey('top_pages', $all);
    }

    /**
     * Test resetAll removes all preferences.
     */
    public function test_reset_all()
    {
        $manager = new UserPreferencesManager($this->testUserId);

        $manager->save('visitors_overview', ['columns' => ['date']]);
        $manager->save('top_pages', ['columns' => ['page']]);

        $result = $manager->resetAll();

        $this->assertTrue($result);

        $all = $manager->getAll();
        $this->assertEmpty($all);
    }

    /**
     * Test invalid context name is rejected.
     */
    public function test_invalid_context_name_rejected()
    {
        $manager = new UserPreferencesManager($this->testUserId);

        // Test with special characters (@ symbol)
        $result = $manager->save('invalid@context', ['columns' => ['date']]);
        $this->assertFalse($result);

        // Test with spaces
        $result = $manager->save('invalid context', ['columns' => ['date']]);
        $this->assertFalse($result);

        // Test with dots
        $result = $manager->save('invalid.context', ['columns' => ['date']]);
        $this->assertFalse($result);

        // Test empty string
        $result = $manager->save('', ['columns' => ['date']]);
        $this->assertFalse($result);
    }

    /**
     * Test context names with hyphens are accepted.
     */
    public function test_context_names_with_hyphens_accepted()
    {
        $manager = new UserPreferencesManager($this->testUserId);

        // Hyphens are valid in context names (e.g., visitors-overview route path)
        $result = $manager->save('visitors-overview', ['columns' => ['date']]);
        $this->assertTrue($result);

        $result = $manager->save('page-insights-overview', ['columns' => ['date']]);
        $this->assertTrue($result);
    }

    /**
     * Test valid context names are accepted.
     */
    public function test_valid_context_names_accepted()
    {
        $manager = new UserPreferencesManager($this->testUserId);

        // Test with underscores
        $result = $manager->save('visitors_overview', ['columns' => ['date']]);
        $this->assertTrue($result);

        // Test with numbers
        $result = $manager->save('widget_123', ['columns' => ['date']]);
        $this->assertTrue($result);

        // Test camelCase
        $result = $manager->save('visitorsOverview', ['columns' => ['date']]);
        $this->assertTrue($result);
    }

    /**
     * Test data is sanitized before saving.
     */
    public function test_data_sanitization()
    {
        $manager = new UserPreferencesManager($this->testUserId);

        $data = [
            'columns'      => ['<script>alert("xss")</script>', 'visitors'],
            'column_order' => ['date', 'visitors'],
        ];

        $manager->save('visitors_overview', $data);

        $preferences = $manager->get('visitors_overview');

        // Script tags should be removed
        $this->assertNotContains('<script>', $preferences['columns']);
    }

    /**
     * Test widget_order context for storing widget arrangement.
     */
    public function test_widget_order_context()
    {
        $manager = new UserPreferencesManager($this->testUserId);

        $data = [
            'visitors_overview' => ['widget_1', 'widget_2', 'widget_3'],
            'dashboard'         => ['widget_a', 'widget_b', 'widget_c'],
        ];

        $result = $manager->save('widget_order', $data);

        $this->assertTrue($result);

        $preferences = $manager->get('widget_order');

        $this->assertArrayHasKey('visitors_overview', $preferences);
        $this->assertArrayHasKey('dashboard', $preferences);
        $this->assertEquals(['widget_1', 'widget_2', 'widget_3'], $preferences['visitors_overview']);
    }

    /**
     * Test preferences are stored in single meta entry.
     */
    public function test_single_meta_entry()
    {
        $manager = new UserPreferencesManager($this->testUserId);

        $manager->save('context_1', ['columns' => ['a']]);
        $manager->save('context_2', ['columns' => ['b']]);
        $manager->save('context_3', ['columns' => ['c']]);

        // Get raw meta value
        $rawMeta = get_user_meta($this->testUserId, UserPreferencesManager::META_KEY, true);

        $this->assertIsArray($rawMeta);
        $this->assertCount(3, $rawMeta);
    }

    /**
     * Test updated_at timestamp is added.
     */
    public function test_updated_at_timestamp()
    {
        $manager = new UserPreferencesManager($this->testUserId);

        $manager->save('visitors_overview', ['columns' => ['date']]);

        $preferences = $manager->get('visitors_overview');

        $this->assertArrayHasKey('updated_at', $preferences);
        $this->assertNotEmpty($preferences['updated_at']);

        // Verify it's a valid date format
        $timestamp = strtotime($preferences['updated_at']);
        $this->assertNotFalse($timestamp);
    }

    /**
     * Test saving updates existing context without affecting others.
     */
    public function test_save_updates_without_affecting_others()
    {
        $manager = new UserPreferencesManager($this->testUserId);

        $manager->save('context_1', ['columns' => ['a', 'b']]);
        $manager->save('context_2', ['columns' => ['c', 'd']]);

        // Update context_1
        $manager->save('context_1', ['columns' => ['x', 'y', 'z']]);

        $context1 = $manager->get('context_1');
        $context2 = $manager->get('context_2');

        $this->assertEquals(['x', 'y', 'z'], $context1['columns']);
        $this->assertEquals(['c', 'd'], $context2['columns']);
    }
}
