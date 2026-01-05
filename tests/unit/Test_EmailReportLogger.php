<?php

use WP_Statistics\Service\EmailReport\EmailReportLogger;

/**
 * Test EmailReportLogger class.
 *
 * @group cron
 * @group email
 */
class Test_EmailReportLogger extends WP_UnitTestCase
{
    /**
     * @var EmailReportLogger
     */
    private $logger;

    public function setUp(): void
    {
        parent::setUp();
        $this->logger = new EmailReportLogger();
        // Clear any existing log
        $this->logger->clear();
    }

    public function tearDown(): void
    {
        $this->logger->clear();
        parent::tearDown();
    }

    public function test_log_creates_entry()
    {
        $this->logger->log([
            'success'    => true,
            'recipients' => ['test@example.com'],
            'frequency'  => 'weekly',
            'error'      => null,
        ]);

        $log = $this->logger->getLog();

        $this->assertCount(1, $log);
        $this->assertTrue($log[0]['success']);
        $this->assertEquals(['test@example.com'], $log[0]['recipients']);
        $this->assertEquals('weekly', $log[0]['frequency']);
        $this->assertNull($log[0]['error']);
    }

    public function test_log_prepends_new_entries()
    {
        $this->logger->log([
            'success'    => true,
            'recipients' => ['first@example.com'],
            'frequency'  => 'daily',
        ]);

        $this->logger->log([
            'success'    => false,
            'recipients' => ['second@example.com'],
            'frequency'  => 'weekly',
            'error'      => 'Send failed',
        ]);

        $log = $this->logger->getLog();

        $this->assertCount(2, $log);
        // Most recent entry should be first
        $this->assertFalse($log[0]['success']);
        $this->assertEquals(['second@example.com'], $log[0]['recipients']);
        $this->assertTrue($log[1]['success']);
    }

    public function test_log_respects_max_entries()
    {
        // Log more than max entries (50)
        for ($i = 0; $i < 60; $i++) {
            $this->logger->log([
                'success'    => true,
                'recipients' => ["test{$i}@example.com"],
                'frequency'  => 'daily',
            ]);
        }

        $log = $this->logger->getLog();

        // Should only keep 50 entries
        $this->assertCount(50, $log);
        // Most recent should be first (index 59)
        $this->assertEquals(['test59@example.com'], $log[0]['recipients']);
    }

    public function test_getRecentEntries()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->logger->log([
                'success'    => true,
                'recipients' => ["test{$i}@example.com"],
                'frequency'  => 'daily',
            ]);
        }

        $recent = $this->logger->getRecentEntries(5);

        $this->assertCount(5, $recent);
        $this->assertEquals(['test19@example.com'], $recent[0]['recipients']);
    }

    public function test_getLastSent_returns_last_successful()
    {
        $this->logger->log([
            'success'    => true,
            'recipients' => ['first@example.com'],
            'frequency'  => 'daily',
        ]);

        $this->logger->log([
            'success'    => false,
            'recipients' => ['failed@example.com'],
            'frequency'  => 'daily',
            'error'      => 'Failed',
        ]);

        $lastSent = $this->logger->getLastSent();

        // Should return the first successful entry's sent_at
        $this->assertNotNull($lastSent);
    }

    public function test_getLastSent_returns_null_when_no_success()
    {
        $this->logger->log([
            'success'    => false,
            'recipients' => ['failed@example.com'],
            'frequency'  => 'daily',
            'error'      => 'Failed',
        ]);

        $lastSent = $this->logger->getLastSent();

        $this->assertNull($lastSent);
    }

    public function test_getLastResult()
    {
        $this->logger->log([
            'success'    => true,
            'recipients' => ['test@example.com'],
            'frequency'  => 'weekly',
        ]);

        $lastResult = $this->logger->getLastResult();

        $this->assertIsArray($lastResult);
        $this->assertTrue($lastResult['success']);
    }

    public function test_getLastResult_returns_null_when_empty()
    {
        $lastResult = $this->logger->getLastResult();

        $this->assertNull($lastResult);
    }

    public function test_getSuccessCount()
    {
        $this->logger->log(['success' => true, 'recipients' => [], 'frequency' => 'daily']);
        $this->logger->log(['success' => true, 'recipients' => [], 'frequency' => 'daily']);
        $this->logger->log(['success' => false, 'recipients' => [], 'frequency' => 'daily', 'error' => 'Failed']);

        $successCount = $this->logger->getSuccessCount();

        $this->assertEquals(2, $successCount);
    }

    public function test_getFailureCount()
    {
        $this->logger->log(['success' => true, 'recipients' => [], 'frequency' => 'daily']);
        $this->logger->log(['success' => false, 'recipients' => [], 'frequency' => 'daily', 'error' => 'Failed']);
        $this->logger->log(['success' => false, 'recipients' => [], 'frequency' => 'daily', 'error' => 'Failed']);

        $failureCount = $this->logger->getFailureCount();

        $this->assertEquals(2, $failureCount);
    }

    public function test_getFailures()
    {
        $this->logger->log(['success' => true, 'recipients' => ['success@example.com'], 'frequency' => 'daily']);
        $this->logger->log(['success' => false, 'recipients' => ['fail1@example.com'], 'frequency' => 'daily', 'error' => 'Error 1']);
        $this->logger->log(['success' => false, 'recipients' => ['fail2@example.com'], 'frequency' => 'daily', 'error' => 'Error 2']);

        $failures = $this->logger->getFailures();

        $this->assertCount(2, $failures);
    }

    public function test_wasLastSendSuccessful()
    {
        $this->logger->log(['success' => true, 'recipients' => [], 'frequency' => 'daily']);

        $this->assertTrue($this->logger->wasLastSendSuccessful());

        $this->logger->log(['success' => false, 'recipients' => [], 'frequency' => 'daily', 'error' => 'Failed']);

        $this->assertFalse($this->logger->wasLastSendSuccessful());
    }

    public function test_wasLastSendSuccessful_returns_null_when_empty()
    {
        $this->assertNull($this->logger->wasLastSendSuccessful());
    }

    public function test_getStatistics()
    {
        $this->logger->log(['success' => true, 'recipients' => [], 'frequency' => 'daily']);
        $this->logger->log(['success' => true, 'recipients' => [], 'frequency' => 'daily']);
        $this->logger->log(['success' => false, 'recipients' => [], 'frequency' => 'daily', 'error' => 'Failed']);
        $this->logger->log(['success' => false, 'recipients' => [], 'frequency' => 'daily', 'error' => 'Failed']);

        $stats = $this->logger->getStatistics();

        $this->assertEquals(4, $stats['total_entries']);
        $this->assertEquals(2, $stats['successful_sends']);
        $this->assertEquals(2, $stats['failed_sends']);
        $this->assertEquals(50.0, $stats['success_rate']);
    }

    public function test_clear()
    {
        $this->logger->log(['success' => true, 'recipients' => [], 'frequency' => 'daily']);
        $this->logger->log(['success' => true, 'recipients' => [], 'frequency' => 'daily']);

        $this->assertCount(2, $this->logger->getLog());

        $this->logger->clear();

        $this->assertCount(0, $this->logger->getLog());
    }

    public function test_formatEntry()
    {
        $entry = [
            'sent_at'    => '2024-01-15 10:30:00',
            'timestamp'  => strtotime('2024-01-15 10:30:00'),
            'success'    => true,
            'recipients' => ['test@example.com', 'test2@example.com'],
            'frequency'  => 'weekly',
            'error'      => null,
        ];

        $formatted = $this->logger->formatEntry($entry);

        $this->assertEquals('Sent', $formatted['status']);
        $this->assertEquals('test@example.com, test2@example.com', $formatted['recipients']);
        $this->assertEquals('Weekly', $formatted['frequency']);
        $this->assertEmpty($formatted['error']);
    }

    public function test_formatEntry_failed()
    {
        $entry = [
            'sent_at'    => '2024-01-15 10:30:00',
            'timestamp'  => strtotime('2024-01-15 10:30:00'),
            'success'    => false,
            'recipients' => ['test@example.com'],
            'frequency'  => 'daily',
            'error'      => 'SMTP connection failed',
        ];

        $formatted = $this->logger->formatEntry($entry);

        $this->assertEquals('Failed', $formatted['status']);
        $this->assertEquals('SMTP connection failed', $formatted['error']);
    }

    public function test_getFormattedLog()
    {
        $this->logger->log(['success' => true, 'recipients' => ['test1@example.com'], 'frequency' => 'daily']);
        $this->logger->log(['success' => false, 'recipients' => ['test2@example.com'], 'frequency' => 'weekly', 'error' => 'Error']);

        $formatted = $this->logger->getFormattedLog(2);

        $this->assertCount(2, $formatted);
        $this->assertEquals('Failed', $formatted[0]['status']); // Most recent first
        $this->assertEquals('Sent', $formatted[1]['status']);
    }
}
