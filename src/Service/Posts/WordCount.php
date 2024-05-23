<?php

namespace WP_Statistics\Service\Posts;

class WordCount
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
        return str_word_count($text);
    }

    /**
     * Save the word count as post meta.
     *
     * @param int $postId
     * @param int $wordCount
     */
    public function saveWordCount($postId, $wordCount)
    {
        update_post_meta($postId, self::WORDS_COUNT_META_KEY, $wordCount);
    }

    /**
     * Remove wps_words_count meta when the post is deleted
     *
     * @param int $postId
     * @param \WP_Post $post
     */
    public function removeWordsCountMeta($postId, $post)
    {
        delete_post_meta($postId, self::WORDS_COUNT_META_KEY);
    }

    /**
     * Handle the save post action to calculate and save word count.
     *
     * @param int $postId
     * @param WP_Post $post
     * @param bool $update
     */
    public function handleSavePost($postId, $post, $update)
    {
        if ($post->post_type == 'post' && $post->post_status == 'publish') {
            $wordCount = $this->calculate($post->post_content);
            $this->saveWordCount($postId, $wordCount);
        }
    }
}
