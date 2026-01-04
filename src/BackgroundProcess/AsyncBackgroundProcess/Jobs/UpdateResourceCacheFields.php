<?php

namespace WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs;

use WP_Statistics\BackgroundProcess\ExtendedBackgroundProcess;
use WP_STATISTICS\Option;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Resources\Core\ResourceDetector;
use WP_Statistics\Utils\PostType;
use WP_Statistics\Utils\Query;

class UpdateResourceCacheFields extends ExtendedBackgroundProcess
{
    /**
     * @var string
     */
    protected $prefix = 'wp_statistics';

    /**
     * @var string
     */
    protected $action = 'update_resouce_cache_fields';

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
        if (!isset($item['offset']) || !isset($item['limit'])) {
            return false;
        }

        $resources = Query::select('ID')
            ->from('resources')
            ->where('resource_type', 'IN', PostType::getQueryableTypes())
            ->perPage($item['offset'], $item['limit'])
            ->getAll();

        if (empty($resources)) {
            return false;
        }

        $resourceIds = wp_list_pluck($resources, 'ID');

        foreach ($resourceIds as $resourceId) {
            $record = RecordFactory::resource()->get([
                'ID' => $resourceId,
            ]);

            if (empty($record)) {
                continue;
            }

            $resource = (new ResourceDetector($record->resource_id, $record->resource_type));

            RecordFactory::resource($record)->update([
                'cached_title'     => $resource->getCachedTitle(),
                'cached_author_id' => $resource->getCachedAuthorId(),
                'cached_terms'     => $resource->getCachedTerms(),
                'cached_date'      => $resource->getCachedDate(),
                'resource_meta'    => $resource->getResourceMeta(),
                'language'         => $resource->getLanguage(),
                'is_deleted'       => $resource->getIsDeleted(),
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

        // Show notice to user
        Notice::addFlashNotice(__('Resource table is updated successfully.', 'wp-statistics'));
    }

    /**
     * Check if we've already queued this job.
     *
     * @return bool
     */
    public function is_initiated()
    {
        return Option::getOptionGroup('jobs', 'update_resouce_cache_fields_initiated', false);
    }
}
