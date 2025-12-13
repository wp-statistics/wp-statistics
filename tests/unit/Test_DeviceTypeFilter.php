<?php

namespace WP_Statistics\Tests\AnalyticsQuery\Filters;

use WP_UnitTestCase;
use WP_Statistics\Service\AnalyticsQuery\Filters\DeviceTypeFilter;

/**
 * Test DeviceTypeFilter class.
 *
 * Tests the DeviceTypeFilter implementation including dropdown options,
 * property values, and configuration.
 */
class Test_DeviceTypeFilter extends WP_UnitTestCase
{
    private $filter;

    public function setUp(): void
    {
        parent::setUp();
        $this->filter = new DeviceTypeFilter();
    }

    /**
     * Test filter name is correct.
     */
    public function test_filter_name()
    {
        $this->assertEquals('device_type', $this->filter->getName());
    }

    /**
     * Test filter column is correct.
     */
    public function test_filter_column()
    {
        $this->assertEquals('device_types.name', $this->filter->getColumn());
    }

    /**
     * Test filter type is string.
     */
    public function test_filter_type()
    {
        $this->assertEquals('string', $this->filter->getType());
    }

    /**
     * Test filter label is translatable.
     */
    public function test_filter_label()
    {
        $label = $this->filter->getLabel();

        $this->assertIsString($label);
        $this->assertNotEmpty($label);
    }

    /**
     * Test filter input type is dropdown.
     */
    public function test_input_type_is_dropdown()
    {
        $this->assertEquals('dropdown', $this->filter->getInputType());
        $this->assertFalse($this->filter->isSearchable());
    }

    /**
     * Test supported operators.
     */
    public function test_supported_operators()
    {
        $operators = $this->filter->getSupportedOperators();

        $this->assertIsArray($operators);
        $this->assertContains('is', $operators);
        $this->assertContains('is_not', $operators);
        $this->assertCount(2, $operators);
    }

    /**
     * Test filter groups.
     */
    public function test_filter_groups()
    {
        $groups = $this->filter->getGroups();

        $this->assertIsArray($groups);
        $this->assertContains('visitors', $groups);
    }

    /**
     * Test filter has joins defined.
     */
    public function test_filter_has_joins()
    {
        $joins = $this->filter->getJoins();

        $this->assertIsArray($joins);
        $this->assertNotEmpty($joins);

        // Check normalized join structure
        $this->assertArrayHasKey('table', $joins[0]);
        $this->assertArrayHasKey('alias', $joins[0]);
        $this->assertArrayHasKey('on', $joins[0]);

        $this->assertEquals('device_types', $joins[0]['table']);
        $this->assertEquals('device_types', $joins[0]['alias']);
        $this->assertStringContainsString('sessions.device_type_id = device_types.ID', $joins[0]['on']);
    }

    /**
     * Test getOptions returns device type options.
     */
    public function test_get_options_returns_device_types()
    {
        $options = $this->filter->getOptions();

        $this->assertIsArray($options);
        $this->assertCount(3, $options);

        // Check structure of options
        foreach ($options as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
        }

        // Check specific values
        $values = array_column($options, 'value');
        $this->assertContains('desktop', $values);
        $this->assertContains('mobile', $values);
        $this->assertContains('tablet', $values);
    }

    /**
     * Test option labels are translatable.
     */
    public function test_option_labels_are_translatable()
    {
        $options = $this->filter->getOptions();

        foreach ($options as $option) {
            $this->assertIsString($option['label']);
            $this->assertNotEmpty($option['label']);
        }
    }

    /**
     * Test option values match expected device types.
     */
    public function test_option_values()
    {
        $options = $this->filter->getOptions();
        $values = array_column($options, 'value');

        $this->assertEquals(['desktop', 'mobile', 'tablet'], $values);
    }

    /**
     * Test filter is not searchable.
     */
    public function test_filter_is_not_searchable()
    {
        $this->assertFalse($this->filter->isSearchable());
    }

    /**
     * Test searchOptions returns empty array (not implemented for dropdown).
     */
    public function test_search_options_returns_empty_array()
    {
        $options = $this->filter->searchOptions('mobile', 10);

        $this->assertIsArray($options);
        $this->assertEmpty($options);
    }

    /**
     * Test toArray includes options.
     */
    public function test_to_array_includes_options()
    {
        $array = $this->filter->toArray();

        $this->assertArrayHasKey('options', $array);
        $this->assertIsArray($array['options']);
        $this->assertCount(3, $array['options']);
    }

    /**
     * Test toFrontendArray includes options.
     */
    public function test_to_frontend_array_includes_options()
    {
        $array = $this->filter->toFrontendArray();

        $this->assertArrayHasKey('options', $array);
        $this->assertIsArray($array['options']);
        $this->assertCount(3, $array['options']);
    }

    /**
     * Test toArray structure is complete.
     */
    public function test_to_array_structure()
    {
        $array = $this->filter->toArray();

        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('column', $array);
        $this->assertArrayHasKey('type', $array);
        $this->assertArrayHasKey('label', $array);
        $this->assertArrayHasKey('supportedOperators', $array);
        $this->assertArrayHasKey('inputType', $array);
        $this->assertArrayHasKey('groups', $array);
        $this->assertArrayHasKey('joins', $array);
        $this->assertArrayHasKey('options', $array);

        $this->assertEquals('device_type', $array['name']);
        $this->assertEquals('dropdown', $array['inputType']);
    }

    /**
     * Test toFrontendArray excludes backend properties.
     */
    public function test_to_frontend_array_excludes_backend_properties()
    {
        $array = $this->filter->toFrontendArray();

        // Should have frontend properties
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('label', $array);
        $this->assertArrayHasKey('supportedOperators', $array);
        $this->assertArrayHasKey('inputType', $array);
        $this->assertArrayHasKey('options', $array);

        // Should NOT have backend properties
        $this->assertArrayNotHasKey('column', $array);
        $this->assertArrayNotHasKey('type', $array);
        $this->assertArrayNotHasKey('joins', $array);
    }
}
