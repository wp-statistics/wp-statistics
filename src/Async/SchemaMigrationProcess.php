<?php

namespace WP_Statistics\Async;

use WP_STATISTICS\Option;
use WP_STATISTICS\WP_Background_Process;

class SchemaMigrationProcess extends WP_Background_Process
{
    /**
     * @var string
     */
    protected $prefix = 'wp_statistics';

    /**
     * @var string
     */
    protected $action = 'schema_migration_process';

    /**
     * Process a single schema migration task.
     * 
     * @param array $data
     * @return bool|string
     */
    protected function task($data)
    {
        $class   = $data['class'] ?? null;
        $method  = $data['method'] ?? null;
        $version = $data['version'] ?? null;

        if (!$class || !$method || !$version) {
            return false;
        }

        if (!class_exists($class)) {
            return false;
        }

        $instance = new $class();

        if (!method_exists($instance, 'setMethod') || !method_exists($instance, $method)) {
            return false;
        }

        $instance->setMethod($method, $version);
        $instance->$method();
        $instance->setVersion();

        return false;
    }

    /**
     * Complete processing.
     */
    protected function complete()
    {
        parent::complete();

        Option::deleteOptionGroup('schema_migration_process_started', 'jobs');
        Option::saveOptionGroup('migrated', true, 'db');
    }
}
