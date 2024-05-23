<?php

namespace WP_Statistics\Async;

use WP_Statistics\Service\Posts\WordCount;

class CalculatePostWordsCount extends \WP_Background_Process
{
    /**
     * @var string
     */
    protected $prefix = 'wp_statistics';

    /**
     * @var string
     */
    protected $action = 'calculate_post_words_count_background_process';

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
        $postId = $item['post_id'];
        $post   = get_post($postId);

        if ($post && $post->post_status == 'publish') {
            $wordCountClass = new WordCount();
            $wordCount      = $wordCountClass->calculate($post->post_content);

            $wordCountClass->saveWordCount($postId, $wordCount);
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

        // Show notice to user or perform some other arbitrary task...
    }
}
