<?php

namespace WP_Statistics\Tests\Tools;

use WP_UnitTestCase;
use WP_Statistics\Service\Admin\Tools\SystemInfoService;

/**
 * Tests for SystemInfoService.
 */
class Test_SystemInfoService extends WP_UnitTestCase
{
    public function test_returns_tables()
    {
        $service = new SystemInfoService();
        $tables  = $service->getTables();

        $this->assertIsArray($tables);
        if (!empty($tables)) {
            $this->assertArrayHasKey('key', $tables[0]);
            $this->assertArrayHasKey('name', $tables[0]);
            $this->assertArrayHasKey('records', $tables[0]);
            $this->assertArrayHasKey('size', $tables[0]);
            $this->assertArrayHasKey('engine', $tables[0]);
            $this->assertArrayHasKey('description', $tables[0]);
        }
    }

    public function test_table_entries_have_all_fields()
    {
        $service = new SystemInfoService();
        $tables  = $service->getTables();

        if (!empty($tables)) {
            foreach ($tables as $table) {
                $this->assertArrayHasKey('key', $table);
                $this->assertArrayHasKey('name', $table);
                $this->assertArrayHasKey('description', $table);
                $this->assertArrayHasKey('records', $table);
                $this->assertArrayHasKey('size', $table);
                $this->assertArrayHasKey('engine', $table);
                $this->assertArrayHasKey('isLegacy', $table);
                $this->assertArrayHasKey('isAddon', $table);
                $this->assertArrayHasKey('addonName', $table);
            }
        }
    }

    public function test_returns_plugin_info()
    {
        $service = new SystemInfoService();
        $info    = $service->getPluginInfo();

        $this->assertArrayHasKey('version', $info);
        $this->assertArrayHasKey('db_version', $info);
        $this->assertArrayHasKey('php', $info);
        $this->assertArrayHasKey('wp', $info);
        $this->assertArrayHasKey('mysql', $info);
    }

    public function test_plugin_version_matches_constant()
    {
        $service = new SystemInfoService();
        $info    = $service->getPluginInfo();

        $this->assertEquals(WP_STATISTICS_VERSION, $info['version']);
    }

    public function test_php_version_matches_runtime()
    {
        $service = new SystemInfoService();
        $info    = $service->getPluginInfo();

        $this->assertEquals(PHP_VERSION, $info['php']);
    }
}
