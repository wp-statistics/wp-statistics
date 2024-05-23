<?php

namespace WP_Statistics\Service\Posts;

use WP_Statistics\Async\CalculatePostWordsCount;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;

class PostsManager
{
    public function __construct()
    {
        add_action('save_post', [$this, 'addWordsCountCallback'], 99, 3);
        add_action('delete_post', [$this, 'removeWordsCountCallback'], 99, 2);
    }

    /**
     * Count the number of words in a post and store it as a meta value
     *
     * @param $postId
     * @param \WP_Post $post
     * @param bool $update
     */
    public function addWordsCountCallback($postId, $post, $update)
    {
        $wordsCount = new WordCount();
        $wordsCount->handleSavePost($postId, $post, $update);
    }


    /**
     * Remove wps_words_count meta when the post is deleted
     *
     * @param $postId
     * @param \WP_Post $post
     */
    public function removeWordsCountCallback($postId, $post)
    {
        $wordsCount = new WordCount();
        $wordsCount->removeWordsCountMeta($postId, $post);
    }

    public function processWordCount()
    {
        // Check if already processed
        if (Option::get('word_count_processed_notice')) { // todo maybe better option name like wp_statistics_notices[word_count_processed]
            return;
        }

        // Initialize and dispatch the CalculatePostWordsCount class
        $calculatePostWordsCount = new CalculatePostWordsCount();
        $posts                   = get_posts([
            'post_type'   => ['post', 'page'],
            'post_status' => 'publish',
            'numberposts' => -1,
            'meta_query'  => [
                'key'     => WordCount::WORDS_COUNT_META_KEY,
                'compare' => 'NOT EXISTS'
            ]
        ]);

        foreach ($posts as $post) {
            $calculatePostWordsCount->push_to_queue(['post_id' => $post->ID]);
        }

        $calculatePostWordsCount->save()->dispatch();

        // Mark as processed
        Option::update('word_count_processed_notice', true);

        // Display admin notice
        Helper::addAdminNotice(__('Word count processing started.', 'wp-statistics'));
    }
}
