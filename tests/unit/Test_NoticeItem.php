<?php

use WP_Statistics\Service\Admin\Notice\NoticeItem;

/**
 * Test case for NoticeItem class.
 *
 * @covers \WP_Statistics\Service\Admin\Notice\NoticeItem
 */
class Test_NoticeItem extends WP_UnitTestCase
{
    /**
     * Test creating a notice with constructor.
     */
    public function test_constructor_with_array()
    {
        $notice = new NoticeItem([
            'id'          => 'test_notice',
            'message'     => 'Test message',
            'type'        => 'warning',
            'dismissible' => true,
            'actionUrl'   => 'https://example.com/action',
            'actionLabel' => 'Click Me',
            'helpUrl'     => 'https://example.com/help',
            'priority'    => 5,
        ]);

        $this->assertEquals('test_notice', $notice->id);
        $this->assertEquals('Test message', $notice->message);
        $this->assertEquals('warning', $notice->type);
        $this->assertTrue($notice->dismissible);
        $this->assertEquals('https://example.com/action', $notice->actionUrl);
        $this->assertEquals('Click Me', $notice->actionLabel);
        $this->assertEquals('https://example.com/help', $notice->helpUrl);
        $this->assertEquals(5, $notice->priority);
    }

    /**
     * Test default values.
     */
    public function test_default_values()
    {
        $notice = new NoticeItem([
            'id'      => 'test',
            'message' => 'Test',
        ]);

        $this->assertEquals('info', $notice->type);
        $this->assertTrue($notice->dismissible);
        $this->assertNull($notice->actionUrl);
        $this->assertNull($notice->actionLabel);
        $this->assertNull($notice->helpUrl);
        $this->assertEquals(10, $notice->priority);
    }

    /**
     * Test info factory method.
     */
    public function test_info_factory_method()
    {
        $notice = NoticeItem::info(
            'info_notice',
            'Info message',
            'https://example.com/action',
            'View'
        );

        $this->assertEquals('info_notice', $notice->id);
        $this->assertEquals('Info message', $notice->message);
        $this->assertEquals('info', $notice->type);
        $this->assertEquals('https://example.com/action', $notice->actionUrl);
        $this->assertEquals('View', $notice->actionLabel);
    }

    /**
     * Test warning factory method.
     */
    public function test_warning_factory_method()
    {
        $notice = NoticeItem::warning(
            'warning_notice',
            'Warning message',
            'https://example.com/fix',
            'Fix Now'
        );

        $this->assertEquals('warning_notice', $notice->id);
        $this->assertEquals('Warning message', $notice->message);
        $this->assertEquals('warning', $notice->type);
        $this->assertEquals('https://example.com/fix', $notice->actionUrl);
        $this->assertEquals('Fix Now', $notice->actionLabel);
    }

    /**
     * Test error factory method.
     */
    public function test_error_factory_method()
    {
        $notice = NoticeItem::error(
            'error_notice',
            'Error message',
            'https://example.com/resolve',
            'Resolve'
        );

        $this->assertEquals('error_notice', $notice->id);
        $this->assertEquals('Error message', $notice->message);
        $this->assertEquals('error', $notice->type);
    }

    /**
     * Test success factory method.
     */
    public function test_success_factory_method()
    {
        $notice = NoticeItem::success(
            'success_notice',
            'Success message',
            'https://example.com/continue',
            'Continue'
        );

        $this->assertEquals('success_notice', $notice->id);
        $this->assertEquals('Success message', $notice->message);
        $this->assertEquals('success', $notice->type);
    }

    /**
     * Test factory methods without optional parameters.
     */
    public function test_factory_methods_without_optional_params()
    {
        $notice = NoticeItem::info('test', 'Message');

        $this->assertNull($notice->actionUrl);
        $this->assertNull($notice->actionLabel);
    }

    /**
     * Test toArray method.
     */
    public function test_to_array_returns_all_properties()
    {
        $notice = new NoticeItem([
            'id'          => 'test_notice',
            'message'     => 'Test message',
            'type'        => 'error',
            'dismissible' => false,
            'actionUrl'   => 'https://example.com',
            'actionLabel' => 'Click',
            'helpUrl'     => 'https://example.com/help',
            'priority'    => 1,
        ]);

        $array = $notice->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('message', $array);
        $this->assertArrayHasKey('type', $array);
        $this->assertArrayHasKey('dismissible', $array);
        $this->assertArrayHasKey('actionUrl', $array);
        $this->assertArrayHasKey('actionLabel', $array);
        $this->assertArrayHasKey('helpUrl', $array);
        $this->assertArrayHasKey('priority', $array);

        $this->assertEquals('test_notice', $array['id']);
        $this->assertEquals('Test message', $array['message']);
        $this->assertEquals('error', $array['type']);
        $this->assertFalse($array['dismissible']);
        $this->assertEquals('https://example.com', $array['actionUrl']);
        $this->assertEquals('Click', $array['actionLabel']);
        $this->assertEquals('https://example.com/help', $array['helpUrl']);
        $this->assertEquals(1, $array['priority']);
    }

    /**
     * Test that constructor ignores unknown properties.
     */
    public function test_constructor_ignores_unknown_properties()
    {
        $notice = new NoticeItem([
            'id'              => 'test',
            'message'         => 'Test',
            'unknownProperty' => 'should be ignored',
        ]);

        $this->assertFalse(property_exists($notice, 'unknownProperty'));
        $this->assertEquals('test', $notice->id);
    }

    /**
     * Test priority affects sorting order.
     */
    public function test_priority_comparison()
    {
        $notice1 = new NoticeItem(['id' => 'n1', 'message' => 'M1', 'priority' => 10]);
        $notice2 = new NoticeItem(['id' => 'n2', 'message' => 'M2', 'priority' => 5]);
        $notice3 = new NoticeItem(['id' => 'n3', 'message' => 'M3', 'priority' => 15]);

        $notices = [$notice1, $notice2, $notice3];

        // Sort by priority (lower = higher priority)
        usort($notices, fn($a, $b) => $a->priority <=> $b->priority);

        $this->assertEquals('n2', $notices[0]->id);
        $this->assertEquals('n1', $notices[1]->id);
        $this->assertEquals('n3', $notices[2]->id);
    }
}
