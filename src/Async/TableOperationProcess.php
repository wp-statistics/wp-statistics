<?php

namespace WP_Statistics\Async;

use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\Database\DatabaseFactory;
use WP_STATISTICS\WP_Background_Process;

class TableOperationProcess extends WP_Background_Process
{
    /**
     * @var string
     */
    protected $prefix = 'wp_statistics';

    /**
     * @var string
     */
    protected $action = 'table_operations_process';

    /**
     * Process each table creation task.
     * @param array $data
     * @return bool|string
     */
    protected function task($data)
    {
        $operation = $data['operation'] ?? null;
        $tableName = $data['table_name'] ?? null;
        $args      = $data['args'] ?? [];

        if (!$operation || !$tableName) {
            return false;
        }

        DatabaseFactory::table($operation)
            ->setName($tableName)
            ->setArgs($args)
            ->execute();

        return false;
    }

    public function is_initiated()
    {
        return Option::getOptionGroup('jobs', 'table_operations_process_initiated', false);
    }

    /**
     * Complete processing.
     */
    protected function complete()
    {
        parent::complete();
        Option::saveOptionGroup('check', false, 'db');
    }
}
