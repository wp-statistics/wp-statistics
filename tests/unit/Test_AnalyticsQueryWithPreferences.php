<?php

namespace WP_Statistics\Tests;

use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\Admin\UserPreferences\UserPreferencesManager;
use WP_UnitTestCase;

/**
 * Test AnalyticsQueryHandler with user preferences integration.
 */
class Test_AnalyticsQueryWithPreferences extends WP_UnitTestCase
{
    /**
     * Test user ID.
     *
     * @var int
     */
    private $testUserId;

    /**
     * Analytics query handler.
     *
     * @var AnalyticsQueryHandler
     */
    private $handler;

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

        // Disable caching for tests
        $this->handler = new AnalyticsQueryHandler(false);
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
     * Test response includes preferences when context is provided.
     */
    public function test_response_includes_preferences_with_context()
    {
        // Save some preferences - columns must match sources + group_by
        $manager = new UserPreferencesManager($this->testUserId);
        $manager->save('visitors_overview', [
            'columns'      => ['date', 'visitors'],
            'column_order' => ['date', 'visitors'],
        ]);

        // Execute query with context
        $response = $this->handler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['date'],
            'date_from' => date('Y-m-d', strtotime('-7 days')),
            'date_to'   => date('Y-m-d'),
            'context'   => 'visitors_overview',
        ]);

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('meta', $response);
        $this->assertArrayHasKey('preferences', $response['meta']);
        $this->assertNotNull($response['meta']['preferences']);
        $this->assertArrayHasKey('columns', $response['meta']['preferences']);
        $this->assertEquals(['date', 'visitors'], $response['meta']['preferences']['columns']);
    }

    /**
     * Test response has null preferences when context not provided.
     */
    public function test_response_has_null_preferences_without_context()
    {
        $response = $this->handler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['date'],
            'date_from' => date('Y-m-d', strtotime('-7 days')),
            'date_to'   => date('Y-m-d'),
        ]);

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('meta', $response);
        $this->assertArrayHasKey('preferences', $response['meta']);
        $this->assertNull($response['meta']['preferences']);
    }

    /**
     * Test response has null preferences when no preferences saved for context.
     */
    public function test_response_has_null_preferences_when_not_saved()
    {
        $response = $this->handler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['date'],
            'date_from' => date('Y-m-d', strtotime('-7 days')),
            'date_to'   => date('Y-m-d'),
            'context'   => 'non_existent_context',
        ]);

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('meta', $response);
        $this->assertArrayHasKey('preferences', $response['meta']);
        $this->assertNull($response['meta']['preferences']);
    }

    /**
     * Test preferences are included with flat format.
     */
    public function test_preferences_with_flat_format()
    {
        $manager = new UserPreferencesManager($this->testUserId);
        $manager->save('top_countries', ['columns' => ['country_name', 'visitors']]);

        $response = $this->handler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['country'],
            'date_from' => date('Y-m-d', strtotime('-7 days')),
            'date_to'   => date('Y-m-d'),
            'format'    => 'flat',
            'context'   => 'top_countries',
        ]);

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('meta', $response);
        $this->assertArrayHasKey('preferences', $response['meta']);
        $this->assertEquals(['country_name', 'visitors'], $response['meta']['preferences']['columns']);
    }

    /**
     * Test preferences are included with chart format.
     */
    public function test_preferences_with_chart_format()
    {
        $manager = new UserPreferencesManager($this->testUserId);
        $manager->save('traffic_trends', ['columns' => ['date', 'visitors']]);

        $response = $this->handler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['date'],
            'date_from' => date('Y-m-d', strtotime('-7 days')),
            'date_to'   => date('Y-m-d'),
            'format'    => 'chart',
            'context'   => 'traffic_trends',
        ]);

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('meta', $response);
        $this->assertArrayHasKey('preferences', $response['meta']);
        $this->assertEquals(['date', 'visitors'], $response['meta']['preferences']['columns']);
    }

    /**
     * Test preferences include updated_at timestamp.
     */
    public function test_preferences_include_timestamp()
    {
        $manager = new UserPreferencesManager($this->testUserId);
        $manager->save('visitors_overview', ['columns' => ['date']]);

        $response = $this->handler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['date'],
            'date_from' => date('Y-m-d', strtotime('-7 days')),
            'date_to'   => date('Y-m-d'),
            'context'   => 'visitors_overview',
        ]);

        $this->assertArrayHasKey('updated_at', $response['meta']['preferences']);
        $this->assertNotEmpty($response['meta']['preferences']['updated_at']);
    }
}
