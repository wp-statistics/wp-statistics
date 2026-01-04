<?php

namespace WP_Statistics\Tests;

use WP_Statistics\Service\Admin\ReactApp\Controllers\Root\Endpoints\UserPreferences;
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
}
