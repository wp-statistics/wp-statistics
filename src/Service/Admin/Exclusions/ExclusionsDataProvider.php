<?php
namespace WP_Statistics\Service\Admin\Exclusions;

use WP_Statistics\Models\ExclusionsModel;

class ExclusionsDataProvider
{
    private $exclusionsModel;

    public function __construct()
    {
        $this->exclusionsModel = new ExclusionsModel();
    }

    public function getChartData()
    {
        return $this->exclusionsModel->getExclusions([
            'group_by'  => ['date', 'reason']
        ]);
    }

    public function getExclusionsData()
    {
        $data   = $this->exclusionsModel->getExclusions();
        $total  = array_sum(array_column($data, 'count'));

        return [
            'data'  => $data,
            'total' => $total
        ];
    }
}