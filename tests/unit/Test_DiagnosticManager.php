<?php

use WP_Statistics\Service\Admin\Diagnostic\DiagnosticManager;
use WP_Statistics\Service\Admin\Diagnostic\DiagnosticResult;
use WP_Statistics\Service\Admin\Diagnostic\Checks\CheckInterface;
use WP_Statistics\Service\Admin\Diagnostic\Checks\AbstractCheck;

/**
 * Mock check for testing purposes.
 */
class Mock_Passing_Check extends AbstractCheck
{
    public function getKey(): string
    {
        return 'mock_pass';
    }

    public function getLabel(): string
    {
        return 'Mock Passing Check';
    }

    public function getDescription(): string
    {
        return 'A mock check that always passes.';
    }

    public function run(): DiagnosticResult
    {
        return $this->pass('Mock check passed successfully.');
    }

    public function isLightweight(): bool
    {
        return true;
    }
}

/**
 * Mock check that fails.
 */
class Mock_Failing_Check extends AbstractCheck
{
    public function getKey(): string
    {
        return 'mock_fail';
    }

    public function getLabel(): string
    {
        return 'Mock Failing Check';
    }

    public function getDescription(): string
    {
        return 'A mock check that always fails.';
    }

    public function run(): DiagnosticResult
    {
        return $this->fail('Mock check failed.', ['error' => 'test_error']);
    }

    public function isLightweight(): bool
    {
        return false;
    }

    public function getHelpUrl(): ?string
    {
        return 'https://example.com/help';
    }
}

/**
 * Mock check that warns.
 */
class Mock_Warning_Check extends AbstractCheck
{
    public function getKey(): string
    {
        return 'mock_warning';
    }

    public function getLabel(): string
    {
        return 'Mock Warning Check';
    }

    public function getDescription(): string
    {
        return 'A mock check that always warns.';
    }

    public function run(): DiagnosticResult
    {
        return $this->warning('Mock check has warnings.');
    }

    public function isLightweight(): bool
    {
        return true;
    }
}

/**
 * Test case for DiagnosticManager class.
 *
 * @covers \WP_Statistics\Service\Admin\Diagnostic\DiagnosticManager
 */
class Test_DiagnosticManager extends WP_UnitTestCase
{
    /**
     * @var DiagnosticManager
     */
    private $manager;

    public function setUp(): void
    {
        parent::setUp();
        $this->manager = new DiagnosticManager();

        // Clear any cached transients
        delete_transient('wps_diagnostic_lightweight');
        delete_transient('wps_diagnostic_full');
    }

    public function tearDown(): void
    {
        parent::tearDown();

        // Clear transients after tests
        delete_transient('wps_diagnostic_lightweight');
        delete_transient('wps_diagnostic_full');
    }

    /**
     * Test getCheckKeys returns registered check keys.
     */
    public function test_get_check_keys_returns_array()
    {
        $keys = $this->manager->getCheckKeys();

        $this->assertIsArray($keys);
        $this->assertNotEmpty($keys);
    }

    /**
     * Test hasCheck method.
     */
    public function test_has_check_returns_true_for_registered_checks()
    {
        $keys = $this->manager->getCheckKeys();

        if (!empty($keys)) {
            $this->assertTrue($this->manager->hasCheck($keys[0]));
        }

        $this->assertFalse($this->manager->hasCheck('non_existent_check'));
    }

    /**
     * Test getCheck returns CheckInterface instance.
     */
    public function test_get_check_returns_check_interface()
    {
        $keys = $this->manager->getCheckKeys();

        if (!empty($keys)) {
            $check = $this->manager->getCheck($keys[0]);
            $this->assertInstanceOf(CheckInterface::class, $check);
        }
    }

    /**
     * Test getCheck returns null for non-existent check.
     */
    public function test_get_check_returns_null_for_invalid_key()
    {
        $this->assertNull($this->manager->getCheck('non_existent'));
    }

    /**
     * Test registerCheck adds a custom check.
     */
    public function test_register_check_adds_custom_check()
    {
        $this->manager->registerCheck('mock_pass', Mock_Passing_Check::class);

        $this->assertTrue($this->manager->hasCheck('mock_pass'));

        $check = $this->manager->getCheck('mock_pass');
        $this->assertInstanceOf(Mock_Passing_Check::class, $check);
    }

    /**
     * Test runCheck returns DiagnosticResult.
     */
    public function test_run_check_returns_diagnostic_result()
    {
        $this->manager->registerCheck('mock_pass', Mock_Passing_Check::class);

        $result = $this->manager->runCheck('mock_pass');

        $this->assertInstanceOf(DiagnosticResult::class, $result);
        $this->assertEquals('mock_pass', $result->key);
        $this->assertEquals(DiagnosticResult::STATUS_PASS, $result->status);
    }

    /**
     * Test runCheck returns null for invalid key.
     */
    public function test_run_check_returns_null_for_invalid_key()
    {
        $this->assertNull($this->manager->runCheck('non_existent'));
    }

    /**
     * Test getLightweightCheckKeys returns only lightweight checks.
     */
    public function test_get_lightweight_check_keys()
    {
        // Register both lightweight and heavy checks
        $this->manager->registerCheck('mock_pass', Mock_Passing_Check::class);
        $this->manager->registerCheck('mock_fail', Mock_Failing_Check::class);

        $lightweightKeys = $this->manager->getLightweightCheckKeys();

        $this->assertContains('mock_pass', $lightweightKeys);
        $this->assertNotContains('mock_fail', $lightweightKeys);
    }

    /**
     * Test getHeavyCheckKeys returns only heavy checks.
     */
    public function test_get_heavy_check_keys()
    {
        // Register both lightweight and heavy checks
        $this->manager->registerCheck('mock_pass', Mock_Passing_Check::class);
        $this->manager->registerCheck('mock_fail', Mock_Failing_Check::class);

        $heavyKeys = $this->manager->getHeavyCheckKeys();

        $this->assertContains('mock_fail', $heavyKeys);
        $this->assertNotContains('mock_pass', $heavyKeys);
    }

    /**
     * Test getFailedChecks returns only failed results.
     */
    public function test_get_failed_checks()
    {
        // Create a fresh manager with only mock checks
        $manager = new DiagnosticManager();

        // Use reflection to clear default checks and add only our mocks
        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('checkClasses');
        $property->setAccessible(true);
        $property->setValue($manager, [
            'mock_pass' => Mock_Passing_Check::class,
            'mock_fail' => Mock_Failing_Check::class,
        ]);

        // Run all checks
        $manager->runAll(true);

        $failed = $manager->getFailedChecks();

        $this->assertCount(1, $failed);
        $this->assertArrayHasKey('mock_fail', $failed);
    }

    /**
     * Test getWarningChecks returns only warning results.
     */
    public function test_get_warning_checks()
    {
        // Create a fresh manager with only mock checks
        $manager = new DiagnosticManager();

        // Use reflection to clear default checks and add only our mocks
        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('checkClasses');
        $property->setAccessible(true);
        $property->setValue($manager, [
            'mock_pass'    => Mock_Passing_Check::class,
            'mock_warning' => Mock_Warning_Check::class,
        ]);

        // Run all checks
        $manager->runAll(true);

        $warnings = $manager->getWarningChecks();

        $this->assertCount(1, $warnings);
        $this->assertArrayHasKey('mock_warning', $warnings);
    }

    /**
     * Test hasFailures returns true when there are failed checks.
     */
    public function test_has_failures()
    {
        $manager = new DiagnosticManager();

        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('checkClasses');
        $property->setAccessible(true);
        $property->setValue($manager, [
            'mock_fail' => Mock_Failing_Check::class,
        ]);

        $manager->runAll(true);

        $this->assertTrue($manager->hasFailures());
    }

    /**
     * Test hasIssues returns true when there are warnings or failures.
     */
    public function test_has_issues()
    {
        $manager = new DiagnosticManager();

        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('checkClasses');
        $property->setAccessible(true);
        $property->setValue($manager, [
            'mock_warning' => Mock_Warning_Check::class,
        ]);

        $manager->runAll(true);

        $this->assertTrue($manager->hasIssues());
    }

    /**
     * Test runAll returns array of DiagnosticResult.
     */
    public function test_run_all_returns_results()
    {
        $manager = new DiagnosticManager();

        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('checkClasses');
        $property->setAccessible(true);
        $property->setValue($manager, [
            'mock_pass' => Mock_Passing_Check::class,
        ]);

        $results = $manager->runAll(true);

        $this->assertIsArray($results);
        $this->assertArrayHasKey('mock_pass', $results);
        $this->assertInstanceOf(DiagnosticResult::class, $results['mock_pass']);
    }

    /**
     * Test clearCache removes cached results.
     */
    public function test_clear_cache()
    {
        // Set some test data
        set_transient('wps_diagnostic_lightweight', ['test' => 'data']);
        set_transient('wps_diagnostic_full', ['test' => 'data']);

        $this->manager->clearCache();

        $this->assertFalse(get_transient('wps_diagnostic_lightweight'));
        $this->assertFalse(get_transient('wps_diagnostic_full'));
    }

    /**
     * Test exception handling in runCheck.
     */
    public function test_run_check_handles_exceptions()
    {
        // Create a check that throws an exception
        $exceptionCheckClass = new class extends AbstractCheck {
            public function getKey(): string
            {
                return 'exception_check';
            }

            public function getLabel(): string
            {
                return 'Exception Check';
            }

            public function getDescription(): string
            {
                return 'A check that throws an exception.';
            }

            public function run(): DiagnosticResult
            {
                throw new Exception('Test exception');
            }
        };

        // Register via reflection
        $reflection = new ReflectionClass($this->manager);
        $checksProperty = $reflection->getProperty('checks');
        $checksProperty->setAccessible(true);
        $checksProperty->setValue($this->manager, ['exception_check' => $exceptionCheckClass]);

        $classesProperty = $reflection->getProperty('checkClasses');
        $classesProperty->setAccessible(true);
        $classes = $classesProperty->getValue($this->manager);
        $classes['exception_check'] = get_class($exceptionCheckClass);
        $classesProperty->setValue($this->manager, $classes);

        $result = $this->manager->runCheck('exception_check');

        $this->assertInstanceOf(DiagnosticResult::class, $result);
        $this->assertEquals(DiagnosticResult::STATUS_FAIL, $result->status);
        $this->assertStringContainsString('Test exception', $result->message);
    }
}
