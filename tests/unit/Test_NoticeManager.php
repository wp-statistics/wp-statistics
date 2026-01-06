<?php

use WP_Statistics\Service\Admin\Notice\NoticeManager;
use WP_Statistics\Service\Admin\Notice\NoticeItem;
use WP_Statistics\Service\Admin\Notice\Notices\NoticeInterface;

/**
 * Mock notice generator for testing.
 */
class Mock_Notice_Generator implements NoticeInterface
{
    private bool $shouldRun;
    private array $notices;

    public function __construct(bool $shouldRun = true, array $notices = [])
    {
        $this->shouldRun = $shouldRun;
        $this->notices   = $notices;
    }

    public function shouldRun(): bool
    {
        return $this->shouldRun;
    }

    public function getNotices(): array
    {
        return $this->notices;
    }
}

/**
 * Test case for NoticeManager class.
 *
 * @covers \WP_Statistics\Service\Admin\Notice\NoticeManager
 */
class Test_NoticeManager extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // Reset manager state before each test
        NoticeManager::reset();
        // Clear dismissed option
        delete_option('wp_statistics_dismissed_notices');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        NoticeManager::reset();
        delete_option('wp_statistics_dismissed_notices');
    }

    /**
     * Test adding a notice.
     */
    public function test_add_notice()
    {
        $notice = NoticeItem::info('test_notice', 'Test message');
        NoticeManager::add($notice);

        $active = NoticeManager::getActive();

        $this->assertCount(1, $active);
        $this->assertArrayHasKey('test_notice', $active);
        $this->assertEquals('Test message', $active['test_notice']->message);
    }

    /**
     * Test adding multiple notices.
     */
    public function test_add_many_notices()
    {
        $notices = [
            NoticeItem::info('notice1', 'Message 1'),
            NoticeItem::warning('notice2', 'Message 2'),
        ];

        NoticeManager::addMany($notices);

        $active = NoticeManager::getActive();

        $this->assertCount(2, $active);
        $this->assertArrayHasKey('notice1', $active);
        $this->assertArrayHasKey('notice2', $active);
    }

    /**
     * Test getting a specific notice.
     */
    public function test_get_notice()
    {
        $notice = NoticeItem::error('specific_notice', 'Specific message');
        NoticeManager::add($notice);

        $retrieved = NoticeManager::get('specific_notice');

        $this->assertNotNull($retrieved);
        $this->assertEquals('specific_notice', $retrieved->id);
        $this->assertEquals('Specific message', $retrieved->message);
    }

    /**
     * Test get returns null for non-existent notice.
     */
    public function test_get_returns_null_for_non_existent()
    {
        $this->assertNull(NoticeManager::get('non_existent'));
    }

    /**
     * Test removing a notice.
     */
    public function test_remove_notice()
    {
        NoticeManager::add(NoticeItem::info('to_remove', 'To be removed'));
        NoticeManager::remove('to_remove');

        $this->assertNull(NoticeManager::get('to_remove'));
    }

    /**
     * Test dismissing a notice.
     */
    public function test_dismiss_notice()
    {
        NoticeManager::add(NoticeItem::info('to_dismiss', 'To be dismissed'));
        NoticeManager::dismiss('to_dismiss');

        $this->assertTrue(NoticeManager::isDismissed('to_dismiss'));

        // Verify it's no longer in active notices
        $active = NoticeManager::getActive();
        $this->assertArrayNotHasKey('to_dismiss', $active);
    }

    /**
     * Test dismissed notice is not added again.
     */
    public function test_dismissed_notice_not_added()
    {
        // First dismiss the notice
        NoticeManager::dismiss('previously_dismissed');

        // Try to add it
        NoticeManager::add(NoticeItem::info('previously_dismissed', 'Should not appear'));

        $active = NoticeManager::getActive();
        $this->assertArrayNotHasKey('previously_dismissed', $active);
    }

    /**
     * Test isDismissed returns correct value.
     */
    public function test_is_dismissed()
    {
        $this->assertFalse(NoticeManager::isDismissed('not_dismissed'));

        NoticeManager::dismiss('test_id');

        $this->assertTrue(NoticeManager::isDismissed('test_id'));
    }

    /**
     * Test getDismissedIds returns array.
     */
    public function test_get_dismissed_ids()
    {
        NoticeManager::dismiss('dismissed1');
        NoticeManager::dismiss('dismissed2');

        $dismissed = NoticeManager::getDismissedIds();

        $this->assertIsArray($dismissed);
        $this->assertContains('dismissed1', $dismissed);
        $this->assertContains('dismissed2', $dismissed);
    }

    /**
     * Test undismiss restores a notice.
     */
    public function test_undismiss_notice()
    {
        NoticeManager::dismiss('to_undismiss');
        $this->assertTrue(NoticeManager::isDismissed('to_undismiss'));

        NoticeManager::undismiss('to_undismiss');
        $this->assertFalse(NoticeManager::isDismissed('to_undismiss'));
    }

    /**
     * Test clearDismissed removes all dismissed.
     */
    public function test_clear_dismissed()
    {
        NoticeManager::dismiss('dismiss1');
        NoticeManager::dismiss('dismiss2');

        NoticeManager::clearDismissed();

        $dismissed = NoticeManager::getDismissedIds();
        $this->assertEmpty($dismissed);
    }

    /**
     * Test registerGenerator adds a notice generator.
     */
    public function test_register_generator()
    {
        $generator = new Mock_Notice_Generator(true, [
            NoticeItem::info('generated_notice', 'Generated message'),
        ]);

        NoticeManager::registerGenerator($generator);

        $active = NoticeManager::getActive();

        $this->assertArrayHasKey('generated_notice', $active);
    }

    /**
     * Test generator that should not run is skipped.
     */
    public function test_generator_should_not_run()
    {
        $generator = new Mock_Notice_Generator(false, [
            NoticeItem::info('should_not_appear', 'Should not appear'),
        ]);

        NoticeManager::registerGenerator($generator);

        $active = NoticeManager::getActive();

        $this->assertArrayNotHasKey('should_not_appear', $active);
    }

    /**
     * Test notices are sorted by priority.
     */
    public function test_notices_sorted_by_priority()
    {
        NoticeManager::add(new NoticeItem([
            'id'       => 'low_priority',
            'message'  => 'Low priority',
            'priority' => 20,
        ]));
        NoticeManager::add(new NoticeItem([
            'id'       => 'high_priority',
            'message'  => 'High priority',
            'priority' => 1,
        ]));
        NoticeManager::add(new NoticeItem([
            'id'       => 'medium_priority',
            'message'  => 'Medium priority',
            'priority' => 10,
        ]));

        $active = NoticeManager::getActive();
        $ids    = array_keys($active);

        $this->assertEquals('high_priority', $ids[0]);
        $this->assertEquals('medium_priority', $ids[1]);
        $this->assertEquals('low_priority', $ids[2]);
    }

    /**
     * Test getDataForReact returns correct format.
     */
    public function test_get_data_for_react()
    {
        NoticeManager::add(new NoticeItem([
            'id'          => 'react_notice',
            'message'     => 'React message',
            'type'        => 'warning',
            'actionUrl'   => 'https://example.com',
            'actionLabel' => 'Click',
            'helpUrl'     => 'https://example.com/help',
            'dismissible' => true,
            'priority'    => 5,
        ]));

        $data = NoticeManager::getDataForReact();

        $this->assertIsArray($data);
        $this->assertCount(1, $data);

        $notice = $data[0];
        $this->assertEquals('react_notice', $notice['id']);
        $this->assertEquals('React message', $notice['message']);
        $this->assertEquals('warning', $notice['type']);
        $this->assertEquals('https://example.com', $notice['actionUrl']);
        $this->assertEquals('Click', $notice['actionLabel']);
        $this->assertEquals('https://example.com/help', $notice['helpUrl']);
        $this->assertTrue($notice['dismissible']);
        $this->assertEquals(5, $notice['priority']);
    }

    /**
     * Test reset clears all state.
     */
    public function test_reset_clears_state()
    {
        NoticeManager::add(NoticeItem::info('to_clear', 'To be cleared'));
        NoticeManager::dismiss('dismissed_id');

        NoticeManager::reset();

        $this->assertNull(NoticeManager::get('to_clear'));
        $this->assertEmpty(NoticeManager::getActive());
    }

    /**
     * Test dismissed notices persist in database.
     */
    public function test_dismissed_persists_in_database()
    {
        NoticeManager::dismiss('persistent_dismiss');

        // Reset the manager (clears cache)
        NoticeManager::reset();

        // Should still be dismissed because it's in the database
        $this->assertTrue(NoticeManager::isDismissed('persistent_dismiss'));
    }

    /**
     * Test duplicate notice is not added twice.
     */
    public function test_duplicate_notice_not_added()
    {
        NoticeManager::add(NoticeItem::info('duplicate', 'First'));
        NoticeManager::add(NoticeItem::info('duplicate', 'Second'));

        $active = NoticeManager::getActive();

        $this->assertCount(1, $active);
        // The second one should replace the first
        $this->assertEquals('Second', $active['duplicate']->message);
    }

    /**
     * Test getDataForReact returns indexed array.
     */
    public function test_get_data_for_react_returns_indexed_array()
    {
        NoticeManager::add(NoticeItem::info('notice1', 'Message 1'));
        NoticeManager::add(NoticeItem::info('notice2', 'Message 2'));

        $data = NoticeManager::getDataForReact();

        // Should be numerically indexed (0, 1) not keyed by notice ID
        $this->assertArrayHasKey(0, $data);
        $this->assertArrayHasKey(1, $data);
    }
}
