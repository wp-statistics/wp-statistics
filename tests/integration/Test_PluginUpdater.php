<?php

use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginUpdater;

/**
 * Test class for PluginUpdater version compatibility handling.
 */
class Test_PluginUpdater extends WP_UnitTestCase
{

    /**
     * Test the adjust_patch_version method for different scenarios using the PluginUpdater class.
     */
    public function test_adjust_patch_version()
    {
        // Create test cases
        $test_cases = [
            ['tested_version' => '6.6', 'wp_version' => '6.6.2', 'expected' => '6.6.2'],
            ['tested_version' => '6.6.1', 'wp_version' => '6.6.2', 'expected' => '6.6.2'],
            ['tested_version' => '5.5', 'wp_version' => '5.5.3', 'expected' => '5.5.3'],
            ['tested_version' => '5.5.1', 'wp_version' => '5.5.0', 'expected' => '5.5.0'],
            ['tested_version' => '4.7', 'wp_version' => '4.7.5', 'expected' => '4.7.5'],
        ];

        // Instantiate the PluginUpdater with dummy parameters
        $pluginUpdater = new PluginUpdater('dummy-slug', '1.0.0', 'dummy-license-key');

        // Loop through test cases
        foreach ($test_cases as $case) {
            // Temporarily set the global WordPress version for testing
            global $wp_version;
            $wp_version = $case['wp_version'];

            // Use the adjustPatchVersion method from PluginUpdater
            $result = $this->invokeMethod($pluginUpdater, 'adjustPatchVersion', [$case['tested_version']]);

            // Assert that the result matches the expected value
            $this->assertEquals($case['expected'], $result, "Failed for tested_version: {$case['tested_version']} and wp_version: {$case['wp_version']}");
        }
    }

    /**
     * Helper method to invoke a private/protected method of a class.
     *
     * @param object $object The instantiated object that we will run the method on.
     * @param string $methodName The name of the method to invoke.
     * @param array $parameters An array of parameters to pass into the method.
     * @return mixed The method's return value.
     * @throws ReflectionException
     */
    private function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method     = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
