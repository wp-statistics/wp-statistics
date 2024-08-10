<?php

namespace WP_Statistics\Async;

use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\Posts\WordCountService;
use WP_STATISTICS\WP_Background_Process;

class CalculatePostWordsCount extends WP_Background_Process
{
    /**
     * @var string
     */
    protected $prefix = 'wp_statistics';

    /**
     * @var string
     */
    protected $action = 'calculate_post_words_count';

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
        $postId         = $item['post_id'];
        $post           = get_post($postId);
        $wordCountClass = new WordCountService();

        $wordCountClass->handleSavePost($postId, $post);

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
        Option::deleteOptionGroup('word_count_process_started', 'jobs');

        // Show notice to user
        Notice::addFlashNotice(__('Word count processed successfully.', 'wp-statistics'));
    }
}
