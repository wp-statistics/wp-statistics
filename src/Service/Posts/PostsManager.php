<?php

namespace WP_Statistics\Service\Posts;

class PostsManager
{
    const WORDS_COUNT_META_KEY = 'wps_words_count';

    public function __construct()
    {
        add_action('save_post', [$this, 'addWordsCountMeta'], 99, 3);
        add_action('delete_post', [$this, 'removeWordsCountMeta'], 99, 2);
    }
    
    /**
     * Count the number of words in a post and store it as a meta value
     *
     * @param int $post_id
     * @param \WP_Post $post
     * @param bool $update
     */
    public function addWordsCountMeta($id, $post, $update)
    {
        $wordsCount = str_word_count(strip_tags($post->post_content));

        if ($wordsCount > 0) {
            update_post_meta($id, self::WORDS_COUNT_META_KEY, $wordsCount);
        }
    }

    
    /**
     * Remove wps_words_count meta when the post is deleted
     *
     * @param int $post_id
     * @param \WP_Post $post
     */
    public function removeWordsCountMeta($id, $post)
    {
        delete_post_meta($id, self::WORDS_COUNT_META_KEY);
    }
}
