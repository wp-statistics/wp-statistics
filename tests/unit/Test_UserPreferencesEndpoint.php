<?php

namespace WP_Statistics\Tests;

use WP_Statistics\Service\Admin\Dashboard\Endpoints\UserPreferences;
use WP_Statistics\Service\Admin\UserPreferences\UserPreferencesManager;
use WP_Statistics\Utils\Request as RequestUtil;
use WP_UnitTestCase;

/**
 * Test UserPreferences AJAX endpoint.
 */
class Test_UserPreferencesEndpoint extends WP_UnitTestCase
{
    /**
     * Test user ID.
     *
     * @var int
     */
    private $testUserId;

    /**
     * Endpoint instance.
     *
     * @var UserPreferences
     */
    private $endpoint;

    /**
     * Set up test fixtures.
     */
    public function setUp(): void
    {
        parent::setUp();

        // Reset the request data cache before each test
        RequestUtil::resetRequestDataCache();

        // Reset $_POST
        $_POST = [];

        // Create a test user
        $this->testUserId = $this->factory->user->create([
            'role' => 'administrator',
        ]);

        // Set current user
        wp_set_current_user($this->testUserId);

        $this->endpoint = new UserPreferences();
    }

    /**
     * Tear down test fixtures.
     */
    public function tearDown(): void
    {
        // Clean up user meta
        delete_user_meta($this->testUserId, UserPreferencesManager::META_KEY);

        // Reset $_POST
        $_POST = [];

        // Reset the request data cache
        RequestUtil::resetRequestDataCache();

        parent::tearDown();
    }

    /**
     * Test endpoint name.
     */
    public function test_endpoint_name()
    {
        $this->assertEquals('user_preferences', $this->endpoint->getEndpointName());
    }

    /**
     * Test save action via handleQuery.
     */
    public function test_save_action()
    {
        // Simulate POST request
        $_POST = [
            'action_type' => 'save',
            'context'     => 'visitors_overview',
            'data'        => json_encode([
                'columns'      => ['date', 'visitors', 'views'],
                'column_order' => ['date', 'visitors', 'views'],
            ]),
        ];

        $response = $this->endpoint->handleQuery();

        $this->assertTrue($response['success']);
        $this->assertEquals('Preferences saved successfully.', $response['message']);

        // Verify preferences were saved
        $manager = new UserPreferencesManager($this->testUserId);
        $preferences = $manager->get('visitors_overview');

        $this->assertNotNull($preferences);
        $this->assertArrayHasKey('columns', $preferences);
    }

    /**
     * Test reset action via handleQuery.
     */
    public function test_reset_action()
    {
        // First save some preferences
        $manager = new UserPreferencesManager($this->testUserId);
        $manager->save('visitors_overview', ['columns' => ['date']]);

        // Simulate POST request for reset
        $_POST = [
            'action_type' => 'reset',
            'context'     => 'visitors_overview',
        ];

        $response = $this->endpoint->handleQuery();

        $this->assertTrue($response['success']);
        $this->assertEquals('Preferences reset successfully.', $response['message']);

        // Verify preferences were reset
        $preferences = $manager->get('visitors_overview');
        $this->assertNull($preferences);
    }

    /**
     * Test invalid action_type returns error.
     */
    public function test_invalid_action_type()
    {
        $_POST = [
            'action_type' => 'invalid',
            'context'     => 'visitors_overview',
        ];

        $response = $this->endpoint->handleQuery();

        $this->assertFalse($response['success']);
        $this->assertEquals('invalid_action_type', $response['error']['code']);
    }

    /**
     * Test missing context returns error for save.
     */
    public function test_missing_context_for_save()
    {
        $_POST = [
            'action_type' => 'save',
            'data'        => json_encode(['columns' => ['date']]),
        ];

        $response = $this->endpoint->handleQuery();

        $this->assertFalse($response['success']);
        $this->assertEquals('missing_context', $response['error']['code']);
    }

    /**
     * Test missing data returns error for save.
     */
    public function test_missing_data_for_save()
    {
        $_POST = [
            'action_type' => 'save',
            'context'     => 'visitors_overview',
        ];

        $response = $this->endpoint->handleQuery();

        $this->assertFalse($response['success']);
        $this->assertEquals('missing_data', $response['error']['code']);
    }

    /**
     * Test missing context returns error for reset.
     */
    public function test_missing_context_for_reset()
    {
        $_POST = [
            'action_type' => 'reset',
        ];

        $response = $this->endpoint->handleQuery();

        $this->assertFalse($response['success']);
        $this->assertEquals('missing_context', $response['error']['code']);
    }

    /**
     * Test save widget order.
     */
    public function test_save_widget_order()
    {
        $_POST = [
            'action_type' => 'save',
            'context'     => 'widget_order',
            'data'        => json_encode([
                'visitors_overview' => ['widget_1', 'widget_2', 'widget_3'],
                'dashboard'         => ['widget_a', 'widget_b'],
            ]),
        ];

        $response = $this->endpoint->handleQuery();

        $this->assertTrue($response['success']);

        // Verify widget order was saved
        $manager = new UserPreferencesManager($this->testUserId);
        $preferences = $manager->get('widget_order');

        $this->assertArrayHasKey('visitors_overview', $preferences);
        $this->assertEquals(['widget_1', 'widget_2', 'widget_3'], $preferences['visitors_overview']);
    }

    /**
     * Test save unified context with all preference types.
     *
     * Validates the new schema where columns, widgets, and metrics
     * can all be saved to the same context.
     */
    public function test_save_unified_context_schema()
    {
        $_POST = [
            'action_type' => 'save',
            'context'     => 'visitors_overview',
            'data'        => json_encode([
                'columns'      => ['date', 'visitors', 'views'],
                'widgets'      => [
                    'traffic_summary' => true,
                    'top_referrers'   => false,
                ],
                'widget_order' => ['traffic_summary', 'visitor_map'],
                'metrics'      => [
                    'visitors' => true,
                    'views'    => true,
                ],
                'metric_order' => ['visitors', 'views'],
            ]),
        ];

        $response = $this->endpoint->handleQuery();

        $this->assertTrue($response['success']);

        // Verify all data was saved correctly
        $manager = new UserPreferencesManager($this->testUserId);
        $preferences = $manager->get('visitors_overview');

        $this->assertArrayHasKey('columns', $preferences);
        $this->assertArrayHasKey('widgets', $preferences);
        $this->assertArrayHasKey('widget_order', $preferences);
        $this->assertArrayHasKey('metrics', $preferences);
        $this->assertArrayHasKey('metric_order', $preferences);

        // Verify data integrity
        $this->assertEquals(['date', 'visitors', 'views'], $preferences['columns']);
        $this->assertTrue($preferences['widgets']['traffic_summary']);
        $this->assertFalse($preferences['widgets']['top_referrers']);
    }

    /**
     * Test save with route-based context names (using hyphens).
     *
     * Context names can use hyphens (route-style) or underscores.
     */
    public function test_save_with_hyphenated_context()
    {
        $_POST = [
            'action_type' => 'save',
            'context'     => 'visitors-overview',
            'data'        => json_encode(['columns' => ['date', 'visitors']]),
        ];

        $response = $this->endpoint->handleQuery();

        $this->assertTrue($response['success']);

        // Verify saved with hyphenated context
        $manager = new UserPreferencesManager($this->testUserId);
        $preferences = $manager->get('visitors-overview');

        $this->assertNotNull($preferences);
        $this->assertEquals(['date', 'visitors'], $preferences['columns']);
    }

    /**
     * Test no premium context restriction in endpoint.
     *
     * After simplification, any valid context format should be accepted.
     * Premium feature locking is handled by frontend UI, not backend validation.
     */
    public function test_no_premium_context_restriction()
    {
        // These contexts were previously restricted to premium
        $premiumContexts = [
            'visitors_overview' => ['widgets' => ['widget1' => true]],
            'page_insights_overview' => ['metrics' => ['metric1' => true]],
        ];

        foreach ($premiumContexts as $context => $data) {
            // Reset request cache
            RequestUtil::resetRequestDataCache();

            $_POST = [
                'action_type' => 'save',
                'context'     => $context,
                'data'        => json_encode($data),
            ];

            $response = $this->endpoint->handleQuery();

            $this->assertTrue(
                $response['success'],
                "Context '{$context}' should be accepted without premium validation"
            );
        }
    }

    /**
     * Test reset clears all preference types from context.
     */
    public function test_reset_clears_unified_context()
    {
        // First save comprehensive preferences
        $manager = new UserPreferencesManager($this->testUserId);
        $manager->save('visitors_overview', [
            'columns'      => ['date', 'visitors'],
            'widgets'      => ['widget1' => true],
            'metrics'      => ['metric1' => true],
        ]);

        // Verify they exist
        $this->assertTrue($manager->exists('visitors_overview'));

        // Reset via endpoint
        $_POST = [
            'action_type' => 'reset',
            'context'     => 'visitors_overview',
        ];

        $response = $this->endpoint->handleQuery();

        $this->assertTrue($response['success']);

        // Verify completely cleared
        $this->assertNull($manager->get('visitors_overview'));
        $this->assertFalse($manager->exists('visitors_overview'));
    }

    /**
     * Test data as array instead of JSON string.
     *
     * The endpoint should accept both JSON strings and arrays for the data parameter.
     */
    public function test_save_with_array_data()
    {
        $_POST = [
            'action_type' => 'save',
            'context'     => 'test_context',
            'data'        => [
                'columns' => ['date', 'visitors'],
            ],
        ];

        $response = $this->endpoint->handleQuery();

        $this->assertTrue($response['success']);

        $manager = new UserPreferencesManager($this->testUserId);
        $preferences = $manager->get('test_context');

        $this->assertEquals(['date', 'visitors'], $preferences['columns']);
    }
}
