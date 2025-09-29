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

        $result = Query::select(['COALESCE(SUM(visitors), 0) as visitors', 'COALESCE(SUM(views), 0) as views'])
            ->from('summary_totals')
            ->whereDate('date', $args['date'])
            ->getRow();

        return $result;
    }

    public function recordExists($args = [])
    {
        $args = $this->parseArgs($args, [
            'id'   => '',
            'date' => ''
        ]);

        $result = Query::select('COUNT(*) as count')
            ->from('summary_totals')
            ->where('date', '=', $args['date'])
            ->where('id', '=', $args['id'])
            ->getVar();

        return (bool) $result;
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