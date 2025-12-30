<?php

namespace WP_Statistics\Testing\Simulator\Generators;

/**
 * AbstractDataGenerator - Base class for all data generators
 *
 * Provides common functionality for loading data files and weighted random selection.
 *
 * @package WP_Statistics\Testing\Simulator\Generators
 * @since 15.0.0
 */
abstract class AbstractDataGenerator
{
    /**
     * Directory containing data files
     */
    protected string $dataDir;

    /**
     * Loaded data files cache
     * @var array<string, array>
     */
    protected array $dataFiles = [];

    /**
     * Constructor
     *
     * @param string $dataDir Path to data directory
     */
    public function __construct(string $dataDir)
    {
        $this->dataDir = rtrim($dataDir, '/');
    }

    /**
     * Generate data for a single request
     *
     * @return array Generated data
     */
    abstract public function generate(): array;

    /**
     * Load a JSON data file
     *
     * @param string $filename Filename (without path)
     * @return array Parsed JSON data
     * @throws \RuntimeException If file cannot be loaded
     */
    protected function loadDataFile(string $filename): array
    {
        if (isset($this->dataFiles[$filename])) {
            return $this->dataFiles[$filename];
        }

        $filepath = $this->dataDir . '/' . $filename;

        if (!file_exists($filepath)) {
            throw new \RuntimeException("Data file not found: {$filepath}");
        }

        $contents = file_get_contents($filepath);
        if ($contents === false) {
            throw new \RuntimeException("Failed to read data file: {$filepath}");
        }

        $data = json_decode($contents, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Invalid JSON in data file: {$filepath} - " . json_last_error_msg());
        }

        $this->dataFiles[$filename] = $data;
        return $data;
    }

    /**
     * Weighted random selection
     *
     * @param array $weights Associative array of [key => weight]
     * @return mixed Selected key
     */
    protected function weightedRandom(array $weights)
    {
        if (empty($weights)) {
            return null;
        }

        $total = array_sum($weights);
        if ($total <= 0) {
            return array_key_first($weights);
        }

        $rand = mt_rand(1, (int)($total * 100)) / 100;

        $cumulative = 0;
        foreach ($weights as $key => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $key;
            }
        }

        return array_key_last($weights);
    }

    /**
     * Random selection from array
     *
     * @param array $items Array of items
     * @return mixed Random item
     */
    protected function randomFrom(array $items)
    {
        if (empty($items)) {
            return null;
        }
        return $items[array_rand($items)];
    }

    /**
     * Random boolean with given probability
     *
     * @param float $probability Probability of true (0.0 to 1.0)
     * @return bool
     */
    protected function randomBool(float $probability = 0.5): bool
    {
        return mt_rand(1, 10000) / 10000 <= $probability;
    }

    /**
     * Random integer within range with optional normal distribution
     *
     * @param int $min Minimum value
     * @param int $max Maximum value
     * @param bool $normalDistribution Use normal distribution (center-biased)
     * @return int
     */
    protected function randomInt(int $min, int $max, bool $normalDistribution = false): int
    {
        if ($normalDistribution) {
            // Approximate normal distribution using sum of uniform randoms
            $sum = 0;
            for ($i = 0; $i < 3; $i++) {
                $sum += mt_rand($min * 1000, $max * 1000);
            }
            return (int) round($sum / 3000);
        }

        return mt_rand($min, $max);
    }

    /**
     * Random float within range
     *
     * @param float $min Minimum value
     * @param float $max Maximum value
     * @param int $precision Decimal precision
     * @return float
     */
    protected function randomFloat(float $min, float $max, int $precision = 2): float
    {
        $factor = pow(10, $precision);
        return mt_rand((int)($min * $factor), (int)($max * $factor)) / $factor;
    }

    /**
     * Generate random string
     *
     * @param int $length String length
     * @param string $chars Character set to use
     * @return string
     */
    protected function randomString(int $length, string $chars = 'abcdefghijklmnopqrstuvwxyz0123456789'): string
    {
        $result = '';
        $charCount = strlen($chars);
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[mt_rand(0, $charCount - 1)];
        }
        return $result;
    }

    /**
     * Get current timestamp with optional offset
     *
     * @param int $offsetSeconds Seconds to offset (negative for past)
     * @return string ISO 8601 timestamp
     */
    protected function getTimestamp(int $offsetSeconds = 0): string
    {
        return date('Y-m-d H:i:s', time() + $offsetSeconds);
    }

    /**
     * Get random date within range
     *
     * @param string $from Start date (Y-m-d)
     * @param string $to End date (Y-m-d)
     * @param array $hourWeights Optional hourly distribution weights (0-23)
     * @return string Y-m-d H:i:s format
     */
    protected function randomDateTime(string $from, string $to, array $hourWeights = []): string
    {
        $fromTs = strtotime($from);
        $toTs = strtotime($to);

        $dateTs = mt_rand($fromTs, $toTs);
        $date = date('Y-m-d', $dateTs);

        if (!empty($hourWeights)) {
            $hour = $this->weightedRandom($hourWeights);
        } else {
            $hour = mt_rand(0, 23);
        }

        $minute = mt_rand(0, 59);
        $second = mt_rand(0, 59);

        return sprintf('%s %02d:%02d:%02d', $date, $hour, $minute, $second);
    }

    /**
     * Get weekday multiplier for traffic distribution
     *
     * @param int $dayOfWeek Day of week (1=Monday, 7=Sunday)
     * @return float Multiplier
     */
    protected function getWeekdayMultiplier(int $dayOfWeek): float
    {
        $multipliers = [
            1 => 1.0,   // Monday
            2 => 1.1,   // Tuesday
            3 => 1.2,   // Wednesday (peak)
            4 => 1.1,   // Thursday
            5 => 0.95,  // Friday
            6 => 0.6,   // Saturday
            7 => 0.5,   // Sunday
        ];

        return $multipliers[$dayOfWeek] ?? 1.0;
    }

    /**
     * Get default hourly traffic distribution
     *
     * @return array<int, int> Hour => weight mapping
     */
    protected function getDefaultHourDistribution(): array
    {
        return [
            0  => 2, 1  => 1, 2  => 1, 3  => 1, 4  => 1, 5  => 2,
            6  => 3, 7  => 4, 8  => 6,
            9  => 8, 10 => 9, 11 => 9, 12 => 8, 13 => 8, 14 => 9, 15 => 9, 16 => 8, 17 => 7,
            18 => 6, 19 => 5, 20 => 5, 21 => 4, 22 => 3, 23 => 2
        ];
    }

    /**
     * Check if this is a valid generator
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return is_dir($this->dataDir);
    }

    /**
     * Get generator name
     *
     * @return string
     */
    public function getName(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }
}
