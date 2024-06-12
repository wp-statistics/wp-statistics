<?php 

namespace WP_Statistics\Service\Admin\Geographic;

use WP_Statistics\Models\VisitorsModel;

class GeographicDataProvider
{
    protected $args;
    protected $visitorsModel;

    
    public function __construct($args)
    {
        $this->args = $args;

        $this->visitorsModel = new VisitorsModel();
    }

    public function getCountriesData()
    {
        return $this->visitorsModel->getVisitorsLocationData($this->args);
    }

    public function getCitiesData()
    {
        $args = array_merge(
            $this->args, 
            ['group_by' => ['country', 'city']]
        );
        return $this->visitorsModel->getVisitorsLocationData($args);
    }
}