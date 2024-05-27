<?php

namespace WP_Statistics\Service\AuthorAnalytics;

use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Posts\WordCount;

class AuthorAnalyticsManager
{
    public function __construct()
    {
        add_filter('wp_statistics_admin_menu_list', [$this, 'addMenuItem']);
        add_filter('wp_ajax_background_word_count_process', [$this, 'processWordCountInBackground']);
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
                'sub'      => 'overview',
                'pages'    => array('pages' => true),
                'title'    => esc_html__('Author Analytics', 'wp-statistics'),
                'page_url' => 'author-analytics',
                'callback' => AuthorAnalyticsPage::class
            ]
        ];

        array_splice($items, 13, 0, $newItem);

        return $items;
    }

    public function processWordCountInBackground()
    {
        // Check if already processed
        if (Option::get("wp_statistics_jobs['word_count_processed']")) { // todo
            return;
        }

        // Initialize and dispatch the CalculatePostWordsCount class
        $remoteRequestAsync      = WP_Statistics()->getRemoteRequestAsync();
        $calculatePostWordsCount = $remoteRequestAsync['calculate_post_words_count'];
        $wordsCount              = new WordCount();

        foreach ($wordsCount->getPostsWithoutWordCountMeta() as $post) {
            $calculatePostWordsCount->push_to_queue(['post_id' => $post->ID]);
        }

        $calculatePostWordsCount->save()->dispatch();

        // Mark as processed
        Option::update("wp_statistics_jobs['word_count_processed']", true);

        // Display admin notice
        Notice::addNotice(__('Word count processing started.', 'wp-statistics'), 'word_count_notice', false, false);
    }
}
