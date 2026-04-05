<?php

use WP_Statistics\Service\CLI\Commands\TrackCommand;

/**
 * Test case for TrackCommand class.
 *
 * @covers \WP_Statistics\Service\CLI\Commands\TrackCommand
 * @group cli
 */
class Test_TrackCommand extends WP_UnitTestCase
{
    /**
     * Test TrackCommand can be instantiated.
     */
    public function test_command_instantiation()
    {
        $command = new TrackCommand();
        $this->assertInstanceOf(TrackCommand::class, $command);
    }

    /**
     * Test __invoke method exists and is callable.
     */
    public function test_invoke_method_exists()
    {
        $command = new TrackCommand();
        $this->assertTrue(method_exists($command, '__invoke'));
    }

    /**
     * Test __invoke is callable (implements WP-CLI single-command pattern).
     */
    public function test_invoke_is_callable()
    {
        $command = new TrackCommand();
        $this->assertIsCallable([$command, '__invoke']);
    }
}
