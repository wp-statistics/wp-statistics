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
            'countries' => $this->visitorsModel->getVisitorsGeoData($this->args),
            'total'     => $this->visitorsModel->countGeoData($this->args)
        ];
    }

    public function getCitiesData()
    {
        $args = array_merge(
            $this->args, 
            [
                'group_by'      => ['city'],
                'not_null'      => 'visitor.city',
                'count_field'   => 'city'
            ]
        );

        return [
            'cities'    => $this->visitorsModel->getVisitorsGeoData($args),
            'total'     => $this->visitorsModel->countGeoData($args)
        ];
    }

    public function getEuropeData()
    {
        $args = array_merge(
            $this->args, 
            ['continent' => 'Europe']
        );

        return [
            'countries' => $this->visitorsModel->getVisitorsGeoData($args),
            'total'     => $this->visitorsModel->countGeoData($args)
        ];
    }

    public function getUsData()
    {
        $args = array_merge(
            $this->args, 
            [
                'country'       => 'US', 
                'group_by'      => ['region'],
                'count_field'   => 'region',
                'not_null'      => 'visitor.region'
            ]
        );

        return [
            'states'    => $this->visitorsModel->getVisitorsGeoData($args),
            'total'     => $this->visitorsModel->countGeoData($args)
        ];
    }

    public function getRegionsData()
    {
        $countryCode = Helper::getTimezoneCountry();
        
        $args = array_merge(
            $this->args, 
            [
                'country'       => $countryCode, 
                'group_by'      => ['country', 'region'],
                'count_field'   => 'region',
                'not_null'      => 'visitor.region'
            ]
        );

        return [
            'regions'   => $this->visitorsModel->getVisitorsGeoData($args),
            'total'     => $this->visitorsModel->countGeoData($args)
        ];
    }
}