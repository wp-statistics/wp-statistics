<?php

namespace WP_Statistics\Service\Charts\Traits;

/**
 * Trait for parsing simple bar chart data with common sorting and "Other" aggregation logic.
 *
 * This trait consolidates duplicate parsing logic from:
 * - BrowserChartDataProvider
 * - DeviceChartDataProvider
 * - OsChartDataProvider
 *
 * @since 15.0.0
 */
trait SimpleBarChartDataTrait
{
    /**
     * Parse bar chart data with sorting and "Other" aggregation.
     *
     * @param array    $data         Raw data rows from query.
     * @param string   $fieldName    Field name to extract (e.g., 'browser', 'device_type', 'os').
     * @param callable $iconCallback Callback to get icon for each item.
     * @param int      $topLimit     Number of top items to show before grouping as "Other". Default 4.
     *
     * @return array Parsed data with 'labels', 'visitors', and 'icons' arrays.
     */
    protected function parseBarChartData(array $data, string $fieldName, callable $iconCallback, int $topLimit = 4): array
    {
        $parsedData = [];

        if (!empty($data)) {
            foreach ($data as $row) {
                $value    = $row[$fieldName] ?? '';
                $visitors = intval($row['visitors'] ?? 0);

                if (!empty($value)) {
                    $parsedData[] = [
                        'label'    => $value,
                        'icon'     => $iconCallback($value),
                        'visitors' => $visitors
                    ];
                }
            }

            // Sort data by visitors descending
            usort($parsedData, function ($a, $b) {
                return $b['visitors'] - $a['visitors'];
            });

            // Aggregate items beyond top limit as "Other"
            if (count($parsedData) > $topLimit) {
                $topData   = array_slice($parsedData, 0, $topLimit);
                $otherData = array_slice($parsedData, $topLimit);

                $otherItem = [
                    'label'    => esc_html__('Other', 'wp-statistics'),
                    'icon'     => '',
                    'visitors' => array_sum(array_column($otherData, 'visitors')),
                ];

                $parsedData = array_merge($topData, [$otherItem]);
            }
        }

        return [
            'labels'   => wp_list_pluck($parsedData, 'label'),
            'visitors' => wp_list_pluck($parsedData, 'visitors'),
            'icons'    => wp_list_pluck($parsedData, 'icon'),
        ];
    }
}
