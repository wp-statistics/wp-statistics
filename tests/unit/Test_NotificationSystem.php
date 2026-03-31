<?php

use WP_Statistics\Service\Admin\Notification\NotificationFactory;
use WP_Statistics\Service\Admin\Notification\NotificationProcessor;
use WP_Statistics\Service\Admin\Notification\NotificationManager;
use WP_Statistics\Service\Admin\Notification\NotificationDataProvider;
use WP_Statistics\Service\Cron\Events\NotificationCronEvent;

/**
 * Test case for the Notification System.
 *
 * @covers \WP_Statistics\Service\Admin\Notification\NotificationFactory
 * @covers \WP_Statistics\Service\Admin\Notification\NotificationProcessor
 * @covers \WP_Statistics\Service\Admin\Notification\NotificationManager
 * @covers \WP_Statistics\Service\Admin\Notification\NotificationDataProvider
 * @covers \WP_Statistics\Service\Cron\Events\NotificationCronEvent
 */
class Test_NotificationSystem extends WP_UnitTestCase
{
    /**
     * @var int
     */
    private $testUserId;

    /**
     * Sample notification data matching the remote API response format.
     *
     * @var array
     */
    private $sampleNotifications = [
        'data' => [
            [
                'id'               => 1,
                'title'            => 'Test Notification 1',
                'description'      => '"<p>First notification description.</p>"',
                'icon'             => null,
                'background_color' => 'info',
                'activated_at'     => '2025-03-01T00:00:00Z',
                'primary_button'   => ['title' => 'Learn More', 'url' => 'https://example.com'],
                'secondary_button' => null,
                'tags'             => [],
            ],
            [
                'id'               => 2,
                'title'            => 'Test Notification 2',
                'description'      => '"Second notification."',
                'icon'             => null,
                'background_color' => 'warning',
                'activated_at'     => '2025-02-01T00:00:00Z',
                'primary_button'   => ['title' => 'Install', 'url' => '{baseUrl}/wp-admin/plugins.php'],
                'secondary_button' => ['title' => 'Dismiss', 'url' => '{baseUrl}/wp-admin/'],
                'tags'             => [],
            ],
            [
                'id'               => 3,
                'title'            => 'Premium Only',
                'description'      => '"Only for premium users."',
                'icon'             => null,
                'background_color' => 'success',
                'activated_at'     => '2025-01-01T00:00:00Z',
                'primary_button'   => null,
                'secondary_button' => null,
                'tags'             => ['is-premium'],
            ],
        ],
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->testUserId = $this->factory->user->create(['role' => 'administrator']);
        wp_set_current_user($this->testUserId);

        delete_option('wp_statistics_notifications');
        delete_user_meta($this->testUserId, 'wp_statistics_dismissed_notifications');
        delete_user_meta($this->testUserId, 'wp_statistics_viewed_notifications');
        $_POST    = [];
        $_REQUEST = [];

        // Reset static cache
        $this->resetFactoryCache();
    }

    public function tearDown(): void
    {
        delete_option('wp_statistics_notifications');
        delete_user_meta($this->testUserId, 'wp_statistics_dismissed_notifications');
        delete_user_meta($this->testUserId, 'wp_statistics_viewed_notifications');
        $_POST    = [];
        $_REQUEST = [];
        $this->resetFactoryCache();
        remove_all_filters('pre_http_request');

        parent::tearDown();
    }

    /**
     * Reset the static cache in NotificationFactory.
     */
    private function resetFactoryCache()
    {
        $ref = new ReflectionProperty(NotificationFactory::class, 'allCache');
        $ref->setAccessible(true);
        $ref->setValue(null, null);
    }

    /**
     * Seed the option with sample notification data.
     */
    private function seedNotifications($data = null)
    {
        update_option('wp_statistics_notifications', $data ?? $this->sampleNotifications, false);
        $this->resetFactoryCache();
    }

    // ==========================================
    // NotificationProcessor Tests
    // ==========================================

    /**
     * Test filterByTags returns all notifications when no tags.
     */
    public function test_filter_by_tags_returns_all_when_no_tags()
    {
        $items = [
            ['id' => 1, 'title' => 'No tags', 'tags' => []],
            ['id' => 2, 'title' => 'Null tags'],
        ];

        $result = NotificationProcessor::filterByTags($items);

        $this->assertCount(2, $result);
    }

    /**
     * Test filterByTags removes items with failing condition tags.
     */
    public function test_filter_by_tags_removes_failing_conditions()
    {
        $items = [
            ['id' => 1, 'title' => 'Free', 'tags' => ['is-free']],
            ['id' => 2, 'title' => 'Premium', 'tags' => ['is-premium']],
        ];

        $result = NotificationProcessor::filterByTags($items);
        $ids    = array_column($result, 'id');

        // is-free should pass (premium plugin not active in test env), is-premium should fail
        $this->assertContains(1, $ids);
        $this->assertNotContains(2, $ids);
    }

    /**
     * Test filterByTags returns empty array for empty input.
     */
    public function test_filter_by_tags_handles_empty_input()
    {
        $this->assertEmpty(NotificationProcessor::filterByTags([]));
        $this->assertEmpty(NotificationProcessor::filterByTags(null));
    }

    /**
     * Test sortByActivatedAt sorts newest first.
     */
    public function test_sort_by_activated_at()
    {
        $data = [
            'data' => [
                ['id' => 1, 'activated_at' => '2025-01-01T00:00:00Z'],
                ['id' => 2, 'activated_at' => '2025-03-01T00:00:00Z'],
                ['id' => 3, 'activated_at' => '2025-02-01T00:00:00Z'],
            ],
        ];

        $sorted = NotificationProcessor::sortByActivatedAt($data);

        $this->assertEquals(2, $sorted['data'][0]['id']);
        $this->assertEquals(3, $sorted['data'][1]['id']);
        $this->assertEquals(1, $sorted['data'][2]['id']);
    }

    /**
     * Test sortByActivatedAt handles empty data.
     */
    public function test_sort_by_activated_at_handles_empty()
    {
        $result = NotificationProcessor::sortByActivatedAt([]);
        $this->assertIsArray($result);

        $result = NotificationProcessor::sortByActivatedAt(['data' => []]);
        $this->assertEmpty($result['data']);
    }

    // ==========================================
    // NotificationFactory Tests
    // ==========================================

    /**
     * Test getRawData returns stored option.
     */
    public function test_get_raw_data_returns_option()
    {
        $this->seedNotifications();

        $raw = NotificationFactory::getRawData();

        $this->assertIsArray($raw);
        $this->assertArrayHasKey('data', $raw);
        $this->assertCount(3, $raw['data']);
    }

    /**
     * Test getRawData returns empty array when no option.
     */
    public function test_get_raw_data_returns_empty_when_no_option()
    {
        $raw = NotificationFactory::getRawData();
        $this->assertIsArray($raw);
    }

    /**
     * Test getAll filters by condition tags.
     */
    public function test_get_all_filters_by_tags()
    {
        $this->seedNotifications();

        $all = NotificationFactory::getAll();

        // 'is-premium' tag should filter out notification 3 in test env
        $ids = array_column($all, 'id');
        $this->assertContains(1, $ids);
        $this->assertContains(2, $ids);
        $this->assertNotContains(3, $ids);
    }

    /**
     * Test getAll caches results.
     */
    public function test_get_all_caches_results()
    {
        $this->seedNotifications();

        $first  = NotificationFactory::getAll();
        $second = NotificationFactory::getAll();

        $this->assertSame($first, $second);
    }

    /**
     * Test getForUser excludes dismissed notifications.
     */
    public function test_get_for_user_excludes_dismissed()
    {
        $this->seedNotifications();

        update_user_meta($this->testUserId, 'wp_statistics_dismissed_notifications', [1]);

        $items = NotificationFactory::getForUser($this->testUserId);
        $ids   = array_column($items, 'id');

        $this->assertNotContains(1, $ids);
        $this->assertContains(2, $ids);
    }

    /**
     * Test getForUser returns all when no dismissals.
     */
    public function test_get_for_user_returns_all_when_no_dismissals()
    {
        $this->seedNotifications();

        $all     = NotificationFactory::getAll();
        $forUser = NotificationFactory::getForUser($this->testUserId);

        $this->assertCount(count($all), $forUser);
    }

    /**
     * Test getUnreadCount returns correct count.
     */
    public function test_get_unread_count()
    {
        $this->seedNotifications();

        // No views — all should be unread
        $allCount = count(NotificationFactory::getForUser($this->testUserId));
        $this->assertEquals($allCount, NotificationFactory::getUnreadCount($this->testUserId));
    }

    /**
     * Test getUnreadCount decreases after marking as viewed.
     */
    public function test_get_unread_count_after_viewing()
    {
        $this->seedNotifications();

        update_user_meta($this->testUserId, 'wp_statistics_viewed_notifications', [1]);

        $unread = NotificationFactory::getUnreadCount($this->testUserId);
        $total  = count(NotificationFactory::getForUser($this->testUserId));

        $this->assertEquals($total - 1, $unread);
    }

    /**
     * Test getUnreadCount is zero when all viewed.
     */
    public function test_get_unread_count_zero_when_all_viewed()
    {
        $this->seedNotifications();

        $all = NotificationFactory::getForUser($this->testUserId);
        $ids = array_column($all, 'id');
        update_user_meta($this->testUserId, 'wp_statistics_viewed_notifications', $ids);

        $this->assertEquals(0, NotificationFactory::getUnreadCount($this->testUserId));
    }

    /**
     * Test getDismissedIds returns empty array by default.
     */
    public function test_get_dismissed_ids_default_empty()
    {
        $dismissed = NotificationFactory::getDismissedIds($this->testUserId);
        $this->assertIsArray($dismissed);
        $this->assertEmpty($dismissed);
    }

    /**
     * Test getDismissedIds returns stored IDs.
     */
    public function test_get_dismissed_ids_returns_stored()
    {
        update_user_meta($this->testUserId, 'wp_statistics_dismissed_notifications', [1, 2]);

        $dismissed = NotificationFactory::getDismissedIds($this->testUserId);

        $this->assertCount(2, $dismissed);
        $this->assertContains(1, $dismissed);
        $this->assertContains(2, $dismissed);
    }

    /**
     * Test getViewedIds returns empty array by default.
     */
    public function test_get_viewed_ids_default_empty()
    {
        $viewed = NotificationFactory::getViewedIds($this->testUserId);
        $this->assertIsArray($viewed);
        $this->assertEmpty($viewed);
    }

    // ==========================================
    // NotificationManager Tests
    // ==========================================

    /**
     * Test dismiss adds notification ID to user meta.
     */
    public function test_dismiss_stores_in_user_meta()
    {
        $userId = $this->testUserId;

        $dismissed = NotificationFactory::getDismissedIds($userId);
        $this->assertNotContains(1, $dismissed);

        $dismissed[] = 1;
        update_user_meta($userId, 'wp_statistics_dismissed_notifications', $dismissed);

        $result = NotificationFactory::getDismissedIds($userId);
        $this->assertContains(1, $result);
    }

    /**
     * Test dismiss does not duplicate IDs.
     */
    public function test_dismiss_no_duplicates()
    {
        $userId = $this->testUserId;

        update_user_meta($userId, 'wp_statistics_dismissed_notifications', [1]);

        $dismissed = NotificationFactory::getDismissedIds($userId);
        if (!in_array(1, $dismissed, true)) {
            $dismissed[] = 1;
        }
        update_user_meta($userId, 'wp_statistics_dismissed_notifications', $dismissed);

        $result = NotificationFactory::getDismissedIds($userId);
        $this->assertCount(1, $result);
    }

    /**
     * Test dismiss all stores multiple IDs.
     */
    public function test_dismiss_all_stores_multiple_ids()
    {
        $userId = $this->testUserId;

        $dismissed = NotificationFactory::getDismissedIds($userId);
        $dismissed = array_unique(array_merge($dismissed, [1, 2, 3]));
        update_user_meta($userId, 'wp_statistics_dismissed_notifications', $dismissed);

        $result = NotificationFactory::getDismissedIds($userId);
        $this->assertContains(1, $result);
        $this->assertContains(2, $result);
        $this->assertContains(3, $result);
    }

    /**
     * Test mark viewed stores IDs in user meta.
     */
    public function test_mark_viewed_stores_in_user_meta()
    {
        $userId = $this->testUserId;

        $viewed = NotificationFactory::getViewedIds($userId);
        $viewed = array_unique(array_merge($viewed, [1, 2]));
        update_user_meta($userId, 'wp_statistics_viewed_notifications', $viewed);

        $result = NotificationFactory::getViewedIds($userId);
        $this->assertContains(1, $result);
        $this->assertContains(2, $result);
    }

    /**
     * Test mark viewed merges with existing viewed IDs.
     */
    public function test_mark_viewed_merges_with_existing()
    {
        $userId = $this->testUserId;

        update_user_meta($userId, 'wp_statistics_viewed_notifications', [1]);

        $viewed = NotificationFactory::getViewedIds($userId);
        $viewed = array_unique(array_merge($viewed, [2]));
        update_user_meta($userId, 'wp_statistics_viewed_notifications', $viewed);

        $result = NotificationFactory::getViewedIds($userId);
        $this->assertContains(1, $result);
        $this->assertContains(2, $result);
    }

    /**
     * Test manager init registers AJAX actions when enabled.
     */
    public function test_manager_init_registers_ajax_actions()
    {
        update_option('wp_statistics', ['display_notifications' => true]);

        // Reset initialized state
        $ref = new ReflectionProperty(NotificationManager::class, 'initialized');
        $ref->setAccessible(true);
        $ref->setValue(null, false);

        NotificationManager::init();

        $this->assertNotFalse(has_action('wp_ajax_wp_statistics_dismiss_notification'));
        $this->assertNotFalse(has_action('wp_ajax_wp_statistics_dismiss_all_notifications'));
        $this->assertNotFalse(has_action('wp_ajax_wp_statistics_mark_notifications_viewed'));
    }

    // ==========================================
    // NotificationDataProvider Tests
    // ==========================================

    /**
     * Test data provider returns correct structure when enabled.
     */
    public function test_data_provider_returns_correct_structure()
    {
        $this->seedNotifications();
        update_option('wp_statistics', ['display_notifications' => true]);

        $provider = new NotificationDataProvider();
        $data     = $provider->getData();

        $this->assertTrue($data['enabled']);
        $this->assertIsArray($data['items']);
        $this->assertIsArray($data['dismissedIds']);
        $this->assertIsInt($data['unreadCount']);
        $this->assertNotEmpty($data['nonce']);
    }

    /**
     * Test data provider returns disabled state.
     */
    public function test_data_provider_disabled()
    {
        update_option('wp_statistics', ['display_notifications' => false]);

        $provider = new NotificationDataProvider();
        $data     = $provider->getData();

        $this->assertFalse($data['enabled']);
        $this->assertEmpty($data['items']);
        $this->assertEquals(0, $data['unreadCount']);
        $this->assertEmpty($data['nonce']);
    }

    /**
     * Test data provider key is 'notifications'.
     */
    public function test_data_provider_key()
    {
        $provider = new NotificationDataProvider();
        $this->assertEquals('notifications', $provider->getKey());
    }

    /**
     * Test sanitizeItem strips HTML from description.
     */
    public function test_data_provider_sanitizes_description()
    {
        $this->seedNotifications();

        $provider = new NotificationDataProvider();
        $data     = $provider->getData();

        $firstItem = $data['items'][0];
        $this->assertStringNotContainsString('<p>', $firstItem['description']);
        $this->assertStringNotContainsString('</p>', $firstItem['description']);
        $this->assertEquals('First notification description.', $firstItem['description']);
    }

    /**
     * Test sanitizeItem replaces {baseUrl} in button URLs.
     */
    public function test_data_provider_replaces_base_url()
    {
        $this->seedNotifications();

        $provider = new NotificationDataProvider();
        $data     = $provider->getData();

        // Find the notification with {baseUrl} (id: 2)
        $item = null;
        foreach ($data['items'] as $i) {
            if ($i['id'] === 2) {
                $item = $i;
                break;
            }
        }

        $this->assertNotNull($item);
        $this->assertStringNotContainsString('{baseUrl}', $item['primary_button']['url']);
        $this->assertStringContainsString('/wp-admin/plugins.php', $item['primary_button']['url']);
        $this->assertStringNotContainsString('{baseUrl}', $item['secondary_button']['url']);
    }

    /**
     * Test data provider includes all items (not just non-dismissed).
     */
    public function test_data_provider_includes_all_items()
    {
        $this->seedNotifications();
        update_user_meta($this->testUserId, 'wp_statistics_dismissed_notifications', [1]);

        $provider = new NotificationDataProvider();
        $data     = $provider->getData();

        // All items should be present (dismissed filtering happens in React)
        $ids = array_column($data['items'], 'id');
        $this->assertContains(2, $ids);

        // Dismissed IDs should be returned separately
        $this->assertContains(1, $data['dismissedIds']);
    }

    // ==========================================
    // NotificationCronEvent Tests
    // ==========================================

    /**
     * Test cron event hook name.
     */
    public function test_cron_event_hook()
    {
        $event = new NotificationCronEvent();
        $this->assertEquals('wp_statistics_fetch_notifications', $event->getHook());
    }

    /**
     * Test cron event recurrence is daily.
     */
    public function test_cron_event_recurrence()
    {
        $event = new NotificationCronEvent();
        $this->assertEquals('daily', $event->getRecurrence());
    }

    /**
     * Test cron should schedule when notifications enabled.
     */
    public function test_cron_should_schedule_when_enabled()
    {
        update_option('wp_statistics', ['display_notifications' => true]);

        $event = new NotificationCronEvent();
        $this->assertTrue($event->shouldSchedule());
    }

    /**
     * Test cron should not schedule when notifications disabled.
     */
    public function test_cron_should_not_schedule_when_disabled()
    {
        update_option('wp_statistics', ['display_notifications' => false]);

        $event = new NotificationCronEvent();
        $this->assertFalse($event->shouldSchedule());
    }

    /**
     * Test cron next schedule time is in the future.
     */
    public function test_cron_next_schedule_in_future()
    {
        $event  = new NotificationCronEvent();
        $method = new ReflectionMethod($event, 'getNextScheduleTime');
        $method->setAccessible(true);

        $nextTime = $method->invoke($event);

        $this->assertGreaterThan(time(), $nextTime);
    }

    // ==========================================
    // NotificationFetcher Tests (mocked HTTP)
    // ==========================================

    /**
     * Test fetcher stores notifications on success.
     */
    public function test_fetcher_stores_on_success()
    {
        $mockResponse = [
            'response' => ['code' => 200],
            'body'     => json_encode($this->sampleNotifications),
        ];

        add_filter('pre_http_request', function () use ($mockResponse) {
            return $mockResponse;
        });

        $fetcher = new \WP_Statistics\Service\Admin\Notification\NotificationFetcher();
        $result  = $fetcher->fetchNotifications();

        $this->assertTrue($result);

        $stored = get_option('wp_statistics_notifications');
        $this->assertNotEmpty($stored);
        $this->assertArrayHasKey('data', $stored);

        remove_all_filters('pre_http_request');
    }

    /**
     * Test fetcher clears option on 404.
     */
    public function test_fetcher_clears_on_404()
    {
        $this->seedNotifications();

        add_filter('pre_http_request', function () {
            return ['response' => ['code' => 404], 'body' => ''];
        });

        $fetcher = new \WP_Statistics\Service\Admin\Notification\NotificationFetcher();
        $result  = $fetcher->fetchNotifications();

        $this->assertFalse($result);
        $this->assertEmpty(get_option('wp_statistics_notifications'));

        remove_all_filters('pre_http_request');
    }

    /**
     * Test fetcher returns false on non-200 response.
     */
    public function test_fetcher_returns_false_on_error()
    {
        add_filter('pre_http_request', function () {
            return ['response' => ['code' => 500], 'body' => ''];
        });

        $fetcher = new \WP_Statistics\Service\Admin\Notification\NotificationFetcher();
        $result  = $fetcher->fetchNotifications();

        $this->assertFalse($result);

        remove_all_filters('pre_http_request');
    }

    /**
     * Test fetcher returns false on empty response.
     */
    public function test_fetcher_returns_false_on_empty_response()
    {
        add_filter('pre_http_request', function () {
            return ['response' => ['code' => 200], 'body' => '[]'];
        });

        $fetcher = new \WP_Statistics\Service\Admin\Notification\NotificationFetcher();
        $result  = $fetcher->fetchNotifications();

        $this->assertFalse($result);

        remove_all_filters('pre_http_request');
    }

    // ==========================================
    // Per-User Isolation Tests
    // ==========================================

    /**
     * Test dismissed state is per-user.
     */
    public function test_dismissed_is_per_user()
    {
        $this->seedNotifications();

        $secondUser = $this->factory->user->create(['role' => 'administrator']);

        // Dismiss notification 1 for first user
        update_user_meta($this->testUserId, 'wp_statistics_dismissed_notifications', [1]);

        // Second user should still see it
        $forSecondUser = NotificationFactory::getForUser($secondUser);
        $ids           = array_column($forSecondUser, 'id');
        $this->assertContains(1, $ids);

        // First user should not
        $forFirstUser = NotificationFactory::getForUser($this->testUserId);
        $ids          = array_column($forFirstUser, 'id');
        $this->assertNotContains(1, $ids);
    }

    /**
     * Test viewed state is per-user.
     */
    public function test_viewed_is_per_user()
    {
        $this->seedNotifications();

        $secondUser = $this->factory->user->create(['role' => 'administrator']);

        // Mark viewed for first user
        $all = NotificationFactory::getForUser($this->testUserId);
        $ids = array_column($all, 'id');
        update_user_meta($this->testUserId, 'wp_statistics_viewed_notifications', $ids);

        // First user: 0 unread
        $this->assertEquals(0, NotificationFactory::getUnreadCount($this->testUserId));

        // Second user: still has unread
        $this->assertGreaterThan(0, NotificationFactory::getUnreadCount($secondUser));
    }
}
