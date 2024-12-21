<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Utils\Query;

class ExclusionsModel extends BaseModel
{
    public function countExclusions($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'reason'    => ''
        ]);

        $result = Query::select(['COUNT(id)'])
            ->from('exclusions')
            ->where('reason', '=', $args['reason'])
            ->whereDate('date', $args['date'])
            ->orderBy('date')
            ->getAll();

        return $result;
    }

    public function getExclusions($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'reason'        => '',
            'exclusion_id'  => '',
            'per_page'      => '',
            'page'          => 1,
            'group_by'      => 'reason',
        ]);

        $result = Query::select([
            'reason',
            'SUM(count) as count'
        ])
            ->from('exclusions')
            ->where('reason', '=', $args['reason'])
            ->where('id', '=', $args['exclusion_id'])
            ->whereDate('date', $args['date'])
            ->perPage($args['page'], $args['per_page'])
            ->groupBy($args['group_by'])
            ->orderBy('date')
            ->getAll();

        return $result;
    }
}
