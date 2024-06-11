<?php

namespace WP_Statistics\Service\Admin\Posts;

class PostsManager
{
    /**
     * @var WordCount $wordsCount
     */
    private $wordsCount;

    public function __construct()
    {
        $this->wordsCount = new WordCount();

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
        $this->wordsCount->handleSavePost($postId, $post);
    }


    /**
     * Remove wps_words_count meta when the post is deleted
     *
     * @param $postId
     * @param \WP_Post $post
     */
    public function removeWordsCountCallback($postId, $post)
    {
        $this->wordsCount->removeWordsCountMeta($postId, $post);
    }
}
