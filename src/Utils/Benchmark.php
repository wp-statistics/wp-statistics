<?php

namespace WP_Statistics\Utils;

/**
 * Benchmark utility class for measuring code performance.
 *
 * This class provides methods to measure execution time, memory usage,
 * and compare performance between different operations.
 *
 */
class Benchmark
{

    /**
     * Benchmark results storage.
     *
     * @var array
     */
    private static $results = [];

    /**
     * Stack for nested benchmark start values.
     *
     * @var array
     */
    private static $stack = [];

    /**
     * Start a new benchmark measurement.
     *
     * @param string $name The name of the benchmark.
     * @return void
     */
    public static function start($name)
    {
        gc_collect_cycles();
        self::$stack[$name] = [
            'time'   => microtime(true),
            'memory' => memory_get_usage()
        ];

        if (!isset(self::$results[$name])) {
            self::$results[$name] = [];
        }
    }

    /**
     * End the current benchmark measurement.
     *
     * @param string $name The name of the benchmark.
     * @return array The benchmark results.
     */
    public static function end($name)
    {
        // Ensure the benchmark was started.
        if (!isset(self::$stack[$name])) {
            return null;
        }

        $start = self::$stack[$name];
        unset(self::$stack[$name]);

        $endTime    = microtime(true);
        $endMemory  = memory_get_usage();
        gc_collect_cycles();

        // Calculate deltas.
        $executionTime = ($endTime - $start['time']) * 1000;   // milliseconds
        $memoryUsage   = $endMemory - $start['memory'];

        $result = [
            'execution_time'         => round($executionTime, 4),
            'memory_usage'           => $memoryUsage,
            'memory_usage_formatted' => self::formatBytes($memoryUsage),
            'timestamp'              => $endTime
        ];

        self::$results[$name][] = $result;

        return $result;
    }

    /**
     * Get all benchmark results.
     *
     * @return array All benchmark results.
     */
    public static function getResults()
    {
        return self::$results;
    }

    /**
     * Get results for a specific benchmark.
     *
     * @param string $name The name of the benchmark.
     * @return array|null The benchmark results or null if not found.
     */
    public static function getResult($name)
    {
        return isset(self::$results[$name]) ? self::$results[$name] : null;
    }

    /**
     * Compare two benchmark results.
     *
     * @param string $name1 First benchmark name.
     * @param string $name2 Second benchmark name.
     * @return array Comparison results.
     */
    public static function compare($name1, $name2)
    {
        $result1 = self::getResult($name1);
        $result2 = self::getResult($name2);
        
        if (!$result1 || !$result2) {
            return null;
        }
        
        // Get the latest results
        $latest1 = end($result1);
        $latest2 = end($result2);
        
        $timeDiff = $latest2['execution_time'] - $latest1['execution_time'];
        $memoryDiff = $latest2['memory_usage'] - $latest1['memory_usage'];
        
        $timePercentage = $latest1['execution_time'] > 0 ? 
            (($timeDiff / $latest1['execution_time']) * 100) : 0;
        
        $memoryPercentage = $latest1['memory_usage'] !== 0
            ? (($memoryDiff / abs($latest1['memory_usage'])) * 100)
            : 0;
        
        return [
            'benchmark1' => [
                'name' => $name1,
                'execution_time' => $latest1['execution_time'],
                'memory_usage' => $latest1['memory_usage_formatted']
            ],
            'benchmark2' => [
                'name' => $name2,
                'execution_time' => $latest2['execution_time'],
                'memory_usage' => $latest2['memory_usage_formatted']
            ],
            'comparison' => [
                'time_difference' => round($timeDiff, 4),
                'time_percentage' => round($timePercentage, 2),
                'memory_difference' => $memoryDiff,
                'memory_difference_formatted' => self::formatBytes($memoryDiff),
                'memory_percentage' => round($memoryPercentage, 2),
                'faster' => $timeDiff < 0 ? $name2 : $name1,
                'more_efficient' => $memoryDiff < 0 ? $name2 : $name1
            ]
        ];
    }

    /**
     * Clear all benchmark results.
     *
     * @return void
     */
    public static function clear()
    {
        self::$results = [];
    }

    /**
     * Format bytes to human readable format.
     *
     * @param int $bytes Number of bytes.
     * @param int $precision Number of decimal places.
     * @return string Formatted bytes.
     */
    private static function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Get a summary of all benchmarks.
     *
     * @return array Summary of all benchmarks.
     */
    public static function getSummary()
    {
        $summary = [];
        
        foreach (self::$results as $name => $results) {
            if (empty($results)) {
                continue;
            }
            
            $totalTime = 0;
            $totalMemory = 0;
            $count = count($results);
            
            foreach ($results as $result) {
                $totalTime += $result['execution_time'];
                $totalMemory += $result['memory_usage'];
            }
            
            $summary[$name] = [
                'count' => $count,
                'average_time' => round($totalTime / $count, 4),
                'total_time' => round($totalTime, 4),
                'average_memory' => self::formatBytes($totalMemory / $count),
                'total_memory' => self::formatBytes($totalMemory)
            ];
        }
        
        return $summary;
    }
} 