<?php

namespace WP_Statistics\Abstracts;

use WP_Statistics\Utils\Query;
use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\HistoricalModel;

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

        return apply_filters('wp_statistics_data_{child-method-name}_args', $args);
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
                // If it's not a post type, skip
                if (!in_array($value, Helper::getPostTypes())) {
                    continue;
                }

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

        return $args;
    }

    /**
     * Parses the query_param argument.
     *
     * @return array The parsed arguments.
     */
    private function parseQueryParamArg($args)
    {
        if (!empty($args['query_param'])) {
            $uri = Query::select('uri')
                ->from('pages')
                ->where('page_id', '=', $args['query_param'])
                ->getVar();

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
}