<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Models\Legacy\LegacyVisitorsModel;

class VisitorsModel extends BaseModel
{
    private $legacy;

    public function __construct()
    {
        $this->legacy = new LegacyVisitorsModel();
    }
 
    public function countVisitors($args = [])
    {        
        if (false) {
            return $this->legacy->countVisitors($args);
        }

        return (new SessionModel())->countVisitors($args);
    }

    public function countDailyVisitors($args = [])
    {
        if (false) {
            return $this->legacy->countDailyVisitors($args);
        }
    
        return (new SessionModel())->countDailyVisitors($args);
    }

    public function countHits($args = [])
    {
        if (false) {
            return $this->legacy->countHits($args);
        }

        return (new SessionModel())->countHits($args);
    }

    public function countDailyReferrers($args = [])
    {
        if (false) {
            return $this->legacy->countDailyReferrers($args);
        }

        return (new SessionModel())->countDailyReferrers($args);
    }

    /**
     * Returns `COUNT DISTINCT` of a column from visitors table.
     *
     * @param array $args Arguments to include in query (e.g. `field`, `date`, `where_col`, `where_val`, etc.).
     *
     * @return  int
     */
    public function countColumnDistinct($args = [])
    {
        if (false) {
            return $this->legacy->countColumnDistinct($args);
        }

        return (new SessionModel())->countColumnDistinct($args);
    }

    public function getVisitorsDevices($args = [])
    {
        if (false) {
            return $this->legacy->getVisitorsDevices($args);
        }

        return (new SessionModel())->getVisitorsDevices($args);
    }

    public function getVisitorsDevicesVersions($args = [])
    {
        if (false) {
            return $this->legacy->getVisitorsDevicesVersions($args);
        }

        return (new SessionModel())->getVisitorsDevicesVersions($args);
    }

    public function getVisitorsSummary($args = [])
    {
        if (false) {
            return $this->legacy->getVisitorsSummary($args);
        }

        return (new SessionModel())->getVisitorsSummary($args);
    }

    public function getHitsSummary($args = [])
    {
        if (false) {
            return $this->legacy->getHitsSummary($args);
        }

        return (new SessionModel())->getHitsSummary($args);
    }

    public function getVisitorsData($args = [])
    {
        if (false) {
            return $this->legacy->getVisitorsData($args);
        }

        return (new SessionModel())->getVisitorsData($args);
    }

    public function getReferredVisitors($args = [])
    {
        if (false) {
            return $this->legacy->getReferredVisitors($args);
        }

        return (new SessionModel())->getReferredVisitors($args);
    }

    public function countReferredVisitors($args = [])
    {
        if (false) {
            return $this->legacy->countReferredVisitors($args);
        }

        return (new SessionModel())->countReferredVisitors($args);
    }

    public function searchVisitors($args = [])
    {
        if (false) {
            return $this->legacy->searchVisitors($args);
        }

        return (new SessionModel())->searchVisitors($args);
    }

    public function getVisitorData($args = [])
    {
        if (false) {
            return $this->legacy->getVisitorData($args);
        }

        return (new SessionModel())->getVisitorData($args);
    }

    public function getVisitorJourney($args)
    {
        if (false) {
            return $this->legacy->getVisitorJourney($args);
        }

        return (new SessionModel())->getVisitorJourney($args);
    }

    public function countGeoData($args = [])
    {
        if (false) {
            return $this->legacy->countGeoData($args);
        }

        return (new SessionModel())->countGeoData($args);
    }

    public function getVisitorsGeoData($args = [])
    {
        if (false) {
            return $this->legacy->getVisitorsGeoData($args);
        }

        return (new SessionModel())->getVisitorsGeoData($args);
    }

    public function getVisitorsWithIncompleteLocation($returnCount = false)
    {
        if (false) {
            return $this->legacy->getVisitorsWithIncompleteLocation($returnCount);
        }

        return (new SessionModel())->getVisitorsWithIncompleteLocation($returnCount);
    }

    public function getVisitorsWithIncompleteSourceChannel($args = [])
    {
        if (false) {
            return $this->legacy->getVisitorsWithIncompleteSourceChannel($args);
        }

        return (new SessionModel())->getVisitorsWithIncompleteSourceChannel($args);
    }

    public function updateVisitor($id, $data)
    {
        return $this->legacy->updateVisitor($id, $data);
    }

    public function getReferrers($args = [])
    {
        if (false) {
            return $this->legacy->getReferrers($args);
        }

        return (new SessionModel())->getReferrers($args);
    }

    public function countReferrers($args = [])
    {
        return $this->legacy->countReferrers($args);
    }

    /**
     * Returns visitors, visits and referrers for the past given days, separated daily.
     *
     * @param array $args Arguments to include in query (e.g. `date`, `post_type`, `post_id`, etc.).
     *
     * @return  array   Format: `[{'date' => "STRING", 'visitors' => INT, 'visits' => INT, 'referrers' => INT}, ...]`.
     *
     * @todo    Make the query faster for date ranges greater than one month.
     */
    public function getDailyStats($args = [])
    {
        return $this->legacy->getDailyStats($args);
    }

    public function getVisitorHits($args = [])
    {
        return $this->legacy->getVisitorHits($args);
    }
}
