<?php

namespace WP_Statistics\Tests\Tools;

use WP_UnitTestCase;
use WP_Statistics\Service\Admin\Tools\BackgroundJobRegistry;

/**
 * Tests for BackgroundJobRegistry.
 */
class Test_BackgroundJobRegistry extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        remove_all_filters('wp_statistics_background_jobs');
    }

    public function tearDown(): void
    {
        remove_all_filters('wp_statistics_background_jobs');
        parent::tearDown();
    }

    public function test_returns_jobs()
    {
        $registry = new BackgroundJobRegistry();
        $jobs     = $registry->getAll();

        $this->assertIsArray($jobs);
        $this->assertNotEmpty($jobs);

        $keys = array_column($jobs, 'key');
        $this->assertContains('update_unknown_visitor_geoip', $keys);
        $this->assertContains('calculate_daily_summary', $keys);
    }

    public function test_returns_six_core_jobs()
    {
        $registry = new BackgroundJobRegistry();
        $jobs     = $registry->getAll();

        $this->assertCount(6, $jobs);
    }

    public function test_get_single_job()
    {
        $registry = new BackgroundJobRegistry();
        $job      = $registry->getJob('calculate_daily_summary');

        $this->assertNotNull($job);
        $this->assertEquals('calculate_daily_summary', $job['key']);
        $this->assertArrayHasKey('status', $job);
        $this->assertArrayHasKey('label', $job);
        $this->assertArrayHasKey('description', $job);
        $this->assertArrayHasKey('progress', $job);
    }

    public function test_job_status_is_valid()
    {
        $registry = new BackgroundJobRegistry();
        $jobs     = $registry->getAll();

        foreach ($jobs as $job) {
            $this->assertContains($job['status'], ['idle', 'running'], "Job '{$job['key']}' has invalid status");
        }
    }

    public function test_idle_jobs_have_null_progress()
    {
        $registry = new BackgroundJobRegistry();
        $jobs     = $registry->getAll();

        foreach ($jobs as $job) {
            if ($job['status'] === 'idle') {
                $this->assertNull($job['progress'], "Idle job '{$job['key']}' should have null progress");
            }
        }
    }

    public function test_returns_null_for_unknown()
    {
        $registry = new BackgroundJobRegistry();
        $this->assertNull($registry->getJob('nonexistent_job'));
    }

    public function test_get_definitions()
    {
        $registry    = new BackgroundJobRegistry();
        $definitions = $registry->getDefinitions();

        $this->assertIsArray($definitions);
        $this->assertArrayHasKey('update_unknown_visitor_geoip', $definitions);
        $this->assertArrayHasKey('label', $definitions['update_unknown_visitor_geoip']);
        $this->assertArrayHasKey('description', $definitions['update_unknown_visitor_geoip']);
        $this->assertArrayHasKey('optionKey', $definitions['update_unknown_visitor_geoip']);
    }

    public function test_filter_adds_jobs()
    {
        add_filter('wp_statistics_background_jobs', function ($definitions) {
            $definitions['premium_sync'] = [
                'label'       => 'Premium Sync',
                'description' => 'Syncs premium data.',
                'optionKey'   => null,
            ];
            return $definitions;
        });

        $registry = new BackgroundJobRegistry();
        $jobs     = $registry->getAll();
        $keys     = array_column($jobs, 'key');

        $this->assertContains('premium_sync', $keys);
        $this->assertContains('update_unknown_visitor_geoip', $keys);
        $this->assertCount(7, $jobs); // 6 core + 1 premium
    }

    public function test_filter_can_remove_core_jobs()
    {
        add_filter('wp_statistics_background_jobs', function ($definitions) {
            unset($definitions['geolocation_database_download']);
            return $definitions;
        });

        $registry = new BackgroundJobRegistry();
        $jobs     = $registry->getAll();
        $keys     = array_column($jobs, 'key');

        $this->assertNotContains('geolocation_database_download', $keys);
        $this->assertCount(5, $jobs);
    }

    public function test_filter_replaces_job_definition()
    {
        add_filter('wp_statistics_background_jobs', function ($definitions) {
            $definitions['calculate_daily_summary']['label'] = 'Custom Label';
            return $definitions;
        });

        $registry = new BackgroundJobRegistry();
        $job      = $registry->getJob('calculate_daily_summary');

        $this->assertEquals('Custom Label', $job['label']);
    }
}
