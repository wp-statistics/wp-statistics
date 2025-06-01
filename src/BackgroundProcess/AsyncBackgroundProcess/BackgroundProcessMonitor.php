<?php

namespace WP_Statistics\BackgroundProcess\AsyncBackgroundProcess;

use WP_STATISTICS\Option;

class BackgroundProcessMonitor
{

    /**
     * Option group name used for storing the progress of background processes.
     *
     * @var string
     */
    private static $optionGroupName = 'background_process_progress';

    /**
     * Cache for index data.
     *
     * @var array
     */
    private static $cache = [];

    /**
     * Retrieve the progress data for a given index from the option group.
     *
     * @param string $index The unique index key for the progress data.
     * @return array The progress data for the index.
     */
    private static function getIndexData($index)
    {
        if (! empty(self::$cache[$index])) {
            return self::$cache[$index];
        }

        $data = Option::getOptionGroup(self::$optionGroupName, $index, []);
        if (! is_array($data)) {
            $data = [];
        }
        return $data;
    }

    /**
     * Update the progress data for a given index in the option group.
     *
     * @param string $index The unique index key for the progress data.
     * @param array  $data  The progress data array to save.
     */
    private static function updateIndexData($index, $data)
    {
        Option::saveOptionGroup($index, $data, self::$optionGroupName);
    }

    /**
     * Set the total number of tasks for a given index.
     *
     * @param string $index      The unique index key for the progress data.
     * @param int    $totalTasks The number of tasks to add or set as total.
     */
    public static function setTotalRecords($index, $totalTasks)
    {
        $data = self::getIndexData($index);
        if (! isset($data['total'])) {
            $data['total'] = 0;
        }

        if ($data['total'] > 0) {
            $data['total'] += $totalTasks;
        } else {
            $data['total'] = $totalTasks;
        }

        if (! isset($data['completed'])) {
            $data['completed'] = 0;
        }
        self::updateIndexData($index, $data);
    }

    /**
     * Set the number of completed tasks for a given index.
     *
     * @param string $index          The unique index key for the progress data.
     * @param int    $completedTasks The number of tasks completed to add or set.
     */
    public static function setCompletedRecords($index, $completedTasks)
    {
        $data = self::getIndexData($index);
        if (! isset($data['completed'])) {
            $data['completed'] = 0;
        }

        if ($data['completed'] > 0) {
            $data['completed'] += $completedTasks;
        } else {
            $data['completed'] = $completedTasks;
        }

        if (! isset($data['total'])) {
            $data['total'] = 0;
        }
        self::updateIndexData($index, $data);
    }

    /**
     * Get the total number of tasks for a given index.
     *
     * @param string $index The unique index key for the progress data.
     * @return int The total number of tasks.
     */
    public static function getTotalRecords($index)
    {
        $data = self::getIndexData($index);
        return isset($data['total']) ? $data['total'] : 0;
    }

    /**
     * Get the number of completed tasks for a given index.
     *
     * @param string $index The unique index key for the progress data.
     * @return int The number of tasks that have been completed.
     */
    public static function getCompletedRecords($index)
    {
        $data = self::getIndexData($index);
        return isset($data['completed']) ? $data['completed'] : 0;
    }

    /**
     * Get the number of tasks remaining to be processed for a given index.
     *
     * @param string $index The unique index key for the progress data.
     * @return int The number of tasks remaining.
     */
    public static function getRemainingRecords($index)
    {
        return self::getTotalRecords($index) - self::getCompletedRecords($index);
    }

    /**
     * Get the progress percentage (0% to 100%) based on completed tasks.
     *
     * @param string $index The unique index key for the progress data.
     * @return float The percentage of tasks that have been completed.
     */
    public static function getProgressPercentage($index)
    {
        $total = self::getTotalRecords($index);
        if ($total === 0) {
            return 0.0;
        }
        $completed = self::getCompletedRecords($index);
        return round(($completed / $total) * 100, 2);
    }

    /**
     * Retrieve the current status as an associative array for a given index.
     * The array contains:
     * - total: Total number of tasks.
     * - remain: Number of tasks remaining (total - completed).
     * - completed: Number of tasks completed.
     * - percentage: The percentage of tasks completed.
     *
     * @param string $index The unique index key for the progress data.
     * @return array The status information.
     */
    public static function getStatus($index)
    {
        return [
            'total'      => self::getTotalRecords($index),
            'remain'     => self::getRemainingRecords($index),
            'completed'  => self::getCompletedRecords($index),
            'percentage' => self::getProgressPercentage($index),
        ];
    }

    /**
     * Delete progress data for a given index from the option group.
     *
     * @param string $index The unique index key for the progress data to delete.
     */
    public static function deleteOption($index)
    {
        Option::deleteOptionGroup($index, self::$optionGroupName);
    }
}
