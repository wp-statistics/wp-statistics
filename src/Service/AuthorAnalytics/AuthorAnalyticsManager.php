<?php

namespace WP_Statistics\Service\AuthorAnalytics;

use WP_STATISTICS\Helper;

class AuthorAnalyticsManager
{
    public function __construct()
    {
        add_action('save_post', [$this, 'addWordsCountMeta'], 99, 3);
        add_filter('wp_statistics_admin_menu_list', [$this, 'addMenuItem']);
    }

    /**
     * Add menu item
     *
     * @param array $items
     * @return array
     */
    public function addMenuItem($items)
    {
        $newItem = [
            'author_analytics' => [
                'sub'       => 'overview',
                'pages'     => array('pages' => true),
                'title'     => esc_html__('Author Analytics', 'wp-statistics'),
                'page_url'  => 'author-analytics',
                'callback'  => AuthorAnalyticsPage::class
            ]
        ];

        array_splice($items, 13, 0, $newItem);

        return $items;
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
        // Return if it's not a public post type
        if (!in_array($post->post_type, Helper::get_list_post_type())) return;

        $wordsCount = str_word_count(strip_tags($post->post_content));
        update_post_meta($id, 'wps_words_count', $wordsCount);
    }
}
