<?php

use WP_Statistics\Service\CLI\Commands\DiagnosticCommand;
use WP_Statistics\Service\Admin\Diagnostic\DiagnosticResult;

/**
 * Test case for DiagnosticCommand class.
 *
 * Tests result formatting and command structure.
 *
 * @covers \WP_Statistics\Service\CLI\Commands\DiagnosticCommand
 * @group cli
 */
class Test_DiagnosticCommand extends WP_UnitTestCase
{
    /**
     * @var DiagnosticCommand
     */
    private $command;

    /**
     * @var ReflectionMethod
     */
    private $formatResultMethod;

    public function setUp(): void
    {
        parent::setUp();
        $this->command = new DiagnosticCommand();

        $this->formatResultMethod = new ReflectionMethod(DiagnosticCommand::class, 'formatResult');
        $this->formatResultMethod->setAccessible(true);
    }

    /**
     * Test DiagnosticCommand can be instantiated.
     */
    public function test_command_instantiation()
    {
        $this->assertInstanceOf(DiagnosticCommand::class, $this->command);
    }

    /**
     * Test run subcommand method exists.
     */
    public function test_run_method_exists()
    {
        $this->assertTrue(method_exists($this->command, 'run'));
    }

    /**
     * Test status subcommand method exists.
     */
    public function test_status_method_exists()
    {
        $this->assertTrue(method_exists($this->command, 'status'));
    }

    /**
     * Test formatResult outputs correct keys for pass.
     */
    public function test_format_result_pass()
    {
        $result = DiagnosticResult::pass('geoip', 'GeoIP Database', 'Database is up to date.');

        $formatted = $this->formatResultMethod->invoke($this->command, $result);

        $this->assertArrayHasKey('Check', $formatted);
        $this->assertArrayHasKey('Status', $formatted);
        $this->assertArrayHasKey('Message', $formatted);
        $this->assertEquals('GeoIP Database', $formatted['Check']);
        $this->assertEquals(DiagnosticResult::STATUS_PASS, $formatted['Status']);
        $this->assertEquals('Database is up to date.', $formatted['Message']);
    }

    /**
     * Test formatResult outputs correct keys for fail.
     */
    public function test_format_result_fail()
    {
        $result = DiagnosticResult::fail('cron', 'Cron Check', 'Cron is not running.');

        $formatted = $this->formatResultMethod->invoke($this->command, $result);

        $this->assertEquals('Cron Check', $formatted['Check']);
        $this->assertEquals(DiagnosticResult::STATUS_FAIL, $formatted['Status']);
        $this->assertEquals('Cron is not running.', $formatted['Message']);
    }

    /**
     * Test formatResult outputs correct keys for warning.
     */
    public function test_format_result_warning()
    {
        $result = DiagnosticResult::warning('cache', 'Cache Check', 'Object cache not enabled.');

        $formatted = $this->formatResultMethod->invoke($this->command, $result);

        $this->assertEquals('Cache Check', $formatted['Check']);
        $this->assertEquals(DiagnosticResult::STATUS_WARNING, $formatted['Status']);
        $this->assertEquals('Object cache not enabled.', $formatted['Message']);
    }

    /**
     * Test formatResult uses label when present.
     */
    public function test_format_result_uses_label()
    {
        $result = new DiagnosticResult([
            'key'     => 'test_key',
            'label'   => 'Test Label',
            'status'  => DiagnosticResult::STATUS_PASS,
            'message' => 'Test message.',
        ]);

        $formatted = $this->formatResultMethod->invoke($this->command, $result);

        $this->assertEquals('Test Label', $formatted['Check']);
    }

    /**
     * Test formatResult handles empty message string.
     */
    public function test_format_result_empty_message()
    {
        $result = new DiagnosticResult([
            'key'     => 'test',
            'label'   => 'Test',
            'status'  => DiagnosticResult::STATUS_PASS,
            'message' => '',
        ]);

        $formatted = $this->formatResultMethod->invoke($this->command, $result);

        $this->assertEquals('', $formatted['Message']);
    }
}
