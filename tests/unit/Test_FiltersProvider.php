<?php

namespace WP_Statistics\Tests\ReactApp;

use WP_UnitTestCase;
use WP_Statistics\Service\Admin\ReactApp\Providers\FiltersProvider;

/**
 * Test FiltersProvider class.
 *
 * Tests the FiltersProvider's ability to provide filter definitions
 * and operator definitions to the React frontend.
 */
class Test_FiltersProvider extends WP_UnitTestCase
{
    private $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->provider = new FiltersProvider();
    }

    /**
     * Test getKey returns correct key.
     */
    public function test_get_key_returns_correct_value()
    {
        $this->assertEquals('filters', $this->provider->getKey());
    }

    /**
     * Test getData returns array.
     */
    public function test_get_data_returns_array()
    {
        $data = $this->provider->getData();

        $this->assertIsArray($data);
    }

    /**
     * Test getData contains required fields.
     */
    public function test_get_data_contains_required_fields()
    {
        $data = $this->provider->getData();

        $this->assertArrayHasKey('fields', $data);
        $this->assertArrayHasKey('operators', $data);
    }

    /**
     * Test fields is array.
     */
    public function test_fields_is_array()
    {
        $data = $this->provider->getData();

        $this->assertIsArray($data['fields']);
    }

    /**
     * Test operators is array.
     */
    public function test_operators_is_array()
    {
        $data = $this->provider->getData();

        $this->assertIsArray($data['operators']);
    }

    /**
     * Test operators contains expected operators.
     */
    public function test_operators_contains_expected_operators()
    {
        $data = $this->provider->getData();

        $expectedOperators = [
            'is', 'is_not', 'is_null', 'in', 'not_in',
            'contains', 'starts_with', 'ends_with',
            'gt', 'gte', 'lt', 'lte',
            'between', 'before', 'after', 'in_the_last'
        ];

        foreach ($expectedOperators as $operator) {
            $this->assertArrayHasKey($operator, $data['operators']);
        }
    }

    /**
     * Test each operator has required structure.
     */
    public function test_each_operator_has_required_structure()
    {
        $data = $this->provider->getData();

        foreach ($data['operators'] as $operator => $definition) {
            $this->assertIsArray($definition, "Operator '{$operator}' should be an array");
            $this->assertArrayHasKey('label', $definition, "Operator '{$operator}' missing 'label'");
            $this->assertArrayHasKey('type', $definition, "Operator '{$operator}' missing 'type'");
        }
    }

    /**
     * Test operator labels are translatable.
     */
    public function test_operator_labels_are_translatable()
    {
        $data = $this->provider->getData();

        foreach ($data['operators'] as $operator => $definition) {
            $this->assertIsString($definition['label'], "Operator '{$operator}' label should be a string");
            $this->assertNotEmpty($definition['label'], "Operator '{$operator}' label should not be empty");
        }
    }

    /**
     * Test operator types are valid.
     */
    public function test_operator_types_are_valid()
    {
        $data = $this->provider->getData();

        $validTypes = ['single', 'multiple', 'range'];

        foreach ($data['operators'] as $operator => $definition) {
            $this->assertContains(
                $definition['type'],
                $validTypes,
                "Operator '{$operator}' has invalid type '{$definition['type']}'"
            );
        }
    }

    /**
     * Test specific operator definitions.
     */
    public function test_specific_operator_definitions()
    {
        $data = $this->provider->getData();

        // Test 'is' operator
        $this->assertEquals('single', $data['operators']['is']['type']);

        // Test 'in' operator
        $this->assertEquals('multiple', $data['operators']['in']['type']);

        // Test 'between' operator
        $this->assertEquals('range', $data['operators']['between']['type']);
    }

    /**
     * Test getData applies filter hook.
     */
    public function test_get_data_applies_filter()
    {
        // Add a filter to modify data
        add_filter('wp_statistics_dashboard_filters_data', function ($data) {
            $data['custom_field'] = 'custom_value';
            return $data;
        });

        $data = $this->provider->getData();

        $this->assertArrayHasKey('custom_field', $data);
        $this->assertEquals('custom_value', $data['custom_field']);

        // Remove filter to not affect other tests
        remove_all_filters('wp_statistics_dashboard_filters_data');
    }

    /**
     * Test getData filter can modify operators.
     */
    public function test_get_data_filter_can_modify_operators()
    {
        // Add a filter to add a custom operator
        add_filter('wp_statistics_dashboard_filters_data', function ($data) {
            $data['operators']['custom_operator'] = [
                'label' => 'Custom Operator',
                'type'  => 'single'
            ];
            return $data;
        });

        $data = $this->provider->getData();

        $this->assertArrayHasKey('custom_operator', $data['operators']);
        $this->assertEquals('Custom Operator', $data['operators']['custom_operator']['label']);

        // Remove filter to not affect other tests
        remove_all_filters('wp_statistics_dashboard_filters_data');
    }

    /**
     * Test getData filter can modify fields.
     */
    public function test_get_data_filter_can_modify_fields()
    {
        // Add a filter to modify fields
        add_filter('wp_statistics_dashboard_filters_data', function ($data) {
            $data['fields'] = array_merge($data['fields'], [
                [
                    'name'  => 'custom_filter',
                    'label' => 'Custom Filter',
                ]
            ]);
            return $data;
        });

        $data = $this->provider->getData();

        $this->assertIsArray($data['fields']);

        // Remove filter to not affect other tests
        remove_all_filters('wp_statistics_dashboard_filters_data');
    }

    /**
     * Test comparison operators have correct types.
     */
    public function test_comparison_operators_have_correct_types()
    {
        $data = $this->provider->getData();

        // Single value operators
        $singleOperators = ['is', 'is_not', 'is_null', 'contains', 'starts_with', 'ends_with', 'gt', 'gte', 'lt', 'lte', 'before', 'after', 'in_the_last'];

        foreach ($singleOperators as $operator) {
            $this->assertEquals('single', $data['operators'][$operator]['type'], "Operator '{$operator}' should be 'single' type");
        }

        // Multiple value operators
        $multipleOperators = ['in', 'not_in'];

        foreach ($multipleOperators as $operator) {
            $this->assertEquals('multiple', $data['operators'][$operator]['type'], "Operator '{$operator}' should be 'multiple' type");
        }

        // Range operators
        $this->assertEquals('range', $data['operators']['between']['type']);
    }

    /**
     * Test operator labels don't contain HTML.
     */
    public function test_operator_labels_dont_contain_html()
    {
        $data = $this->provider->getData();

        foreach ($data['operators'] as $operator => $definition) {
            $this->assertDoesNotMatchRegularExpression(
                '/<[^>]+>/',
                $definition['label'],
                "Operator '{$operator}' label should not contain HTML tags"
            );
        }
    }

    /**
     * Test all expected operator types are present.
     */
    public function test_all_expected_operator_types_are_present()
    {
        $data = $this->provider->getData();

        // Should have at least one of each type
        $types = array_column($data['operators'], 'type');

        $this->assertContains('single', $types);
        $this->assertContains('multiple', $types);
        $this->assertContains('range', $types);
    }

    /**
     * Test operator definitions are consistent.
     */
    public function test_operator_definitions_are_consistent()
    {
        $data = $this->provider->getData();

        // All operators should have both label and type
        foreach ($data['operators'] as $operator => $definition) {
            $this->assertCount(2, $definition, "Operator '{$operator}' should have exactly 2 properties (label and type)");
        }
    }
}
