<?php

namespace WP_Statistics\Async;

use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Models\VisitorsModel;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Geolocation\GeolocationFactory;
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

        foreach ($visitors as $visitorId) {
            /** @var VisitorDecorator $visitor */
            $visitor    = $visitorModel->getVisitorData([
                'visitor_id' => $visitorId,
                'user_info'  => false,
                'page_info'  => false,
                'fields'     => ['visitor.ip']
            ]);

            $location   = GeolocationFactory::getLocation($visitor->getIP());

            $visitorModel->updateVisitor($visitorId, [
                'location'  => $location['country_code'],
                'city'      => $location['city'],
                'region'    => $location['region'],
                'continent' => $location['continent'],
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
