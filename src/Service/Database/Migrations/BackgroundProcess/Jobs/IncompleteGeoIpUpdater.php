<?php

namespace WP_Statistics\Service\Database\Migrations\BackgroundProcess\Jobs;

use WP_Statistics\Abstracts\BaseBackgroundProcess;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_STATISTICS\Menus;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Geolocation\GeolocationFactory;
use WP_Statistics\Service\Geolocation\Provider\MaxmindGeoIPProvider;

class IncompleteGeoIpUpdater extends BaseBackgroundProcess
{
    /**
     * Background-process action slug for this job.
     *
     * @var string
     */
    protected $action = 'update_unknown_visitor_geoip';

    /**
     * Initiated key for option storage.
     *
     * @var string
     */
    protected $initiatedKey = 'update_geoip_process_initiated';

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        add_action('admin_init', [$this, 'localizeJobTexts']);
    }

    /**
     * Localize the job's title and description for display in the admin UI.
     *
     * @return void
     */
    public function localizeJobTexts()
    {
        $this->setSuccessNotice(esc_html__('GeoIP update for incomplete visitors processed successfully.', 'wp-statistics'));
        $this->setJobTitle(esc_html__('Update GeoIP for visitors without location data', 'wp-statistics'));
    }

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
            $visitor = $visitorModel->getVisitorData([
                'visitor_id' => $visitorId,
                'user_info'  => false,
                'page_info'  => false,
                'fields'     => ['visitor.ip']
            ]);

            $location = GeolocationFactory::getLocation($visitor->getIP(), MaxmindGeoIPProvider::class);

            $visitorModel->updateVisitor($visitorId, [
                'location'  => $location['country_code'],
                'city'      => $location['city'],
                'region'    => $location['region'],
                'continent' => $location['continent'],
            ]);
        }

        $this->setProcessed($visitors);

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

        $this->clearTotalAndProcessed();
    }

    /**
     * Show initial notice to start the background process.
     *
     * When `$force` is true, the generated action URL includes `force=1` so the
     * manager can re-initiate the job even if it has already been started.
     *
     * @param bool $force Whether to include the `force` flag to restart the job. Default false.
     * @return void
     */
    public function initialNotice($force = false)
    {
        if ($this->isInitiated() || $this->is_active() || !Menus::in_page('geographic')) {
            return;
        }

        $actionUrl = $this->getActionUrl($force);

        $message = sprintf(
            __('Detected visitors without location data. Please <a href="%s">click here</a> to update the geographic data in the background. This is necessary for accurate analytics.', 'wp-statistics'),
            esc_url($actionUrl),
            '' // compatibility with old translations
        );

        Notice::addNotice($message, 'update_unknown_visitor_geoip', 'info', false);
    }

    /**
     * Initiate the background process to calculate word counts for posts.
     *
     * @return void
     */
    public function process()
    {
        if ($this->is_active()) {
            Notice::addNotice(
                __('Geographic data update is already in progress.', 'wp-statistics'),
                'running_geoip_process_notice',
                'info',
                true
            );
            return;
        }

        $visitorModel                   = new VisitorsModel();
        $visitorsWithIncompleteLocation = $visitorModel->getVisitorsWithIncompleteLocation();

        $this->setTotal($visitorsWithIncompleteLocation);

        $visitorsWithIncompleteLocation = wp_list_pluck($visitorsWithIncompleteLocation, 'ID');

        $batchSize = 100;
        $batches   = array_chunk($visitorsWithIncompleteLocation, $batchSize);

        foreach ($batches as $batch) {
            $this->push_to_queue(['visitors' => $batch]);
        }

        $this->setInitiated();
        $this->save()->dispatch();
    }
}
