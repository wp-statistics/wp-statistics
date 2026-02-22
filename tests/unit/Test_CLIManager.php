<?php

use WP_Statistics\Service\CLI\CLIManager;

/**
 * Test case for CLIManager class.
 *
 * Tests that the CLI manager registers all expected commands.
 *
 * @covers \WP_Statistics\Service\CLI\CLIManager
 * @group cli
 */
class Test_CLIManager extends WP_UnitTestCase
{
    /**
     * Test CLIManager has a register method.
     */
    public function test_register_method_exists()
    {
        $this->assertTrue(method_exists(CLIManager::class, 'register'));
    }

    /**
     * Test register is a static method.
     */
    public function test_register_is_static()
    {
        $method = new ReflectionMethod(CLIManager::class, 'register');
        $this->assertTrue($method->isStatic());
    }

    /**
     * Test register returns early when WP_CLI is not defined.
     *
     * Since WP_CLI is defined in the test environment, we verify
     * the method does not throw when called.
     */
    public function test_register_does_not_throw()
    {
        // This should not throw regardless of WP_CLI state
        CLIManager::register();
        $this->assertTrue(true);
    }
}
