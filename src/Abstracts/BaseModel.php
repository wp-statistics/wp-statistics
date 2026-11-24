<?php

namespace WP_Statistics\Abstracts;

use WP_Statistics\Utils\Query;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Models\HistoricalModel;
use WP_STATISTICS\Option;

abstract class BaseModel
{
    /**
     * Instance of HistoricalModel for historical page view data
     *
     * @var HistoricalModel
     */
    protected $historicalModel;

    /**
     * Initialize the BaseModel class.
     *
     * Creates a new instance of HistoricalModel if one hasn't been set yet.
     * This ensures we have access to historical data throughout the model.
     */
    public function __construct()
    {
        if (empty($this->historicalModel)) {
            $this->historicalModel = new HistoricalModel();
        }
    }

    /**
     * @param $args
     * @param $defaults
     * @return mixed|null
     */
    protected function parseArgs($args, $defaults = [])
    {
        $args = wp_parse_args($args, $defaults);
        $args = $this->parseQueryParamArg($args);
        $args = $this->parseResourceTypeArg($args);
        $args = $this->parseDateArg($args);
        $args = $this->parseUtmParams($args);
        $args = $this->parsePerPage($args);

        return apply_filters('wp_statistics_data_{child-method-name}_args', $args);
    }

    /**
     * Escape any underscores in UTM params with a backslash
     * This is necessary because WordPress's LIKE operator uses underscores as a wildcard
     * @param array $args
     * @return array
     */
    private function parseUtmParams($args)
    {
        if (!empty($args['utm_source'])) {
            $args['utm_source'] = str_replace('_', '\_', $args['utm_source']);
        }

        if (!empty($args['utm_medium'])) {
            $args['utm_medium'] = str_replace('_', '\_', $args['utm_medium']);
        }

        if (!empty($args['utm_campaign'])) {
            $args['utm_campaign'] = str_replace('_', '\_', $args['utm_campaign']);
        }

        return $args;
    }

    /**
     * Parse resource type argument to make sure it contains valid post types and properly formatted
     *
     * @param array $args
     *
     * @return array
     */
    private function parseResourceTypeArg($args)
    {
        if (!empty($args['resource_type'])) {
            // Make sure resource_type is an array
            if (is_string($args['resource_type'])) {
                $args['resource_type'] = [$args['resource_type']];
            }

            foreach ($args['resource_type'] as $key => $value) {
                // If it's a taxonomy.
                if (taxonomy_exists($value)) {
                    if (!in_array($value, ['category', 'post_tag'])) {
                        $args['resource_type'][$key] = "tax_{$value}";
                    }
                }

                // If it's a post type.
                if (post_type_exists($value)) {
                    // If it's a custom post type, add 'post_type' prefix
                    if (!in_array($value, ['post', 'page', 'product', 'attachment'])) {
                        $args['resource_type'][$key] = "post_type_$value";
                    }

                    // If the array contains page post type, add home as well
                    if (!in_array('home', $args['resource_type']) && $value === 'page') {
                        $args['resource_type'][] = 'home';
                    }
                }
            }
        }

        return $args;
    }

    /**
     * Convert post type names to the `type` values stored in the pages table
     * (e.g. a custom post type becomes `post_type_{name}`, and `page` adds `home`).
     *
     * @param string|array $postTypes
     * @param bool $includeHome Whether `page` also adds the `home` type (front page rows).
     * @return array
     */
    protected function getResourceTypes($postTypes, $includeHome = true)
    {
        if (empty($postTypes)) {
            return [];
        }

        // A comma-separated list, as the `IN` filters on post_type read it
        if (is_string($postTypes)) {
            $postTypes = array_values(array_filter(array_map('trim', explode(',', $postTypes))));
        }

        $parsed = $this->parseResourceTypeArg(['resource_type' => $postTypes]);
        $types  = $parsed['resource_type'];

        if (!$includeHome) {
            $types = array_values(array_diff($types, ['home']));
        }

        return $types;
    }

    /**
     * Cache a query whose date filters sit in its subqueries, the way whereDate() does: only when the
     * range ends before today.
     *
     * @param Query $query
     * @param array|string $date
     */
    protected function allowCachingForPastRange($query, $date)
    {
        if (empty($date)) {
            return;
        }

        $range = is_array($date) ? $date : DateRange::get($date);

        if (!empty($range['from']) && !empty($range['to']) && $range['to'] < DateTime::get()) {
            $query->allowCaching();
        }
    }

    /**
     * Limit a query on the posts table to a taxonomy and/or term (no-op when neither is set).
     *
     * @param Query $query
     * @param array $args Reads `taxonomy` and `term`.
     * @param string $postIdColumn Column holding the post ID in $query.
     */
    protected function joinTermFilter($query, $args, $postIdColumn = 'posts.ID')
    {
        if (empty($args['taxonomy']) && empty($args['term'])) {
            return;
        }

        $taxQuery = Query::select(['DISTINCT object_id'])
            ->from('term_relationships')
            ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
            ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
            ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'] ?? '')
            ->where('terms.term_id', '=', $args['term'] ?? '')
            ->getQuery();

        $query->joinQuery($taxQuery, [$postIdColumn, 'tax.object_id'], 'tax');
    }

    /**
     * Turn rows of `id` + `total` into an id => count map.
     *
     * @param object[]|null $rows
     * @return int[]
     */
    protected function pluckCounts($rows)
    {
        $counts = [];

        foreach ((array) $rows as $row) {
            $counts[(int) $row->id] = (int) $row->total;
        }

        return $counts;
    }

    /**
     * Parses the query_param argument.
     *
     * @return array The parsed arguments.
     */
    private function parseQueryParamArg($args)
    {
        if (!empty($args['query_param'])) {

            if (is_numeric($args['query_param'])) {
                $uri = Query::select('uri')
                    ->from('pages')
                    ->where('page_id', '=', $args['query_param'])
                    ->getVar();
            } else if (is_string($args['query_param'])) {
                $uri = $args['query_param'];
            }

            $args['query_param'] = !empty($uri) ? $uri : '';
        }

        return $args;
    }

    private function parseDateArg($args)
    {
        if (empty($args['date']) && empty($args['ignore_date'])) {
            $args['date'] = DateRange::get();
        }

        return $args;
    }

    private function parsePerPage($args)
    {
        // On export, ignore default per_page and get data based on the advanced reporting option
        if (!empty($args['export'])) {
            $args['page']     = 1;
            $args['per_page'] = Option::getByAddon('table_csv_export_row_limit', 'advanced_reporting', 100);
        }

        return $args;
    }
}