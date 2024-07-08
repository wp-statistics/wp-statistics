<?php

namespace WP_Statistics\Async;

use WP_Statistics\Models\VisitorsModel;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\Posts\WordCountService;

class BackgroundProcessFactory
{
    /**
     * Process word count for posts.
     *
     * @return void
     */
    public static function processWordCountForPosts()
    {
        // Initialize and dispatch the CalculatePostWordsCount class
        $remoteRequestAsync      = WP_Statistics()->getBackgroundProcess();
        $calculatePostWordsCount = $remoteRequestAsync['calculate_post_words_count'];
        $wordCount               = new WordCountService();

        foreach ($wordCount->getPostsWithoutWordCountMeta() as $postId) {
            $calculatePostWordsCount->push_to_queue(['post_id' => $postId]);
        }

        // Mark as processed
        Option::saveOptionGroup('word_count_process_started', true, 'jobs');

        $calculatePostWordsCount->save()->dispatch();
    }

    /**
     * Batch update incomplete GeoIP info for visitors.
     *
     * @return void
     */
    public static function batchUpdateIncompleteGeoIpForVisitors()
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

        // Mark the process as completed
        Option::saveOptionGroup('update_unknown_visitor_geoip_started', true, 'jobs');

        // Save the queue and dispatch it
        $updateIncompleteVisitorsLocations->save()->dispatch();
    }

    // Add other static methods for different background processes as needed
}
