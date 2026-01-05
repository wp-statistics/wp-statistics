<?php

namespace WP_Statistics\BackgroundProcess\AsyncBackgroundProcess;

use WP_Statistics\Components\DateTime;
use WP_Statistics\Components\Option;
use WP_Statistics\Service\Admin\Posts\WordCountService;
use WP_Statistics\Service\Geolocation\GeolocationFactory;
use WP_Statistics\Service\Resources\ResourcesFactory;
use WP_Statistics\Utils\Query;

class BackgroundProcessFactory
{
    /**
     * Process word count for posts.
     *
     * @return void
     */
    public static function processWordCountForPosts()
    {
        $calculatePostWordsCount = WP_Statistics()->getBackgroundProcess('calculate_post_words_count');

        if ($calculatePostWordsCount->is_active()) {
            return;
        }

        $wordCount               = new WordCountService();
        $postsWithoutWordCount   = $wordCount->getPostsWithoutWordCountMeta();

        $batchSize = 100;
        $batches   = array_chunk($postsWithoutWordCount, $batchSize);

        foreach ($batches as $batch) {
            $calculatePostWordsCount->push_to_queue(['posts' => $batch]);
        }

        // Mark as processed
        Option::updateGroup('word_count_process_initiated', true, 'jobs');

        $calculatePostWordsCount->save()->dispatch();
    }

    /**
     * Batch update incomplete GeoIP info for visitors.
     *
     * @return void
     */
    public static function batchUpdateIncompleteGeoIpForVisitors()
    {
        $updateIncompleteVisitorsLocations = WP_Statistics()->getBackgroundProcess('update_unknown_visitor_geoip');

        if ($updateIncompleteVisitorsLocations->is_active()) {
            return;
        }

        $privateCountry                 = GeolocationFactory::getProviderInstance()->getPrivateCountryCode();
        $visitorsWithIncompleteLocation = Query::select(['ID'])
            ->from('visitor')
            ->whereRaw(
                "(location = ''
            OR location = %s
            OR location IS NULL
            OR continent = ''
            OR continent IS NULL
            OR (continent = location))
            AND ip NOT LIKE '#hash#%%'",
                [$privateCountry]
            )
            ->getAll();

        $visitorsWithIncompleteLocation = wp_list_pluck($visitorsWithIncompleteLocation, 'ID');

        // Define the batch size
        $batchSize = 100;
        $batches   = array_chunk($visitorsWithIncompleteLocation, $batchSize);

        // Push each batch to the queue
        foreach ($batches as $batch) {
            $updateIncompleteVisitorsLocations->push_to_queue(['visitors' => $batch]);
        }

        // Initiate the process
        Option::updateGroup('update_geoip_process_initiated', true, 'jobs');

        // Save the queue and dispatch it
        $updateIncompleteVisitorsLocations->save()->dispatch();
    }

    /**
     * Download/Update geolocation database using.
     *
     * @return void
     */
    public static function downloadGeolocationDatabase()
    {
        $provider        = GeolocationFactory::getProviderInstance();
        $downloadProcess = WP_Statistics()->getBackgroundProcess('geolocation_database_download');

        if ($downloadProcess->is_active()) {
            return;
        }

        // Queue download process
        $downloadProcess->push_to_queue(['provider' => $provider]);
        $downloadProcess->save()->dispatch();
    }

    /**
     * Batch update incomplete Source Channel info for visitors.
     *
     * @return void
     */
    public static function batchUpdateSourceChannelForVisitors()
    {
        @ini_set('memory_limit', '-1');

        $updateIncompleteVisitorsSourceChannels = WP_Statistics()->getBackgroundProcess('update_visitors_source_channel');

        if ($updateIncompleteVisitorsSourceChannels->is_active()) {
            return;
        }

        $visitorsWithIncompleteSourceChannel = Query::select(['visitor.ID'])
            ->from('visitor')
            ->whereNotNull('referred')
            ->whereNull('source_channel')
            ->whereNull('source_name')
            ->getAll();

        $visitorsWithIncompleteSourceChannel = $visitorsWithIncompleteSourceChannel ? wp_list_pluck($visitorsWithIncompleteSourceChannel, 'ID') : [];

        // Define the batch size
        $batchSize = 100;
        $batches   = array_chunk($visitorsWithIncompleteSourceChannel, $batchSize);

        // Push each batch to the queue
        foreach ($batches as $batch) {
            $updateIncompleteVisitorsSourceChannels->push_to_queue(['visitors' => $batch]);
        }

        // Initiate the process
        Option::updateGroup('update_source_channel_process_initiated', true, 'jobs');

        // Save the queue and dispatch it
        $updateIncompleteVisitorsSourceChannels->save()->dispatch();
    }

    /**
     * Batch calculation of per-resource daily summaries.
     *
     * Retrieves the set of resource URI IDs for the target day,
     * splits them into manageable batches, and enqueues a background job
     * (`calculate_daily_summary`) for each batch.
     *
     * @return void
     * @since 15.0.0
     */
    public static function processDailySummary()
    {
        $calculateDailySummary = WP_Statistics()->getBackgroundProcess('calculate_daily_summary');

        if ($calculateDailySummary->is_active()) {
            return;
        }

        $todayResources = self::getResourceUriIdsByDate();

        $batchSize = 50;
        $batches   = array_chunk($todayResources, $batchSize);

        foreach ($batches as $batch) {
            $calculateDailySummary->push_to_queue(['ids' => $batch]);
        }

        $calculateDailySummary->save()->dispatch();
    }

    /**
     * List distinct resource URI IDs that occurred on yesterday.
     *
     * Returns the set of `views.resource_uri_id` values among sessions whose
     * `started_at` date matches yesterday. Sessions without any view rows
     * are represented by the sentinel `''`.
     *
     * @return array<int|string> Ordered list of resource URI IDs (may include '').
     */
    private static function getResourceUriIdsByDate()
    {
        $dateRange = DateTime::getUtcRangeForLocalDate('yesterday');
        $startUtc  = $dateRange['startUtc'];
        $endUtc    = $dateRange['endUtc'];

        $rows = Query::select("DISTINCT COALESCE(views.resource_uri_id, '') AS resource_uri_id")
            ->from('sessions')
            ->join('views', ['views.session_id', 'sessions.ID'], null, 'LEFT')
            ->where('sessions.started_at', '>=', $startUtc)
            ->where('sessions.started_at', '<', $endUtc)
            ->orderBy('resource_uri_id')
            ->getAll();

        if (empty($rows)) {
            return [];
        }

        return array_column($rows, 'resource_uri_id');
    }

    /**
     * Queue calculation of the site-wide daily summary totals.
     *
     * Schedules a single background task that aggregates the daily totals
     * (e.g., visitors, sessions, views, duration) across all resources for
     * the target day.
     *
     * @return void
     * @since 15.0.0
     */
    public static function processDailySummaryTotal()
    {
        $calculateDailySummaryTotal = WP_Statistics()->getBackgroundProcess('calculate_daily_summary_total');

        if ($calculateDailySummaryTotal->is_active()) {
            return;
        }

        $calculateDailySummaryTotal->push_to_queue(['is_total' => true]);
        $calculateDailySummaryTotal->save()->dispatch();
    }

    /**
     * Update cache fields for all resources using memory-efficient batching.
     *
     * This method uses cursor-based pagination to process resources in batches
     * without loading all IDs into memory at once, preventing memory limit issues
     * when dealing with large resource tables.
     *
     * @return void
     */
    public static function updateResourceCacheFields()
    {
        $updateResource = WP_Statistics()->getBackgroundProcess('update_resouce_cache_fields');

        if ($updateResource->is_active()) {
            return;
        }

        $totalResourcesCount = ResourcesFactory::countResources(true);

        if ($totalResourcesCount === 0) {
            Option::updateGroup('update_resouce_cache_fields_initiated', true, 'jobs');
            return;
        }

        $batchSize  = 500;
        $totalPages = (int) ceil($totalResourcesCount / $batchSize);
        $flushEvery = 200;

        for ($page = 0; $page < $totalPages; $page++) {
            $offset = $page * $batchSize;

            $updateResource->push_to_queue([
                'offset' => $offset,
                'limit'  => $batchSize,
            ]);

            if ($page > 0 && $page % $flushEvery === 0) {
                $updateResource->save();
            }
        }

        Option::updateGroup('update_resouce_cache_fields_initiated', true, 'jobs');

        $updateResource->save()->dispatch();
    }

    // Add other static methods for different background processes as needed
}
