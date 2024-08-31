<?php

namespace WP_Statistics\Abstracts;

use WP_Statistics\Components\DateRange;

abstract class BaseModel
{
    /**
     * @param $args
     * @param $defaults
     * @return mixed|null
     */
    protected function parseArgs($args, $defaults = [])
    {
        $args = wp_parse_args($args, $defaults);
        $args = $this->parseQueryParamArg($args);
        $args = $this->parseDateArg($args);

        return apply_filters('wp_statistics_data_{child-method-name}_args', $args);
    }

    /**
     * Parses the query_param argument.
     *
     * @return array The parsed arguments.
     */
    private function parseQueryParamArg($args)
    {
        if (!empty($args['query_param'])) {
            $select = $this->query;
            $uri    = $select::select('uri')
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