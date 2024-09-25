<?php

namespace WP_Statistics\Async;

use WP_STATISTICS\GeoIP;
use WP_Statistics\Models\VisitorsModel;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Analytics\Referrals\Referrals;
use WP_Statistics\Service\Analytics\Referrals\SourceDetector;
use WP_Statistics\Utils\Url;
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

        foreach ($visitors as $visitor) {
            $referrer    = $visitor->referred;
            $landingPage = $visitor->first_hit;

            $sourceDetector = new SourceDetector($referrer, $landingPage);

            $visitorModel->updateVisitor($visitor->ID, [
                'source_channel'    => $sourceDetector->getChannel(),
                'source_name'       => $sourceDetector->getName(),
                'referred'          => Referrals::getUrl($referrer)
            ]);
        }

        return false;
    }

    /**
     * Complete processing.
     */
    protected function complete()
    {
        parent::complete();

        // Set running to false
        Option::saveOptionGroup('update_source_channel_process_running', false, 'jobs');

        // Mark the process as completed
        Option::saveOptionGroup('update_source_channel_process_finished', true, 'jobs');

        // Show notice to user
        Notice::addFlashNotice(__('Source channel update for visitors processed successfully.', 'wp-statistics'));
    }
}
