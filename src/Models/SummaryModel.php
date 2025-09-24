<?php
namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Utils\Query;

class SummaryModel extends BaseModel
{
    public function getData($args = [])
    {
        $args = $this->parseArgs($args, [
            'date' => ''
        ]);

        $result = Query::select(['SUM(visitors)', 'SUM(views)'])
            ->from('summary_totals')
            ->whereDate('date', $args['date'])
            ->getRow();

        return $result;
    }

    public function insert($args)
    {
        $data = [
            'date'     => $args['date'] ?? DateTime::get('now', 'Y-m-d'),
            'visitors' => $args['visitors'] ?? 0,
            'views'    => $args['views'] ?? 0
        ];

        $result = Query::insert('summary_totals')
            ->set($data)
            ->execute();

        return $result;
    }
}