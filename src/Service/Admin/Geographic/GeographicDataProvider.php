<?php 

namespace WP_Statistics\Service\Admin\Geographic;

use WP_STATISTICS\Helper;
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
            'countries' => $this->visitorsModel->getVisitorsCountryData($this->args),
            'total'     => $this->visitorsModel->countCountries($this->args)
        ];
    }

    public function getCitiesData()
    {
        return [
            'cities'    => $this->visitorsModel->getVisitorsCityData($this->args),
            'total'     => $this->visitorsModel->countCities($this->args)
        ];
    }

    public function getEuropeData()
    {
        $args = array_merge(
            $this->args, 
            ['continent' => 'europe']
        );

        return [
            'countries' => $this->visitorsModel->getVisitorsCountryData($args),
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
            'states'    => $this->visitorsModel->getVisitorsRegionData($args),
            'total'     => $this->visitorsModel->countRegions($args)
        ];
    }

    public function getRegionsData()
    {
        $countryCode = Helper::getTimezoneCountry();
        
        $args = array_merge(
            $this->args, 
            ['country' => $countryCode]
        );

        return [
            'regions'   => $this->visitorsModel->getVisitorsRegionData($args),
            'total'     => $this->visitorsModel->countRegions($args)
        ];
    }
}