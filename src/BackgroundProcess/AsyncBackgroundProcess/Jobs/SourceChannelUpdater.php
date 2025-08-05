<?php

namespace WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs;

use WP_Statistics\Models\VisitorsModel;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Analytics\Referrals\Referrals;
use WP_Statistics\Service\Analytics\Referrals\SourceDetector;
use WP_STATISTICS\WP_Background_Process;

class SourceChannelUpdater extends WP_Background_Process
{
    /**
     * @var string
     */
    protected $prefix = 'wp_statistics';

    /**
     * @var string
     */
    protected $action = 'update_visitors_source_channel';

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

            $referrer   = $visitor->referred;
            $firstPage  = $visitor->first_uri;

            $sourceDetector = new SourceDetector($referrer, $firstPage);

            $visitorModel->updateVisitor($visitorId, [
                'source_channel'    => $sourceDetector->getChannel(),
                'source_name'       => $sourceDetector->getName(),
                'referred'          => Referrals::getUrl($referrer)
            ]);
        }

        return false;
    }

    public function is_initiated()
    {
        return Option::getOptionGroup('jobs', 'update_source_channel_process_initiated', false);
    }

    /**
     * Complete processing.
     */
    protected function complete()
    {
        parent::complete();

        // Show notice to user
        Notice::addFlashNotice(__('Source channel update for visitors processed successfully.', 'wp-statistics'));
    }
}
