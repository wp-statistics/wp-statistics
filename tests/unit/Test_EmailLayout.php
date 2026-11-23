<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Helper;

/**
 * The email layout is included once per email. Two emails in one request
 * (for example a report and an alert) must not redeclare its helpers, and the
 * layout must fit a phone screen.
 */
class Test_EmailLayout extends WP_UnitTestCase
{
    private $sent = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->sent = [];
        add_filter('pre_wp_mail', [$this, 'catchMail'], 10, 2);
    }

    public function tearDown(): void
    {
        remove_filter('pre_wp_mail', [$this, 'catchMail'], 10);

        parent::tearDown();
    }

    public function catchMail($return, $atts)
    {
        $this->sent[] = $atts;

        return true;
    }

    public function test_two_reports_in_one_request_do_not_redeclare_helpers()
    {
        $first  = Helper::send_mail('admin@example.com', 'Report one', 'First report');
        $second = Helper::send_mail('admin@example.com', 'Report two', 'Second report');

        $this->assertTrue($first);
        $this->assertTrue($second);
        $this->assertCount(2, $this->sent);
        $this->assertTrue(function_exists('wp_statistics_email_performance_section'));
        $this->assertFalse(function_exists('getPerformanceStyles'), 'The old global helper name is gone');
    }

    public function test_report_then_alert_in_one_request()
    {
        $this->assertTrue(Helper::send_mail('admin@example.com', 'Report', 'Report body'));
        $this->assertTrue(Helper::send_mail('admin@example.com', 'Alert', 'Alert body', true, ['is_report' => false]));
        $this->assertCount(2, $this->sent);
    }

    public function test_layout_is_fluid_for_small_screens()
    {
        Helper::send_mail('admin@example.com', 'Report', 'Report body');

        $html = $this->sent[0]['message'];

        $this->assertStringContainsString('max-width: 618px', $html);
        $this->assertDoesNotMatchRegularExpression('/[^-]width:\s*618px/', $html, 'No fixed 618px width');
        $this->assertStringContainsString('class="content__tip" width="100%"', $html);
    }
}
