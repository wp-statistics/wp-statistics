<?php

use WP_Statistics\Service\Admin\Diagnostic\DiagnosticResult;

/**
 * Test case for DiagnosticResult class.
 *
 * @covers \WP_Statistics\Service\Admin\Diagnostic\DiagnosticResult
 */
class Test_DiagnosticResult extends WP_UnitTestCase
{
    /**
     * Test creating a passing result.
     */
    public function test_pass_creates_correct_status()
    {
        $result = DiagnosticResult::pass(
            'test_check',
            'Test Check',
            'Everything is working fine.',
            ['detail' => 'value'],
            'https://example.com/help'
        );

        $this->assertEquals('test_check', $result->key);
        $this->assertEquals('Test Check', $result->label);
        $this->assertEquals(DiagnosticResult::STATUS_PASS, $result->status);
        $this->assertEquals('Everything is working fine.', $result->message);
        $this->assertEquals(['detail' => 'value'], $result->details);
        $this->assertEquals('https://example.com/help', $result->helpUrl);
        $this->assertTrue($result->isPassed());
        $this->assertFalse($result->isWarning());
        $this->assertFalse($result->isFailed());
    }

    /**
     * Test creating a warning result.
     */
    public function test_warning_creates_correct_status()
    {
        $result = DiagnosticResult::warning(
            'test_check',
            'Test Check',
            'Some issues detected.',
            ['issue' => 'minor'],
            'https://example.com/help'
        );

        $this->assertEquals(DiagnosticResult::STATUS_WARNING, $result->status);
        $this->assertFalse($result->isPassed());
        $this->assertTrue($result->isWarning());
        $this->assertFalse($result->isFailed());
    }

    /**
     * Test creating a failing result.
     */
    public function test_fail_creates_correct_status()
    {
        $result = DiagnosticResult::fail(
            'test_check',
            'Test Check',
            'Critical error detected.',
            ['error' => 'critical'],
            'https://example.com/help'
        );

        $this->assertEquals(DiagnosticResult::STATUS_FAIL, $result->status);
        $this->assertFalse($result->isPassed());
        $this->assertFalse($result->isWarning());
        $this->assertTrue($result->isFailed());
    }

    /**
     * Test constructor with array data.
     */
    public function test_constructor_with_array()
    {
        $result = new DiagnosticResult([
            'key'     => 'custom_check',
            'label'   => 'Custom Check',
            'status'  => DiagnosticResult::STATUS_PASS,
            'message' => 'Custom message',
            'details' => ['custom' => 'detail'],
            'helpUrl' => 'https://example.com',
        ]);

        $this->assertEquals('custom_check', $result->key);
        $this->assertEquals('Custom Check', $result->label);
        $this->assertEquals(DiagnosticResult::STATUS_PASS, $result->status);
        $this->assertEquals('Custom message', $result->message);
        $this->assertEquals(['custom' => 'detail'], $result->details);
        $this->assertEquals('https://example.com', $result->helpUrl);
    }

    /**
     * Test timestamp is set automatically.
     */
    public function test_timestamp_is_set_automatically()
    {
        $before = time();
        $result = DiagnosticResult::pass('test', 'Test', 'Message');
        $after = time();

        $this->assertGreaterThanOrEqual($before, $result->timestamp);
        $this->assertLessThanOrEqual($after, $result->timestamp);
    }

    /**
     * Test toArray method.
     */
    public function test_to_array_returns_all_properties()
    {
        $result = DiagnosticResult::pass(
            'test_check',
            'Test Check',
            'Test message',
            ['detail' => 'value'],
            'https://example.com'
        );

        $array = $result->toArray();

        $this->assertArrayHasKey('key', $array);
        $this->assertArrayHasKey('label', $array);
        $this->assertArrayHasKey('status', $array);
        $this->assertArrayHasKey('message', $array);
        $this->assertArrayHasKey('details', $array);
        $this->assertArrayHasKey('helpUrl', $array);
        $this->assertArrayHasKey('timestamp', $array);

        $this->assertEquals('test_check', $array['key']);
        $this->assertEquals('Test Check', $array['label']);
        $this->assertEquals('pass', $array['status']);
        $this->assertEquals('Test message', $array['message']);
        $this->assertEquals(['detail' => 'value'], $array['details']);
        $this->assertEquals('https://example.com', $array['helpUrl']);
    }

    /**
     * Test status constants.
     */
    public function test_status_constants_exist()
    {
        $this->assertEquals('pass', DiagnosticResult::STATUS_PASS);
        $this->assertEquals('warning', DiagnosticResult::STATUS_WARNING);
        $this->assertEquals('fail', DiagnosticResult::STATUS_FAIL);
    }

    /**
     * Test default values for optional parameters.
     */
    public function test_default_values()
    {
        $result = DiagnosticResult::pass('test', 'Test', 'Message');

        $this->assertEquals([], $result->details);
        $this->assertNull($result->helpUrl);
    }

    /**
     * Test hydrating result from cached array data.
     */
    public function test_hydrate_from_array()
    {
        $originalResult = DiagnosticResult::warning(
            'cache_test',
            'Cache Test',
            'Warning message',
            ['cached' => true]
        );

        $arrayData = $originalResult->toArray();

        // Simulate hydrating from cache
        $hydratedResult = new DiagnosticResult($arrayData);

        $this->assertEquals($originalResult->key, $hydratedResult->key);
        $this->assertEquals($originalResult->label, $hydratedResult->label);
        $this->assertEquals($originalResult->status, $hydratedResult->status);
        $this->assertEquals($originalResult->message, $hydratedResult->message);
        $this->assertEquals($originalResult->details, $hydratedResult->details);
        $this->assertEquals($originalResult->timestamp, $hydratedResult->timestamp);
    }
}
