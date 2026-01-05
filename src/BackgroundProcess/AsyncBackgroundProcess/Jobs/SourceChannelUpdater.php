<?php

namespace WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs;

use WP_Statistics\BackgroundProcess\ExtendedBackgroundProcess;
use WP_Statistics\Components\Option;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Analytics\Referrals\Referrals;
use WP_Statistics\Service\Analytics\Referrals\SourceDetector;
use WP_Statistics\Utils\Query;

class SourceChannelUpdater extends ExtendedBackgroundProcess
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
        $visitors = $item['visitors'];

        foreach ($visitors as $visitorId) {
            $visitor = Query::select([
                'visitor.referred',
                'pages.uri as first_uri'
            ])
                ->from('visitor')
                ->join('pages', ['first_page', 'pages.page_id'], [], 'LEFT')
                ->where('visitor.ID', '=', $visitorId)
                ->getRow();

            if (empty($visitor)) {
                continue;
            }

            $referrer  = $visitor->referred;
            $firstPage = $visitor->first_uri;

            $sourceDetector = new SourceDetector($referrer, $firstPage);

            Query::update('visitor')
                ->set([
                    'source_channel' => $sourceDetector->getChannel(),
                    'source_name'    => $sourceDetector->getName(),
                    'referred'       => Referrals::getUrl($referrer)
                ])
                ->where('ID', '=', $visitorId)
                ->execute();
        }

        return false;
    }

    public function is_initiated()
    {
        return Option::getGroupValue('jobs', 'update_source_channel_process_initiated', false);
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
