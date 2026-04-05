<?php

namespace WP_Statistics\Tests\Utils;

use WP_UnitTestCase;
use WP_Statistics\Utils\OptionValueFormatter;

/**
 * Tests for OptionValueFormatter utility class.
 */
class Test_OptionValueFormatter extends WP_UnitTestCase
{
    public function test_formats_array_as_json()
    {
        $result = OptionValueFormatter::format(['a' => 1, 'b' => 2]);
        $this->assertStringContainsString('"a": 1', $result);
    }

    public function test_formats_nested_array()
    {
        $result = OptionValueFormatter::format(['parent' => ['child' => 'value']]);
        $this->assertStringContainsString('"child": "value"', $result);
    }

    public function test_formats_empty_array()
    {
        $result = OptionValueFormatter::format([]);
        $this->assertEquals('[]', $result);
    }

    public function test_formats_object()
    {
        $obj = (object) ['key' => 'val'];
        $result = OptionValueFormatter::format($obj);
        $this->assertStringContainsString('"key": "val"', $result);
    }

    public function test_formats_bool_as_string()
    {
        $this->assertEquals('true', OptionValueFormatter::format(true));
        $this->assertEquals('false', OptionValueFormatter::format(false));
    }

    public function test_formats_null_as_dash()
    {
        $this->assertEquals('-', OptionValueFormatter::format(null));
        $this->assertEquals('-', OptionValueFormatter::format(''));
    }

    public function test_formats_scalar_as_string()
    {
        $this->assertEquals('42', OptionValueFormatter::format(42));
        $this->assertEquals('hello', OptionValueFormatter::format('hello'));
    }

    public function test_formats_zero_as_string()
    {
        $this->assertEquals('0', OptionValueFormatter::format(0));
    }

    public function test_formats_float_as_string()
    {
        $this->assertEquals('3.14', OptionValueFormatter::format(3.14));
    }
}
