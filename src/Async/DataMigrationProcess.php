<?php

namespace WP_Statistics\Async;

use WP_STATISTICS\Option;
use WP_Statistics\Service\Database\DatabaseFactory;
use WP_Statistics\Service\Database\Migrations\DataMigration;
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
     * Is the background process currently running?
     *
     * @return bool
     */
    public function is_processing()
    {
        if (get_site_transient($this->identifier . '_process_lock')) {
            Option::saveOptionGroup('migration_status_detail', [
                'status' => 'progress'
            ], 'db');
            return true;
        }

        return false;
    }

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
        $type    = $data['type'] ?? null;

        if (!$class || !$method || !$version) {
            return false;
        }

        if (!class_exists($class)) {
            return;
        }

        $instance = new $class();

        if (!method_exists($instance, 'setMethod')) {
            return false;
        }

        if ('schema' === $type) {
            if (!method_exists($instance, $method)) {
                return false;
            }

            $instance->setMethod($method, $version);
            $instance->$method($version);

            $dataSteps = (new DataMigration)->getMigrationSteps();

            if (! isset($dataSteps[$version])) {
                $instance->setVersion();
            }

            return false;
        }

        if (! $task) {
            return false;
        }

        $batchClass = $class;

        if (is_array($task) && ! empty($task['class'])) {
            $data    = ! empty($task['data']) ? $task['data'] : [];
            $setData = ! empty($task['setData']) ? $task['setData'] : '';

            if (empty($setData)) {
                return false;
            }

            $batchClass = DatabaseFactory::table($task['class'])->$setData($data);
        }

        if (!method_exists($batchClass, 'execute')) {
            return false;
        }

        $instance->setMethod($method, $version);
        $batchClass->execute();
        $instance->setVersion();

        return false;
    }

    /**
     * Complete processing.
     */
    protected function complete()
    {
        parent::complete();

        $details = Option::getOptionGroup('db', 'migration_status_detail', null);

        $operationStatus = [
            'status' => 'done',
        ];

        if (! empty($details['status']) && 'failed' === $details['status']) {
            $operationStatus = [
                'status' => 'failed',
                'message' => $details['message'],
            ];
        }

        Option::deleteOptionGroup('data_migration_process_started', 'jobs');
        Option::saveOptionGroup('migrated', true, 'db');
        Option::saveOptionGroup('migration_status_detail', $operationStatus, 'db');
        BackgroundProcessMonitor::deleteOption($this->action);
    }
}
