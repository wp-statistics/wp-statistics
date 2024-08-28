<?php

namespace WP_Statistics\Models;

use WP_Statistics\Utils\Query;
use WP_Statistics\Abstracts\BaseModel;

class HistoricalModel extends BaseModel
{
    /**
     * Returns historical views of a page by its URL.
     *
     * @param array $args Arguments to include in query (e.g. `page_id`, `uri`, etc.).
     * @return  int
     *
     * @todo    Merge this with count methods in `ViewsModel`.
     */
    public function countUris($args = [])
    {
        $args = $this->parseArgs($args, [
            'page_id' => '',
            'uri'     => '',
        ]);

        $query = Query::select('SUM(`value`) AS `historical_views`')
            ->from('historical')
            ->where('page_id', '=', intval($args['page_id']))
            ->where('uri', '=', $args['uri']);

        return intval($query->getVar());
    }
}
