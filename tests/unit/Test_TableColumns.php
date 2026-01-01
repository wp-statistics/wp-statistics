<?php

namespace WP_Statistics\Tests;

use WP_Statistics\Service\AnalyticsQuery\Schema\TableColumns;
use WP_UnitTestCase;

/**
 * Test TableColumns class for column definitions.
 */
class Test_TableColumns extends WP_UnitTestCase
{
    /**
     * Test getColumns returns correct columns for countries table.
     */
    public function test_get_columns_for_countries()
    {
        $columns = TableColumns::getColumns('countries');

        $this->assertArrayHasKey('country_id', $columns);
        $this->assertArrayHasKey('country_code', $columns);
        $this->assertArrayHasKey('country_name', $columns);
        $this->assertArrayHasKey('country_continent_code', $columns);
        $this->assertArrayHasKey('country_continent', $columns);

        $this->assertEquals('countries.ID', $columns['country_id']);
        $this->assertEquals('countries.code', $columns['country_code']);
        $this->assertEquals('countries.name', $columns['country_name']);
    }

    /**
     * Test getColumns returns correct columns for cities table.
     */
    public function test_get_columns_for_cities()
    {
        $columns = TableColumns::getColumns('cities');

        $this->assertArrayHasKey('city_id', $columns);
        $this->assertArrayHasKey('city_name', $columns);
        $this->assertArrayHasKey('city_region_code', $columns);
        $this->assertArrayHasKey('city_region_name', $columns);
        $this->assertArrayHasKey('city_country_id', $columns);

        $this->assertEquals('cities.ID', $columns['city_id']);
        $this->assertEquals('cities.city_name', $columns['city_name']);
    }

    /**
     * Test getColumns returns correct columns for device_types table.
     */
    public function test_get_columns_for_device_types()
    {
        $columns = TableColumns::getColumns('device_types');

        $this->assertArrayHasKey('device_type_id', $columns);
        $this->assertArrayHasKey('device_type_name', $columns);

        $this->assertEquals('device_types.ID', $columns['device_type_id']);
        $this->assertEquals('device_types.name', $columns['device_type_name']);
    }

    /**
     * Test getColumns returns correct columns for device_browsers table.
     */
    public function test_get_columns_for_device_browsers()
    {
        $columns = TableColumns::getColumns('device_browsers');

        $this->assertArrayHasKey('browser_id', $columns);
        $this->assertArrayHasKey('browser_name', $columns);

        $this->assertEquals('device_browsers.ID', $columns['browser_id']);
        $this->assertEquals('device_browsers.name', $columns['browser_name']);
    }

    /**
     * Test getColumns returns correct columns for device_oss table.
     */
    public function test_get_columns_for_device_oss()
    {
        $columns = TableColumns::getColumns('device_oss');

        $this->assertArrayHasKey('os_id', $columns);
        $this->assertArrayHasKey('os_name', $columns);

        $this->assertEquals('device_oss.ID', $columns['os_id']);
        $this->assertEquals('device_oss.name', $columns['os_name']);
    }

    /**
     * Test getColumns returns correct columns for referrers table.
     */
    public function test_get_columns_for_referrers()
    {
        $columns = TableColumns::getColumns('referrers');

        $this->assertArrayHasKey('referrer_id', $columns);
        $this->assertArrayHasKey('referrer_channel', $columns);
        $this->assertArrayHasKey('referrer_name', $columns);
        $this->assertArrayHasKey('referrer_domain', $columns);

        $this->assertEquals('referrers.ID', $columns['referrer_id']);
        $this->assertEquals('referrers.channel', $columns['referrer_channel']);
    }

    /**
     * Test getColumns returns correct columns for languages table.
     */
    public function test_get_columns_for_languages()
    {
        $columns = TableColumns::getColumns('languages');

        $this->assertArrayHasKey('language_id', $columns);
        $this->assertArrayHasKey('language_code', $columns);
        $this->assertArrayHasKey('language_name', $columns);
        $this->assertArrayHasKey('language_region', $columns);

        $this->assertEquals('languages.ID', $columns['language_id']);
        $this->assertEquals('languages.name', $columns['language_name']);
    }

    /**
     * Test getColumns returns correct columns for resolutions table.
     */
    public function test_get_columns_for_resolutions()
    {
        $columns = TableColumns::getColumns('resolutions');

        $this->assertArrayHasKey('resolution_id', $columns);
        $this->assertArrayHasKey('resolution_width', $columns);
        $this->assertArrayHasKey('resolution_height', $columns);

        $this->assertEquals('resolutions.ID', $columns['resolution_id']);
        $this->assertEquals('resolutions.width', $columns['resolution_width']);
        $this->assertEquals('resolutions.height', $columns['resolution_height']);
    }

    /**
     * Test getColumns returns correct columns for sessions table.
     */
    public function test_get_columns_for_sessions()
    {
        $columns = TableColumns::getColumns('sessions');

        $this->assertArrayHasKey('session_id', $columns);
        $this->assertArrayHasKey('session_visitor_id', $columns);
        $this->assertArrayHasKey('session_country_id', $columns);
        $this->assertArrayHasKey('session_started_at', $columns);
        $this->assertArrayHasKey('session_duration', $columns);

        $this->assertEquals('sessions.ID', $columns['session_id']);
        $this->assertEquals('sessions.started_at', $columns['session_started_at']);
    }

    /**
     * Test getColumns returns correct columns for views table.
     */
    public function test_get_columns_for_views()
    {
        $columns = TableColumns::getColumns('views');

        $this->assertArrayHasKey('view_id', $columns);
        $this->assertArrayHasKey('view_session_id', $columns);
        $this->assertArrayHasKey('view_resource_uri_id', $columns);
        $this->assertArrayHasKey('view_viewed_at', $columns);
        $this->assertArrayHasKey('view_duration', $columns);

        $this->assertEquals('views.ID', $columns['view_id']);
        $this->assertEquals('views.viewed_at', $columns['view_viewed_at']);
    }

    /**
     * Test getColumns returns correct columns for resources table.
     */
    public function test_get_columns_for_resources()
    {
        $columns = TableColumns::getColumns('resources');

        $this->assertArrayHasKey('resource_id', $columns);
        $this->assertArrayHasKey('resource_type', $columns);
        $this->assertArrayHasKey('resource_wp_id', $columns);
        $this->assertArrayHasKey('resource_cached_title', $columns);

        $this->assertEquals('resources.ID', $columns['resource_id']);
        $this->assertEquals('resources.resource_type', $columns['resource_type']);
    }

    /**
     * Test getColumns returns empty array for unknown table.
     */
    public function test_get_columns_for_unknown_table()
    {
        $columns = TableColumns::getColumns('unknown_table');

        $this->assertIsArray($columns);
        $this->assertEmpty($columns);
    }

    /**
     * Test getColumn returns correct expression for specific alias.
     */
    public function test_get_column_returns_expression()
    {
        $expression = TableColumns::getColumn('countries', 'country_name');

        $this->assertEquals('countries.name', $expression);
    }

    /**
     * Test getColumn returns null for unknown alias.
     */
    public function test_get_column_returns_null_for_unknown_alias()
    {
        $expression = TableColumns::getColumn('countries', 'unknown_alias');

        $this->assertNull($expression);
    }

    /**
     * Test getColumnWithAlias returns expression with alias.
     */
    public function test_get_column_with_alias()
    {
        $result = TableColumns::getColumnWithAlias('countries', 'country_name');

        $this->assertEquals('countries.name AS country_name', $result);
    }

    /**
     * Test getColumnWithAlias returns null for unknown alias.
     */
    public function test_get_column_with_alias_returns_null_for_unknown()
    {
        $result = TableColumns::getColumnWithAlias('countries', 'unknown_alias');

        $this->assertNull($result);
    }

    /**
     * Test getColumnsWithAliases returns multiple columns.
     */
    public function test_get_columns_with_aliases()
    {
        $result = TableColumns::getColumnsWithAliases('countries', ['country_name', 'country_code']);

        $this->assertCount(2, $result);
        $this->assertContains('countries.name AS country_name', $result);
        $this->assertContains('countries.code AS country_code', $result);
    }

    /**
     * Test getColumnsWithAliases skips unknown aliases.
     */
    public function test_get_columns_with_aliases_skips_unknown()
    {
        $result = TableColumns::getColumnsWithAliases('countries', ['country_name', 'unknown', 'country_code']);

        $this->assertCount(2, $result);
        $this->assertContains('countries.name AS country_name', $result);
        $this->assertContains('countries.code AS country_code', $result);
    }

    /**
     * Test getAliases returns all aliases for a table.
     */
    public function test_get_aliases()
    {
        $aliases = TableColumns::getAliases('countries');

        $this->assertContains('country_id', $aliases);
        $this->assertContains('country_code', $aliases);
        $this->assertContains('country_name', $aliases);
        $this->assertContains('country_continent_code', $aliases);
        $this->assertContains('country_continent', $aliases);
    }

    /**
     * Test all column aliases follow naming convention.
     */
    public function test_all_aliases_follow_naming_convention()
    {
        $tables = [
            'countries',
            'cities',
            'device_types',
            'device_browsers',
            'device_oss',
            'referrers',
            'languages',
            'resolutions',
            'sessions',
            'views',
            'resources',
        ];

        foreach ($tables as $table) {
            $aliases = TableColumns::getAliases($table);

            foreach ($aliases as $alias) {
                // All aliases should be lowercase with underscores
                $this->assertMatchesRegularExpression(
                    '/^[a-z_]+$/',
                    $alias,
                    "Alias '{$alias}' in table '{$table}' should be lowercase with underscores"
                );
            }
        }
    }

    /**
     * Test column expressions use correct table prefixes.
     */
    public function test_column_expressions_have_table_prefixes()
    {
        $tables = [
            'countries'       => 'countries.',
            'cities'          => 'cities.',
            'device_types'    => 'device_types.',
            'device_browsers' => 'device_browsers.',
            'device_oss'      => 'device_oss.',
            'referrers'       => 'referrers.',
            'languages'       => 'languages.',
            'resolutions'     => 'resolutions.',
            'sessions'        => 'sessions.',
            'views'           => 'views.',
            'resources'       => 'resources.',
        ];

        foreach ($tables as $table => $expectedPrefix) {
            $columns = TableColumns::getColumns($table);

            foreach ($columns as $alias => $expression) {
                $this->assertStringStartsWith(
                    $expectedPrefix,
                    $expression,
                    "Expression for '{$alias}' should start with '{$expectedPrefix}'"
                );
            }
        }
    }
}
