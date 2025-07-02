<?php

namespace WP_Statistics\Service\Admin\Posts;

use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;

class WordCountService
{
    const WORDS_COUNT_META_KEY = 'wp_statistics_words_count';

    /**
     * Calculate the number of words in a given text.
     *
     * @param string $text
     * @return int
     */
    public function calculate($text)
    {
        // Strip HTML tags to ensure we only count visible text
        $text = wp_strip_all_tags($text);

        // Split the text into words and count them
        $words = explode(' ', $text);
        $words = array_filter($words);
        return count($words);
    }

    /**
     * Save the word count as post meta.
     *
     * @param int $postId
     * @param int $wordCount
     */
    public function saveWordsCount($postId, $wordCount)
    {
        update_post_meta($postId, self::WORDS_COUNT_META_KEY, $wordCount);
    }

    /**
     * Remove words count meta when the post is deleted
     *
     * @param int $postId
     * @param \WP_Post $post
     */
    public function removeWordsCountMeta($postId, $post)
    {
        delete_post_meta($postId, self::WORDS_COUNT_META_KEY);
    }

    /**
     * Get the words count meta by ID
     *
     * @param int $postId
     */
    public static function getWordsCountMeta($postId)
    {
        return get_post_meta($postId, self::WORDS_COUNT_META_KEY, true);
    }

    /**
     * Handle the save post action to calculate and save word count.
     *
     * @param int $postId
     * @param \WP_Post $post
     */
    public function handleSavePost($postId, $post)
    {
        if ($post && $post->post_status == 'publish' && in_array($post->post_type, Helper::get_list_post_type())) {
            $wordCount = $this->calculate($post->post_content);
            $this->saveWordsCount($postId, $wordCount);
        }
    }

    public function getPostsWithoutWordCountMeta()
    {
        return get_posts([
            'post_type'    => Helper::get_list_post_type(),
            'post_status'  => 'publish',
            'numberposts'  => -1,
            'meta_key'     => self::WORDS_COUNT_META_KEY,
            'meta_compare' => 'NOT EXISTS',
            'fields'       => 'ids'
        ]);
    }
}
