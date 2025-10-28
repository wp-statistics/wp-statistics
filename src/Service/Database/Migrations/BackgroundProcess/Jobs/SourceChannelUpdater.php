<?php

namespace WP_Statistics\Service\Database\Migrations\BackgroundProcess\Jobs;

use WP_Statistics\Abstracts\BaseBackgroundProcess;
use WP_STATISTICS\Menus;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Analytics\Referrals\Referrals;
use WP_Statistics\Service\Analytics\Referrals\SourceDetector;

class SourceChannelUpdater extends BaseBackgroundProcess
{
    /**
     * Background-process action slug for this job.
     *
     * @var string
     */
    protected $action = 'update_visitors_source_channel';

    /**
     * Initiated key for option storage.
     *
     * @var string
     */
    protected $initiatedKey = 'update_source_channel_process_initiated';

    /**
     * Constructor to initialize the background process.
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
        $this->setSuccessNotice(esc_html__('Source channel update for visitors processed successfully.', 'wp-statistics'));
        $this->setJobTitle(esc_html__('Update incomplete source channels', 'wp-statistics'));
    }

    /**
     * Perform task with queued item.
     *
     * @param mixed $item Queue item to iterate over.
     * @return mixed
     */
    protected function task($item)
    {
        $visitors     = $item['visitors'];
        $visitorModel = new VisitorsModel();

        foreach ($visitors as $visitorId) {
            $visitor = $visitorModel->getVisitorData([
                'visitor_id' => $visitorId,
                'user_info'  => false,
                'page_info'  => true,
                'decorate'   => false,
                'fields'     => ['visitor.referred']
            ]);

            $referrer  = $visitor->referred;
            $firstPage = $visitor->first_uri;

            $sourceDetector = new SourceDetector($referrer, $firstPage);

            $visitorModel->updateVisitor($visitorId, [
                'source_channel' => $sourceDetector->getChannel(),
                'source_name'    => $sourceDetector->getName(),
                'referred'       => Referrals::getUrl($referrer)
            ]);
        }

        $this->setProcessed($visitors);

        return false;
    }

    /**
     * Complete processing.
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
        if ($this->isInitiated() || $this->is_active() || !Menus::in_page('referrals')) {
            return;
        }

        $actionUrl = $this->getActionUrl($force);

        $message = sprintf(
            __('We’ve updated the referral structure in this version. To ensure accurate reports, please initiate the background data process <a href="%s">by clicking here</a>.', 'wp-statistics'),
            esc_url($actionUrl)
        );

        Notice::addNotice($message, 'update_visitors_source_channel_notice', 'info', false);
    }

    /**
     * Initiate the background process to calculate word counts for posts.
     *
     * @return void
     */
    public function process()
    {
        if ($this->is_active()) {
            return;
        }

        @ini_set('memory_limit', '-1');

        $visitorModel                        = new VisitorsModel();
        $visitorsWithIncompleteSourceChannel = $visitorModel->getVisitorsWithIncompleteSourceChannel();

        $this->setTotal($visitorsWithIncompleteSourceChannel);

        $visitorsWithIncompleteSourceChannel = wp_list_pluck($visitorsWithIncompleteSourceChannel, 'ID');

        $batchSize = 100;
        $batches   = array_chunk($visitorsWithIncompleteSourceChannel, $batchSize);

        foreach ($batches as $batch) {
            $this->push_to_queue(['visitors' => $batch]);
        }

        $this->setInitiated();
        $this->save()->dispatch();
    }
}
