<?php

namespace WP_Statistics\Tests\Integration\Simulator;

// Load base test case
require_once __DIR__ . '/SimulatorTestCase.php';

use WP_Statistics\Testing\Simulator\SimulatorConfig;
use WP_Statistics\Testing\Simulator\SimulatorRunner;

/**
 * Test cases for SimulatorRunner
 *
 * @group simulator
 * @group stress
 */
class Test_SimulatorRunner extends SimulatorTestCase
{
    /**
     * Test configuration validation
     */
    public function test_config_validation(): void
    {
        // Valid config
        $runner = $this->createRunner();
        $errors = $runner->validateConfig();
        $this->assertEmpty($errors, 'Valid config should have no errors');

        // Invalid config - no URL
        $badConfig = new SimulatorConfig();
        $badConfig->targetUrl = '';
        $badRunner = new SimulatorRunner($badConfig);
        $errors = $badRunner->validateConfig();
        $this->assertNotEmpty($errors);
        $this->assertContains('Target URL is required', $errors);
    }

    /**
     * Test configuration validation - workers range
     */
    public function test_config_validation_workers_range(): void
    {
        $config = $this->createTestConfig();
        $config->parallelWorkers = 150;

        $runner = new SimulatorRunner($config);
        $errors = $runner->validateConfig();

        $this->assertNotEmpty($errors);
        $foundError = false;
        foreach ($errors as $error) {
            if (strpos($error, 'workers') !== false) {
                $foundError = true;
                break;
            }
        }
        $this->assertTrue($foundError, 'Should report invalid worker count');
    }

    /**
     * Test ratio validation
     */
    public function test_config_validation_ratios(): void
    {
        $config = $this->createTestConfig();
        $config->invalidDataRatio = 0.6;
        $config->attackPayloadRatio = 0.6;

        $runner = new SimulatorRunner($config);
        $errors = $runner->validateConfig();

        $this->assertNotEmpty($errors);
        $foundError = false;
        foreach ($errors as $error) {
            if (strpos($error, 'ratio') !== false) {
                $foundError = true;
                break;
            }
        }
        $this->assertTrue($foundError, 'Should report invalid ratio combination');
    }

    /**
     * Test quick test mode
     *
     * @group slow
     */
    public function test_quick_test_mode(): void
    {
        $this->requireCurl();
        $this->requireNetwork();

        // Create posts for testing
        $this->createTestPosts(3);
        $this->createTestPages(2);

        $config = $this->createTestConfig(100);
        $runner = new SimulatorRunner($config);

        // Silence output
        $runner->setOutputCallback(function () {
        });

        $results = $runner->runQuickTest(5);

        $this->assertArrayHasKey('sent', $results);
        $this->assertEquals(5, $results['sent']);
    }

    /**
     * Test scenario factory
     */
    public function test_scenario_factory(): void
    {
        $scenarios = ['stress', 'security', 'invalid', 'mixed'];

        foreach ($scenarios as $scenario) {
            $runner = SimulatorRunner::forScenario($scenario, $this->targetUrl);
            $config = $runner->getConfig();

            $this->assertInstanceOf(SimulatorConfig::class, $config);
            $this->assertEquals($this->targetUrl, $config->targetUrl);
        }
    }

    /**
     * Test stress scenario configuration
     */
    public function test_stress_scenario_config(): void
    {
        $runner = SimulatorRunner::forScenario('stress', $this->targetUrl);
        $config = $runner->getConfig();

        $this->assertGreaterThan(10000, $config->targetRecords);
        $this->assertGreaterThan(10, $config->parallelWorkers);
    }

    /**
     * Test security scenario configuration
     */
    public function test_security_scenario_config(): void
    {
        $runner = SimulatorRunner::forScenario('security', $this->targetUrl);
        $config = $runner->getConfig();

        $this->assertGreaterThan(0, $config->attackPayloadRatio);
    }

    /**
     * Test invalid scenario configuration
     */
    public function test_invalid_scenario_config(): void
    {
        $runner = SimulatorRunner::forScenario('invalid', $this->targetUrl);
        $config = $runner->getConfig();

        $this->assertEquals(0.5, $config->invalidDataRatio);
    }

    /**
     * Test mixed scenario configuration
     */
    public function test_mixed_scenario_config(): void
    {
        $runner = SimulatorRunner::forScenario('mixed', $this->targetUrl);
        $config = $runner->getConfig();

        $this->assertGreaterThan(0, $config->invalidDataRatio);
        $this->assertGreaterThan(0, $config->attackPayloadRatio);
    }

    /**
     * Test output callback
     */
    public function test_output_callback(): void
    {
        $messages = [];

        $runner = $this->createRunner();
        $runner->setOutputCallback(function ($message, $level) use (&$messages) {
            $messages[] = ['message' => $message, 'level' => $level];
        });

        // Trigger some output by validating
        $runner->validateConfig();

        // Output callback should be callable
        $this->assertIsArray($messages);
    }

    /**
     * Test isRunning state
     */
    public function test_is_running_state(): void
    {
        $runner = $this->createRunner();
        $this->assertFalse($runner->isRunning());
    }

    /**
     * Test get config
     */
    public function test_get_config(): void
    {
        $runner = $this->createRunner();
        $config = $runner->getConfig();

        $this->assertInstanceOf(SimulatorConfig::class, $config);
        $this->assertEquals($this->config, $config);
    }

    /**
     * Test checkpoint manager access before run
     */
    public function test_checkpoint_manager_null_before_run(): void
    {
        $runner = $this->createRunner();
        $this->assertNull($runner->getCheckpointManager());
    }

    /**
     * Test HTTP stats before run
     */
    public function test_http_stats_before_run(): void
    {
        $runner = $this->createRunner();
        $stats = $runner->getHttpStats();

        $this->assertIsArray($stats);
        $this->assertEmpty($stats);
    }
}
