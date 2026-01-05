<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\BaseChartResponseTrait;

class PublishOverviewChartDataProvider extends AbstractChartDataProvider
{
    use BaseChartResponseTrait;

    public function __construct($args)
    {
        parent::__construct($args);
    }

    public function getData()
    {
        // Get data from database using direct query
        $publishingData = $this->countDailyPosts();

        // Parse and prepare data
        $parsedData = $this->parseData($publishingData);
        $result     = $this->prepareResult($parsedData);

        return $result;
    }

    /**
     * Count daily posts published in the last 12 months.
     *
     * @return array Array of objects with 'posts' and 'date' properties.
     */
    protected function countDailyPosts()
    {
        global $wpdb;

        $dateRange = DateRange::get('12months');
        $dateFrom  = $dateRange['from'] ?? '';
        $dateTo    = $dateRange['to'] ?? '';

        $postTypes = !empty($this->args['post_type']) ? (array) $this->args['post_type'] : Helper::get_list_post_type();
        $authorId  = !empty($this->args['author_id']) ? intval($this->args['author_id']) : 0;

        // Build placeholders for post types
        $postTypePlaceholders = implode(',', array_fill(0, count($postTypes), '%s'));

        $query = "
            SELECT COUNT(*) as posts, DATE(post_date) as date
            FROM {$wpdb->posts}
            WHERE post_status = 'publish'
            AND post_type IN ($postTypePlaceholders)
        ";

        $queryParams = $postTypes;

        // Add date range condition
        if (!empty($dateFrom) && !empty($dateTo)) {
            $query        .= " AND post_date >= %s AND post_date <= %s";
            $queryParams[] = $dateFrom . ' 00:00:00';
            $queryParams[] = $dateTo . ' 23:59:59';
        }

        // Add author filter if specified
        if ($authorId > 0) {
            $query        .= " AND post_author = %d";
            $queryParams[] = $authorId;
        }

        $query .= " GROUP BY DATE(post_date)";

        // Handle taxonomy/term filtering if specified
        if (!empty($this->args['taxonomy']) || !empty($this->args['term'])) {
            $taxonomy = !empty($this->args['taxonomy']) ? (array) $this->args['taxonomy'] : [];
            $term     = !empty($this->args['term']) ? intval($this->args['term']) : 0;

            if (!empty($taxonomy) && $term > 0) {
                $taxonomyPlaceholders = implode(',', array_fill(0, count($taxonomy), '%s'));

                $query = "
                    SELECT COUNT(*) as posts, DATE(p.post_date) as date
                    FROM {$wpdb->posts} p
                    INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
                    INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                    INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                    WHERE p.post_status = 'publish'
                    AND p.post_type IN ($postTypePlaceholders)
                    AND tt.taxonomy IN ($taxonomyPlaceholders)
                    AND t.term_id = %d
                ";

                $queryParams = array_merge($postTypes, $taxonomy, [$term]);

                // Re-add date range condition
                if (!empty($dateFrom) && !empty($dateTo)) {
                    $query        .= " AND p.post_date >= %s AND p.post_date <= %s";
                    $queryParams[] = $dateFrom . ' 00:00:00';
                    $queryParams[] = $dateTo . ' 23:59:59';
                }

                // Re-add author filter if specified
                if ($authorId > 0) {
                    $query        .= " AND p.post_author = %d";
                    $queryParams[] = $authorId;
                }

                $query .= " GROUP BY DATE(p.post_date)";
            }
        }

        $preparedQuery = $wpdb->prepare($query, $queryParams);

        return $wpdb->get_results($preparedQuery);
    }

    /**
     * Parse the raw database results into chart-ready format.
     *
     * @param array $data Array of objects with 'posts' and 'date' properties.
     * @return array Parsed data array for the chart.
     */
    protected function parseData($data)
    {
        $publishingData = wp_list_pluck($data, 'posts', 'date');

        $today = time();
        $date  = strtotime('-365 days');

        $parsedData = [];

        // Get number of posts published per day during last 365 days
        while ($date <= $today) {
            $currentDate   = date('Y-m-d', $date);
            $numberOfPosts = isset($publishingData[$currentDate]) ? intval($publishingData[$currentDate]) : 0;

            $parsedData[] = [
                'x' => $currentDate, // date in Y-m-d format
                'y' => date('N', $date), // day of week
                'd' => date_i18n(Helper::getDefaultDateFormat(), strtotime($currentDate)), // date in default format
                'v' => $numberOfPosts // number of posts
            ];

            $date += DAY_IN_SECONDS;
        }

        return $parsedData;
    }

    /**
     * Prepare the final chart result.
     *
     * @param array $data Parsed data array.
     * @return array Chart data structure.
     */
    protected function prepareResult($data)
    {
        $this->initChartData();

        $this->setChartDatasets($data);

        return $this->getChartData();
    }
}
