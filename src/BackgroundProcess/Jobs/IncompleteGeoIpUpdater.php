<?php

namespace WP_Statistics\BackgroundProcess\Jobs;

use WP_Statistics\BackgroundProcess\ExtendedBackgroundProcess;
use WP_Statistics\Components\Ip;
use WP_Statistics\Components\Option;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Geolocation\GeolocationFactory;
use WP_Statistics\Utils\Query;

class IncompleteGeoIpUpdater extends ExtendedBackgroundProcess
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
        $visitors = $item['visitors'];

        foreach ($visitors as $visitorId) {
            $visitor = Query::select(['visitor.ip'])
                ->from('visitor')
                ->where('visitor.ID', '=', $visitorId)
                ->getRow();

            if (empty($visitor) || empty($visitor->ip)) {
                continue;
            }

            $ip       = Ip::isHashed($visitor->ip) ? '' : $visitor->ip;
            $location = GeolocationFactory::getLocation($ip);

            Query::update('visitor')
                ->set([
                    'location'  => $location['country_code'],
                    'city'      => $location['city'],
                    'region'    => $location['region'],
                    'continent' => $location['continent'],
                ])
                ->where('ID', '=', $visitorId)
                ->execute();
        }

        return false;
    }

    public function is_initiated()
    {
        return Option::getGroup('jobs', 'update_geoip_process_initiated');
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

        // Show notice to user
        Notice::addFlashNotice(__('GeoIP update for incomplete visitors processed successfully.', 'wp-statistics'));
    }
}
