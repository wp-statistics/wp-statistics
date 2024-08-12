<?php

namespace WP_Statistics\Async;

use WP_STATISTICS\GeoIP;
use WP_Statistics\Models\VisitorsModel;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_STATISTICS\WP_Background_Process;

class IncompleteGeoIpUpdater extends WP_Background_Process
{
    /**
     * @var string
     */
    protected $prefix = 'wp_statistics';

    /**
     * @var string
     */
    protected $action = 'update_unknown_visitor_geoip';

    /**
     * Perform task with queued item.
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param mixed $item Queue item to iterate over.
     *
     * @return mixed
     */
    protected function task($item)
    {
        $visitors     = $item['visitors'];
        $visitorModel = new VisitorsModel();

        foreach ($visitors as $visitor) {
            $country = GeoIP::getCountry($visitor->ip);
            $city    = GeoIP::getCity($visitor->ip, true);

            $visitorModel->updateVisitor($visitor->ID, [
                'location'  => $country,
                'city'      => $city['city'] == 'Unknown' ? null : $city['city'],
                'region'    => $city['region'] == 'Unknown' ? null : $city['region'],
                'continent' => $city['continent'] == 'Unknown' ? null : $city['continent'],
            ]);
        }

        return false;
    }

    /**
     * Complete processing.
     *
     * Override if applicable, but ensure that the below actions are
     * performed, or, call parent::complete().
     */
    protected function complete()
    {
        parent::complete();

        // Delete option
        Option::deleteOptionGroup('geo_ip_process_started', 'jobs');

        // Show notice to user
        Notice::addFlashNotice(__('GeoIP update for incomplete visitors processed successfully.', 'wp-statistics'));
    }
}
