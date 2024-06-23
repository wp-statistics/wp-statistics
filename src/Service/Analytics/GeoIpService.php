<?php

namespace WP_Statistics\Service\Analytics;

use WP_Statistics\Models\VisitorsModel;

class GeoIpService
{
    /**
     * Update Incomplete Geo IP Info for Visitors
     *
     * @return void
     */
    public function batchUpdateIncompleteGeoIpForVisitors()
    {
        $visitorModel                   = new VisitorsModel();
        $visitorsWithIncompleteLocation = $visitorModel->getVisitorsWithIncompleteLocation();

        // Initialize and dispatch the UpdateIncompleteVisitorsLocations class
        $remoteRequestAsync                = WP_Statistics()->getBackgroundProcess();
        $updateIncompleteVisitorsLocations = $remoteRequestAsync['update_unknown_visitor_geoip'];

        // Define the batch size
        $batchSize = 100;
        $batches   = array_chunk($visitorsWithIncompleteLocation, $batchSize);

        // Push each batch to the queue
        foreach ($batches as $batch) {
            $updateIncompleteVisitorsLocations->push_to_queue(['visitors' => $batch]);
        }

        // Save the queue and dispatch it
        $updateIncompleteVisitorsLocations->save()->dispatch();
    }
}
