<?php

namespace WP_Statistics\Tests;

use WP_Statistics\Service\AnalyticsQuery\GroupBy\BrowserGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\CityGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\ContinentGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\CountryGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\DateGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\DeviceTypeGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\LanguageGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\OsGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\PageGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\ReferrerGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\ResolutionGroupBy;
use WP_UnitTestCase;

/**
 * Test GroupBy classes for proper column naming.
 */
class Test_AnalyticsQueryGroupBy extends WP_UnitTestCase
{
    /**
     * Test CountryGroupBy returns proper column names.
     */
    public function test_country_group_by_columns()
    {
        $groupBy = new CountryGroupBy();

        $this->assertEquals('country', $groupBy->getName());
        $this->assertEquals('country_name', $groupBy->getAlias());

        $columns = $groupBy->getSelectColumns();
        $columnString = implode(' ', $columns);

        // Check primary column
        $this->assertStringContainsString('AS country_name', $columnString);

        // Check extra columns
        $extraAliases = $groupBy->getExtraColumnAliases();
        $this->assertContains('country_id', $extraAliases);
        $this->assertContains('country_code', $extraAliases);
        $this->assertContains('country_continent_code', $extraAliases);
        $this->assertContains('country_continent', $extraAliases);
    }

    /**
     * Test CityGroupBy returns proper column names.
     */
    public function test_city_group_by_columns()
    {
        $groupBy = new CityGroupBy();

        $this->assertEquals('city', $groupBy->getName());
        $this->assertEquals('city_name', $groupBy->getAlias());

        $extraAliases = $groupBy->getExtraColumnAliases();
        $this->assertContains('city_id', $extraAliases);
        $this->assertContains('city_region_code', $extraAliases);
        $this->assertContains('city_region_name', $extraAliases);
        $this->assertContains('city_country_id', $extraAliases);
        $this->assertContains('country_code', $extraAliases);
        $this->assertContains('country_name', $extraAliases);
    }

    /**
     * Test ContinentGroupBy returns proper column names.
     */
    public function test_continent_group_by_columns()
    {
        $groupBy = new ContinentGroupBy();

        $this->assertEquals('continent', $groupBy->getName());
        $this->assertEquals('continent_name', $groupBy->getAlias());

        $extraAliases = $groupBy->getExtraColumnAliases();
        $this->assertContains('continent_code', $extraAliases);
    }

    /**
     * Test BrowserGroupBy returns proper column names.
     */
    public function test_browser_group_by_columns()
    {
        $groupBy = new BrowserGroupBy();

        $this->assertEquals('browser', $groupBy->getName());
        $this->assertEquals('browser_name', $groupBy->getAlias());

        $extraAliases = $groupBy->getExtraColumnAliases();
        $this->assertContains('browser_id', $extraAliases);
        $this->assertContains('browser_version', $extraAliases);
        $this->assertContains('browser_version_id', $extraAliases);
    }

    /**
     * Test OsGroupBy returns proper column names.
     */
    public function test_os_group_by_columns()
    {
        $groupBy = new OsGroupBy();

        $this->assertEquals('os', $groupBy->getName());
        $this->assertEquals('os_name', $groupBy->getAlias());

        $extraAliases = $groupBy->getExtraColumnAliases();
        $this->assertContains('os_id', $extraAliases);
    }

    /**
     * Test DeviceTypeGroupBy returns proper column names.
     */
    public function test_device_type_group_by_columns()
    {
        $groupBy = new DeviceTypeGroupBy();

        $this->assertEquals('device_type', $groupBy->getName());
        $this->assertEquals('device_type_name', $groupBy->getAlias());

        $extraAliases = $groupBy->getExtraColumnAliases();
        $this->assertContains('device_type_id', $extraAliases);
    }

    /**
     * Test LanguageGroupBy returns proper column names.
     */
    public function test_language_group_by_columns()
    {
        $groupBy = new LanguageGroupBy();

        $this->assertEquals('language', $groupBy->getName());
        $this->assertEquals('language_name', $groupBy->getAlias());

        $extraAliases = $groupBy->getExtraColumnAliases();
        $this->assertContains('language_id', $extraAliases);
        $this->assertContains('language_code', $extraAliases);
        $this->assertContains('language_region', $extraAliases);
    }

    /**
     * Test ReferrerGroupBy returns proper column names.
     */
    public function test_referrer_group_by_columns()
    {
        $groupBy = new ReferrerGroupBy();

        $this->assertEquals('referrer', $groupBy->getName());
        $this->assertEquals('referrer_domain', $groupBy->getAlias());

        $extraAliases = $groupBy->getExtraColumnAliases();
        $this->assertContains('referrer_id', $extraAliases);
        $this->assertContains('referrer_channel', $extraAliases);
        $this->assertContains('referrer_name', $extraAliases);
    }

    /**
     * Test ResolutionGroupBy returns proper column names.
     */
    public function test_resolution_group_by_columns()
    {
        $groupBy = new ResolutionGroupBy();

        $this->assertEquals('resolution', $groupBy->getName());
        $this->assertEquals('resolution', $groupBy->getAlias());

        $extraAliases = $groupBy->getExtraColumnAliases();
        $this->assertContains('resolution_id', $extraAliases);
        $this->assertContains('resolution_width', $extraAliases);
        $this->assertContains('resolution_height', $extraAliases);
    }

    /**
     * Test PageGroupBy returns proper column names.
     */
    public function test_page_group_by_columns()
    {
        $groupBy = new PageGroupBy();

        $this->assertEquals('page', $groupBy->getName());
        $this->assertEquals('page_uri', $groupBy->getAlias());

        $extraAliases = $groupBy->getExtraColumnAliases();
        $this->assertContains('page_uri_id', $extraAliases);
        $this->assertContains('resource_id', $extraAliases);
        $this->assertContains('page_title', $extraAliases);
        $this->assertContains('page_wp_id', $extraAliases);
        $this->assertContains('page_type', $extraAliases);
    }

    /**
     * Test DateGroupBy returns proper column names.
     */
    public function test_date_group_by_columns()
    {
        $groupBy = new DateGroupBy();

        $this->assertEquals('date', $groupBy->getName());
        $this->assertEquals('date', $groupBy->getAlias());
        $this->assertEquals('ASC', $groupBy->getOrder());
    }

    /**
     * Test all GroupBy classes have consistent naming pattern.
     */
    public function test_all_group_by_classes_follow_naming_convention()
    {
        $groupByClasses = [
            CountryGroupBy::class,
            CityGroupBy::class,
            ContinentGroupBy::class,
            BrowserGroupBy::class,
            OsGroupBy::class,
            DeviceTypeGroupBy::class,
            LanguageGroupBy::class,
            ReferrerGroupBy::class,
            ResolutionGroupBy::class,
            PageGroupBy::class,
            DateGroupBy::class,
        ];

        foreach ($groupByClasses as $class) {
            $groupBy = new $class();

            // Name should be lowercase and use underscores
            $name = $groupBy->getName();
            $this->assertMatchesRegularExpression('/^[a-z_]+$/', $name, "GroupBy name '{$name}' should be lowercase with underscores");

            // Alias should be lowercase and use underscores
            $alias = $groupBy->getAlias();
            $this->assertMatchesRegularExpression('/^[a-z_]+$/', $alias, "GroupBy alias '{$alias}' should be lowercase with underscores");

            // Extra column aliases should follow the same pattern
            foreach ($groupBy->getExtraColumnAliases() as $extraAlias) {
                $this->assertMatchesRegularExpression('/^[a-z_]+$/', $extraAlias, "Extra column alias '{$extraAlias}' should be lowercase with underscores");
            }
        }
    }

    /**
     * Test that GroupBy classes include ID columns for related entities.
     */
    public function test_group_by_classes_include_id_columns()
    {
        $testCases = [
            [CountryGroupBy::class, 'country_id'],
            [CityGroupBy::class, 'city_id'],
            [BrowserGroupBy::class, 'browser_id'],
            [OsGroupBy::class, 'os_id'],
            [DeviceTypeGroupBy::class, 'device_type_id'],
            [LanguageGroupBy::class, 'language_id'],
            [ReferrerGroupBy::class, 'referrer_id'],
            [ResolutionGroupBy::class, 'resolution_id'],
            [PageGroupBy::class, 'page_uri_id'],
        ];

        foreach ($testCases as [$class, $expectedIdColumn]) {
            $groupBy = new $class();
            $extraAliases = $groupBy->getExtraColumnAliases();

            $this->assertContains(
                $expectedIdColumn,
                $extraAliases,
                "GroupBy class {$class} should include {$expectedIdColumn} in extra columns"
            );
        }
    }
}
