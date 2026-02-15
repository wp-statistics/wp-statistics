<?php

use WP_Statistics\Service\CLI\Commands\DatabaseCommand;

/**
 * Test case for DatabaseCommand class.
 *
 * Tests that all subcommand methods exist and the command can be instantiated.
 *
 * @covers \WP_Statistics\Service\CLI\Commands\DatabaseCommand
 * @group cli
 */
class Test_DatabaseCommand extends WP_UnitTestCase
{
    /**
     * @var DatabaseCommand
     */
    private $command;

    public function setUp(): void
    {
        parent::setUp();
        $this->command = new DatabaseCommand();
    }

    /**
     * Test DatabaseCommand can be instantiated.
     */
    public function test_command_instantiation()
    {
        $this->assertInstanceOf(DatabaseCommand::class, $this->command);
    }

    /**
     * Test tables subcommand method exists.
     */
    public function test_tables_method_exists()
    {
        $this->assertTrue(method_exists($this->command, 'tables'));
    }

    /**
     * Test stats subcommand method exists.
     */
    public function test_stats_method_exists()
    {
        $this->assertTrue(method_exists($this->command, 'stats'));
    }

    /**
     * Test optimize subcommand method exists.
     */
    public function test_optimize_method_exists()
    {
        $this->assertTrue(method_exists($this->command, 'optimize'));
    }

    /**
     * Test reinitialize subcommand method exists.
     */
    public function test_reinitialize_method_exists()
    {
        $this->assertTrue(method_exists($this->command, 'reinitialize'));
    }

    /**
     * Test purgeOld subcommand method exists.
     */
    public function test_purge_old_method_exists()
    {
        $this->assertTrue(method_exists($this->command, 'purgeOld'));
    }

    /**
     * Test cleanupOrphans subcommand method exists.
     */
    public function test_cleanup_orphans_method_exists()
    {
        $this->assertTrue(method_exists($this->command, 'cleanupOrphans'));
    }

    /**
     * Test purgeAll subcommand method exists.
     */
    public function test_purge_all_method_exists()
    {
        $this->assertTrue(method_exists($this->command, 'purgeAll'));
    }

    /**
     * Test all seven subcommands are present.
     */
    public function test_all_subcommands_present()
    {
        $expected = ['tables', 'stats', 'optimize', 'reinitialize', 'purgeOld', 'cleanupOrphans', 'purgeAll'];

        foreach ($expected as $method) {
            $this->assertTrue(
                method_exists($this->command, $method),
                sprintf('Missing subcommand method: %s', $method)
            );
        }
    }
}
