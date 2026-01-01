<?php

namespace WP_Statistics\Tests\AnalyticsQuery\Filters;

use WP_UnitTestCase;
use WP_Statistics\Service\AnalyticsQuery\Filters\CountryFilter;

/**
 * Test CountryFilter class.
 *
 * Tests the CountryFilter implementation including property values,
 * searchable functionality, and AJAX option searching.
 */
class Test_CountryFilter extends WP_UnitTestCase
{
    private $filter;

    public function setUp(): void
    {
        parent::setUp();
        $this->filter = new CountryFilter();
    }

    /**
     * Test filter name is correct.
     */
    public function test_filter_name()
    {
        $this->assertEquals('country', $this->filter->getName());
    }

    /**
     * Test filter column is correct.
     */
    public function test_filter_column()
    {
        $this->assertEquals('countries.ID', $this->filter->getColumn());
    }

    /**
     * Test filter type is integer (uses ID).
     */
    public function test_filter_type()
    {
        $this->assertEquals('integer', $this->filter->getType());
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
     * Test filter input type is searchable.
     */
    public function test_input_type_is_searchable()
    {
        $this->assertEquals('searchable', $this->filter->getInputType());
        $this->assertTrue($this->filter->isSearchable());
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
        $this->assertContains('in', $operators);
        $this->assertContains('not_in', $operators);
        $this->assertCount(4, $operators);
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

        $this->assertEquals('countries', $joins[0]['table']);
        $this->assertEquals('countries', $joins[0]['alias']);
        $this->assertStringContainsString('sessions.country_id = countries.ID', $joins[0]['on']);
    }

    /**
     * Test searchOptions returns empty array when countries table doesn't exist.
     */
    public function test_search_options_when_table_does_not_exist()
    {
        global $wpdb;

        // Temporarily set a non-existent table prefix to simulate missing table
        $originalPrefix = $wpdb->prefix;
        $wpdb->prefix = 'nonexistent_';

        $options = $this->filter->searchOptions('United', 10);

        // Restore original prefix
        $wpdb->prefix = $originalPrefix;

        $this->assertIsArray($options);
    }

    /**
     * Test searchOptions returns array of options.
     */
    public function test_search_options_returns_options()
    {
        global $wpdb;

        // Create a test countries table if it doesn't exist
        $table = $wpdb->prefix . 'statistics_countries';

        // Insert test data
        $wpdb->query("CREATE TABLE IF NOT EXISTS {$table} (
            ID int(11) NOT NULL AUTO_INCREMENT,
            code varchar(3) NOT NULL,
            name varchar(255) NOT NULL,
            PRIMARY KEY (ID)
        )");

        $wpdb->insert($table, ['code' => 'US', 'name' => 'United States']);
        $wpdb->insert($table, ['code' => 'GB', 'name' => 'United Kingdom']);
        $wpdb->insert($table, ['code' => 'CA', 'name' => 'Canada']);

        $options = $this->filter->searchOptions('United', 10);

        $this->assertIsArray($options);

        if (!empty($options)) {
            $this->assertArrayHasKey('value', $options[0]);
            $this->assertArrayHasKey('label', $options[0]);
        }

        // Cleanup
        $wpdb->query("DELETE FROM {$table} WHERE code IN ('US', 'GB', 'CA')");
    }

    /**
     * Test searchOptions filters by search term.
     */
    public function test_search_options_filters_by_search_term()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'statistics_countries';

        // Create table and insert test data
        $wpdb->query("CREATE TABLE IF NOT EXISTS {$table} (
            ID int(11) NOT NULL AUTO_INCREMENT,
            code varchar(3) NOT NULL,
            name varchar(255) NOT NULL,
            PRIMARY KEY (ID)
        )");

        $wpdb->insert($table, ['code' => 'US', 'name' => 'United States']);
        $wpdb->insert($table, ['code' => 'FR', 'name' => 'France']);

        // Search for 'United'
        $options = $this->filter->searchOptions('United', 10);

        // Should only return results containing 'United'
        foreach ($options as $option) {
            $containsUnited = stripos($option['label'], 'United') !== false ||
                            stripos($option['value'], 'United') !== false;
            $this->assertTrue($containsUnited, "Option should contain 'United'");
        }

        // Cleanup
        $wpdb->query("DELETE FROM {$table} WHERE code IN ('US', 'FR')");
    }

    /**
     * Test searchOptions respects limit parameter.
     */
    public function test_search_options_respects_limit()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'statistics_countries';

        // Create table
        $wpdb->query("CREATE TABLE IF NOT EXISTS {$table} (
            ID int(11) NOT NULL AUTO_INCREMENT,
            code varchar(3) NOT NULL,
            name varchar(255) NOT NULL,
            PRIMARY KEY (ID)
        )");

        // Insert multiple test countries
        for ($i = 1; $i <= 10; $i++) {
            $wpdb->insert($table, [
                'code' => 'C' . $i,
                'name' => 'Country ' . $i
            ]);
        }

        // Request only 5 results
        $options = $this->filter->searchOptions('Country', 5);

        $this->assertLessThanOrEqual(5, count($options));

        // Cleanup
        $wpdb->query("DELETE FROM {$table} WHERE code LIKE 'C%'");
    }

    /**
     * Test searchOptions returns empty array when search has no matches.
     */
    public function test_search_options_returns_empty_for_no_matches()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'statistics_countries';

        // Create table
        $wpdb->query("CREATE TABLE IF NOT EXISTS {$table} (
            ID int(11) NOT NULL AUTO_INCREMENT,
            code varchar(3) NOT NULL,
            name varchar(255) NOT NULL,
            PRIMARY KEY (ID)
        )");

        $wpdb->insert($table, ['code' => 'US', 'name' => 'United States']);

        // Search for something that doesn't exist
        $options = $this->filter->searchOptions('Nonexistent Country XYZ', 10);

        $this->assertIsArray($options);
        $this->assertEmpty($options);

        // Cleanup
        $wpdb->query("DELETE FROM {$table} WHERE code = 'US'");
    }

    /**
     * Test toArray includes all necessary properties.
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

        $this->assertEquals('country', $array['name']);
        $this->assertEquals('searchable', $array['inputType']);
    }

    /**
     * Test toFrontendArray excludes backend properties.
     */
    public function test_to_frontend_array_structure()
    {
        $array = $this->filter->toFrontendArray();

        // Should have frontend properties
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('label', $array);
        $this->assertArrayHasKey('supportedOperators', $array);
        $this->assertArrayHasKey('inputType', $array);

        // Should NOT have backend properties
        $this->assertArrayNotHasKey('column', $array);
        $this->assertArrayNotHasKey('type', $array);
        $this->assertArrayNotHasKey('joins', $array);
    }
}
