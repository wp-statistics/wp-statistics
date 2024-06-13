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
        return [
            'countries' => $this->visitorsModel->getVisitorsGeoData($this->args),
            'total'     => $this->visitorsModel->countCountries($this->args)
        ];
    }

    public function getCitiesData()
    {
        $args = array_merge(
            $this->args, 
            ['group_by' => ['country', 'city']]
        );

        return [
            'cities'    => $this->visitorsModel->getVisitorsGeoData($args),
            'total'     => $this->visitorsModel->countCities($args)
        ];
    }

    public function getEuropeData()
    {
        $args = array_merge(
            $this->args, 
            ['continent' => 'europe']
        );

        return [
            'countries' => $this->visitorsModel->getVisitorsGeoData($args),
            'total'     => $this->visitorsModel->countCountries($args)
        ];
    }

    public function getUsData()
    {
        $args = array_merge(
            $this->args, 
            ['country' => 'US']
        );

        return [
            'countries' => $this->visitorsModel->getVisitorsGeoData($args),
            'total'     => $this->visitorsModel->countCountries($args)
        ];
    }
}