<?php

namespace WP_Statistics\Async;

use WP_STATISTICS\Option;
use WP_STATISTICS\WP_Background_Process;

class DataMigrationProcess extends WP_Background_Process
{
    /**
     * @var string
     */
    protected $prefix = 'wp_statistics';

    /**
     * @var string
     */
    protected $action = 'data_migration_process';

    /**
     * Process a single data migration task.
     * 
     * @param array $data
     * @return bool|string
     */
    protected function task($data)
    {
        $class   = $data['class'] ?? null;
        $method  = $data['method'] ?? null;
        $version = $data['version'] ?? null;
        $task    = $data['task'] ?? null;

        if (!$class || !$method || !$version || !$task) {
            return false;
        }

        if ( !class_exists($class)) {
            return;
        }

        $instance = new $class();

        if (!method_exists($instance, 'setMethod') || !method_exists($task, 'execute')) {
            return false;
        }

        $instance->setMethod($method, $version);
        $task->execute();

        return false;
    }

    /**
     * Complete processing.
     */
    protected function complete()
    {
        parent::complete();

        Option::deleteOptionGroup('data_migration_process_started', 'jobs');
        Option::update('db_migrated', true);
    }
}
