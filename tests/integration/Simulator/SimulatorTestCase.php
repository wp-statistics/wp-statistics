<?php

namespace WP_Statistics\Tests\Integration\Simulator;

use WP_UnitTestCase;

// Load simulator classes from bin/simulator/
$binDir = dirname(__DIR__, 3) . '/bin/simulator';
require_once $binDir . '/SimulatorConfig.php';
require_once $binDir . '/SettingsConfigurator.php';
require_once $binDir . '/ResourceProvisioner.php';
require_once $binDir . '/CheckpointManager.php';
require_once $binDir . '/Generators/AbstractDataGenerator.php';
require_once $binDir . '/Generators/RealisticVisitorGenerator.php';
require_once $binDir . '/Generators/InvalidDataGenerator.php';
require_once $binDir . '/Generators/AttackPayloadGenerator.php';
require_once $binDir . '/Http/CurlMultiSender.php';
require_once $binDir . '/SimulatorRunner.php';

use WP_Statistics\Testing\Simulator\SimulatorConfig;
use WP_Statistics\Testing\Simulator\SimulatorRunner;
use WP_Statistics\Testing\Simulator\SettingsConfigurator;
use WP_Statistics\Testing\Simulator\ResourceProvisioner;
use WP_Statistics\Testing\Simulator\CheckpointManager;
use WP_Statistics\Testing\Simulator\Generators\RealisticVisitorGenerator;
use WP_Statistics\Testing\Simulator\Generators\InvalidDataGenerator;
use WP_Statistics\Testing\Simulator\Generators\AttackPayloadGenerator;

/**
 * Base test case for Simulator tests
 *
 * Provides common setup, teardown, and utility methods for testing
 * the stress test simulator components.
 *
 * @group simulator
 * @group integration
 */
abstract class SimulatorTestCase extends WP_UnitTestCase
{
    /**
     * Path to test data directory
     */
    protected string $dataDir;

    /**
     * Path to temporary checkpoint directory
     */
    protected string $checkpointDir;

    /**
     * Target URL for testing
     */
    protected string $targetUrl;

    /**
     * Test configuration
     */
    protected SimulatorConfig $config;

    /**
     * Original WP Statistics settings (for restoration)
     */
    protected array $originalSettings = [];

    /**
     * Set up test fixtures
     */
    public function setUp(): void
    {
        parent::setUp();

        // Set data directory
        $this->dataDir = dirname(__DIR__, 3) . '/bin/data';

        // Create temporary checkpoint directory
        $this->checkpointDir = sys_get_temp_dir() . '/wp-statistics-test-' . uniqid();
        mkdir($this->checkpointDir, 0755, true);

        // Set target URL
        $this->targetUrl = admin_url('admin-ajax.php');

        // Save original settings
        $this->originalSettings = get_option('wp_statistics_settings', []);

        // Create default test configuration
        $this->config = $this->createTestConfig();
    }

    /**
     * Tear down test fixtures
     */
    public function tearDown(): void
    {
        // Restore original settings
        if (!empty($this->originalSettings)) {
            update_option('wp_statistics_settings', $this->originalSettings);
        }

        // Clean up checkpoint directory
        $this->cleanupDirectory($this->checkpointDir);

        parent::tearDown();
    }

    /**
     * Create a test configuration
     *
     * @param int $targetRecords Number of records
     * @return SimulatorConfig
     */
    protected function createTestConfig(int $targetRecords = 10): SimulatorConfig
    {
        $config = SimulatorConfig::forPHPUnit($this->targetUrl);
        $config->targetRecords = $targetRecords;
        $config->dataDir = $this->dataDir;
        $config->checkpointDir = $this->checkpointDir;
        $config->enableCheckpoints = false;

        return $config;
    }

    /**
     * Create a simulator runner for testing
     *
     * @param SimulatorConfig|null $config Optional configuration
     * @return SimulatorRunner
     */
    protected function createRunner(?SimulatorConfig $config = null): SimulatorRunner
    {
        return new SimulatorRunner($config ?? $this->config);
    }

    /**
     * Create a visitor generator for testing
     *
     * @return RealisticVisitorGenerator
     */
    protected function createVisitorGenerator(): RealisticVisitorGenerator
    {
        // Create ResourceProvisioner for the generator
        $resourceProvisioner = new ResourceProvisioner(
            function (string $message, string $level = 'info') {
                // Silent logger for tests
            },
            $this->config->minPosts ?? 10,
            $this->config->minPages ?? 5,
            $this->config->minUsers ?? 5
        );

        return new RealisticVisitorGenerator($this->dataDir, $this->config, $resourceProvisioner);
    }

    /**
     * Create an invalid data generator for testing
     *
     * @return InvalidDataGenerator
     */
    protected function createInvalidGenerator(): InvalidDataGenerator
    {
        return new InvalidDataGenerator($this->dataDir);
    }

    /**
     * Create an attack payload generator for testing
     *
     * @return AttackPayloadGenerator
     */
    protected function createAttackGenerator(): AttackPayloadGenerator
    {
        return new AttackPayloadGenerator($this->dataDir);
    }

    /**
     * Create a checkpoint manager for testing
     *
     * @param string $identifier Checkpoint identifier
     * @return CheckpointManager
     */
    protected function createCheckpointManager(string $identifier = 'test'): CheckpointManager
    {
        return new CheckpointManager($this->checkpointDir, $identifier);
    }

    /**
     * Assert that a request result was successful
     *
     * @param array $result Result from HTTP sender
     * @param string $message Optional assertion message
     */
    protected function assertRequestSuccess(array $result, string $message = ''): void
    {
        $this->assertEquals('success', $result['status'], $message ?: 'Request should succeed');
        $this->assertGreaterThanOrEqual(200, $result['http_code']);
        $this->assertLessThan(400, $result['http_code']);
    }

    /**
     * Assert that a request was rejected (application-level rejection)
     *
     * @param array $result Result from HTTP sender
     * @param string $message Optional assertion message
     */
    protected function assertRequestRejected(array $result, string $message = ''): void
    {
        $this->assertEquals('rejected', $result['status'], $message ?: 'Request should be rejected');
    }

    /**
     * Assert that a request failed (HTTP-level failure)
     *
     * @param array $result Result from HTTP sender
     * @param string $message Optional assertion message
     */
    protected function assertRequestFailed(array $result, string $message = ''): void
    {
        $this->assertEquals('error', $result['status'], $message ?: 'Request should fail');
    }

    /**
     * Assert that generated data has required fields
     *
     * @param array $data Generated data
     * @param array $requiredFields Required field names
     */
    protected function assertHasFields(array $data, array $requiredFields): void
    {
        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $data, "Missing required field: {$field}");
        }
    }

    /**
     * Assert that a value is a valid base64 string
     *
     * @param string $value Value to check
     * @param string $message Optional assertion message
     */
    protected function assertBase64(string $value, string $message = ''): void
    {
        $decoded = base64_decode($value, true);
        $this->assertNotFalse($decoded, $message ?: 'Value should be valid base64');
    }

    /**
     * Create sample WordPress posts for testing
     *
     * @param int $count Number of posts to create
     * @return array Created post IDs
     */
    protected function createTestPosts(int $count = 5): array
    {
        $postIds = [];

        for ($i = 0; $i < $count; $i++) {
            $postIds[] = $this->factory()->post->create([
                'post_title'  => "Test Post {$i}",
                'post_status' => 'publish',
                'post_type'   => 'post',
            ]);
        }

        return $postIds;
    }

    /**
     * Create sample WordPress pages for testing
     *
     * @param int $count Number of pages to create
     * @return array Created page IDs
     */
    protected function createTestPages(int $count = 3): array
    {
        $pageIds = [];

        for ($i = 0; $i < $count; $i++) {
            $pageIds[] = $this->factory()->post->create([
                'post_title'  => "Test Page {$i}",
                'post_status' => 'publish',
                'post_type'   => 'page',
            ]);
        }

        return $pageIds;
    }

    /**
     * Create test users
     *
     * @param int $count Number of users
     * @return array Created user IDs
     */
    protected function createTestUsers(int $count = 3): array
    {
        $userIds = [];

        for ($i = 0; $i < $count; $i++) {
            $userIds[] = $this->factory()->user->create([
                'role' => 'subscriber',
            ]);
        }

        return $userIds;
    }

    /**
     * Clean up a directory and its contents
     *
     * @param string $dir Directory path
     */
    protected function cleanupDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            if ($fileinfo->isDir()) {
                rmdir($fileinfo->getRealPath());
            } else {
                unlink($fileinfo->getRealPath());
            }
        }

        rmdir($dir);
    }

    /**
     * Skip test if curl is not available
     */
    protected function requireCurl(): void
    {
        if (!function_exists('curl_init')) {
            $this->markTestSkipped('cURL extension is required for this test');
        }
    }

    /**
     * Skip test if running in CI without network
     */
    protected function requireNetwork(): void
    {
        if (getenv('CI') && getenv('NO_NETWORK')) {
            $this->markTestSkipped('Network tests are disabled in CI');
        }
    }
}
