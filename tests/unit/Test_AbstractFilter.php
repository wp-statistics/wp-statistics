<?php

namespace WP_Statistics\Tests\AnalyticsQuery;

use WP_UnitTestCase;
use WP_Statistics\Service\AnalyticsQuery\Filters\AbstractFilter;

/**
 * Test AbstractFilter class.
 *
 * Tests the base filter functionality including property getters,
 * JOIN normalization, and array conversion methods.
 */
class Test_AbstractFilter extends WP_UnitTestCase
{
    private $filter;

    public function setUp(): void
    {
        parent::setUp();

        // Create a concrete implementation for testing
        $this->filter = new class extends AbstractFilter {
            protected $name = 'test_filter';
            protected $column = 'test_table.test_column';
            protected $type = 'string';
            protected $inputType = 'text';
            protected $groups = ['visitors'];

            public function getLabel(): string
            {
                return 'Test Filter';
            }
        };
    }

    /**
     * Test getName method.
     */
    public function test_get_name()
    {
        $this->assertEquals('test_filter', $this->filter->getName());
    }

    /**
     * Test getColumn method.
     */
    public function test_get_column()
    {
        $this->assertEquals('test_table.test_column', $this->filter->getColumn());
    }

    /**
     * Test getType method.
     */
    public function test_get_type()
    {
        $this->assertEquals('string', $this->filter->getType());
    }

    /**
     * Test getLabel method.
     */
    public function test_get_label()
    {
        $this->assertEquals('Test Filter', $this->filter->getLabel());
    }

    /**
     * Test getInputType method.
     */
    public function test_get_input_type()
    {
        $this->assertEquals('text', $this->filter->getInputType());
    }

    /**
     * Test getGroups method.
     */
    public function test_get_groups()
    {
        $this->assertIsArray($this->filter->getGroups());
        $this->assertContains('visitors', $this->filter->getGroups());
    }

    /**
     * Test getSupportedOperators returns default operators.
     */
    public function test_get_supported_operators()
    {
        $operators = $this->filter->getSupportedOperators();

        $this->assertIsArray($operators);
        $this->assertContains('is', $operators);
        $this->assertContains('is_not', $operators);
        $this->assertContains('in', $operators);
        $this->assertContains('not_in', $operators);
        $this->assertContains('contains', $operators);
    }

    /**
     * Test getOptions returns null by default.
     */
    public function test_get_options_returns_null_by_default()
    {
        $this->assertNull($this->filter->getOptions());
    }

    /**
     * Test getOptions returns array when set.
     */
    public function test_get_options_returns_array_when_set()
    {
        $filter = new class extends AbstractFilter {
            protected $name = 'test_dropdown';
            protected $column = 'test_column';
            protected $inputType = 'dropdown';
            protected $options = [
                ['value' => '1', 'label' => 'Option 1'],
                ['value' => '2', 'label' => 'Option 2'],
            ];

            public function getLabel(): string
            {
                return 'Test Dropdown';
            }
        };

        $options = $filter->getOptions();

        $this->assertIsArray($options);
        $this->assertCount(2, $options);
        $this->assertEquals('1', $options[0]['value']);
        $this->assertEquals('Option 1', $options[0]['label']);
    }

    /**
     * Test getJoins returns null when no joins defined.
     */
    public function test_get_joins_returns_null_when_not_defined()
    {
        $this->assertNull($this->filter->getJoins());
    }

    /**
     * Test getJoins normalizes single join to array.
     */
    public function test_get_joins_normalizes_single_join_to_array()
    {
        $filter = new class extends AbstractFilter {
            protected $name = 'with_join';
            protected $column = 'joined_table.column';
            protected $joins = [
                'table' => 'joined_table',
                'alias' => 'joined_table',
                'on'    => 'main.id = joined_table.main_id',
                'type'  => 'LEFT'
            ];

            public function getLabel(): string
            {
                return 'With Join';
            }
        };

        $joins = $filter->getJoins();

        $this->assertIsArray($joins);
        $this->assertCount(1, $joins);
        $this->assertArrayHasKey('table', $joins[0]);
        $this->assertEquals('joined_table', $joins[0]['table']);
    }

    /**
     * Test getJoins returns multiple joins as is.
     */
    public function test_get_joins_returns_multiple_joins()
    {
        $filter = new class extends AbstractFilter {
            protected $name = 'with_multiple_joins';
            protected $column = 'table3.column';
            protected $joins = [
                [
                    'table' => 'table2',
                    'alias' => 'table2',
                    'on'    => 'table1.id = table2.table1_id',
                ],
                [
                    'table' => 'table3',
                    'alias' => 'table3',
                    'on'    => 'table2.id = table3.table2_id',
                ]
            ];

            public function getLabel(): string
            {
                return 'With Multiple Joins';
            }
        };

        $joins = $filter->getJoins();

        $this->assertIsArray($joins);
        $this->assertCount(2, $joins);
        $this->assertEquals('table2', $joins[0]['table']);
        $this->assertEquals('table3', $joins[1]['table']);
    }

    /**
     * Test getRequirement returns null by default.
     */
    public function test_get_requirement_returns_null_by_default()
    {
        $this->assertNull($this->filter->getRequirement());
    }

    /**
     * Test getRequirement returns value when set.
     */
    public function test_get_requirement_returns_value_when_set()
    {
        $filter = new class extends AbstractFilter {
            protected $name = 'requires_sessions';
            protected $column = 'sessions.user_id';
            protected $requirement = 'sessions';

            public function getLabel(): string
            {
                return 'Requires Sessions';
            }
        };

        $this->assertEquals('sessions', $filter->getRequirement());
    }

    /**
     * Test isSearchable returns false for non-searchable filters.
     */
    public function test_is_searchable_returns_false_by_default()
    {
        $this->assertFalse($this->filter->isSearchable());
    }

    /**
     * Test isSearchable returns true for searchable filters.
     */
    public function test_is_searchable_returns_true_for_searchable()
    {
        $filter = new class extends AbstractFilter {
            protected $name = 'searchable_filter';
            protected $column = 'test_column';
            protected $inputType = 'searchable';

            public function getLabel(): string
            {
                return 'Searchable Filter';
            }
        };

        $this->assertTrue($filter->isSearchable());
    }

    /**
     * Test searchOptions returns empty array by default.
     */
    public function test_search_options_returns_empty_array_by_default()
    {
        $options = $this->filter->searchOptions('test', 10);

        $this->assertIsArray($options);
        $this->assertEmpty($options);
    }

    /**
     * Test toArray method includes all properties.
     */
    public function test_to_array_includes_all_properties()
    {
        $array = $this->filter->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('column', $array);
        $this->assertArrayHasKey('type', $array);
        $this->assertArrayHasKey('label', $array);
        $this->assertArrayHasKey('supportedOperators', $array);
        $this->assertArrayHasKey('inputType', $array);
        $this->assertArrayHasKey('groups', $array);

        $this->assertEquals('test_filter', $array['name']);
        $this->assertEquals('test_table.test_column', $array['column']);
        $this->assertEquals('string', $array['type']);
        $this->assertEquals('Test Filter', $array['label']);
    }

    /**
     * Test toArray includes joins when defined.
     */
    public function test_to_array_includes_joins_when_defined()
    {
        $filter = new class extends AbstractFilter {
            protected $name = 'with_join';
            protected $column = 'joined.column';
            protected $joins = [
                'table' => 'joined_table',
                'alias' => 'joined',
                'on'    => 'main.id = joined.main_id',
            ];

            public function getLabel(): string
            {
                return 'With Join';
            }
        };

        $array = $filter->toArray();

        $this->assertArrayHasKey('joins', $array);
        $this->assertIsArray($array['joins']);
    }

    /**
     * Test toArray includes requirement when defined.
     */
    public function test_to_array_includes_requirement_when_defined()
    {
        $filter = new class extends AbstractFilter {
            protected $name = 'with_requirement';
            protected $column = 'sessions.column';
            protected $requirement = 'sessions';

            public function getLabel(): string
            {
                return 'With Requirement';
            }
        };

        $array = $filter->toArray();

        $this->assertArrayHasKey('requirement', $array);
        $this->assertEquals('sessions', $array['requirement']);
    }

    /**
     * Test toArray includes options when defined.
     */
    public function test_to_array_includes_options_when_defined()
    {
        $filter = new class extends AbstractFilter {
            protected $name = 'with_options';
            protected $column = 'column';
            protected $inputType = 'dropdown';
            protected $options = [
                ['value' => 'a', 'label' => 'Option A'],
            ];

            public function getLabel(): string
            {
                return 'With Options';
            }
        };

        $array = $filter->toArray();

        $this->assertArrayHasKey('options', $array);
        $this->assertIsArray($array['options']);
        $this->assertCount(1, $array['options']);
    }

    /**
     * Test toFrontendArray excludes backend-only fields.
     */
    public function test_to_frontend_array_excludes_backend_fields()
    {
        $filter = new class extends AbstractFilter {
            protected $name = 'test';
            protected $column = 'table.column';
            protected $type = 'string';
            protected $requirement = 'sessions';
            protected $joins = [
                'table' => 'joined',
                'alias' => 'joined',
                'on'    => 'main.id = joined.main_id',
            ];

            public function getLabel(): string
            {
                return 'Test';
            }
        };

        $array = $filter->toFrontendArray();

        // Should include frontend fields
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('label', $array);
        $this->assertArrayHasKey('supportedOperators', $array);
        $this->assertArrayHasKey('inputType', $array);
        $this->assertArrayHasKey('groups', $array);

        // Should NOT include backend fields
        $this->assertArrayNotHasKey('column', $array);
        $this->assertArrayNotHasKey('type', $array);
        $this->assertArrayNotHasKey('requirement', $array);
        $this->assertArrayNotHasKey('joins', $array);
    }

    /**
     * Test toFrontendArray includes options when defined.
     */
    public function test_to_frontend_array_includes_options()
    {
        $filter = new class extends AbstractFilter {
            protected $name = 'with_options';
            protected $column = 'column';
            protected $options = [
                ['value' => 'x', 'label' => 'X'],
            ];

            public function getLabel(): string
            {
                return 'With Options';
            }
        };

        $array = $filter->toFrontendArray();

        $this->assertArrayHasKey('options', $array);
        $this->assertIsArray($array['options']);
    }
}
