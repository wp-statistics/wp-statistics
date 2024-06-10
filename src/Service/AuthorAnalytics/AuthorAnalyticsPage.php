<?php

namespace WP_Statistics\Service\AuthorAnalytics;

use WP_Statistics\Abstracts\MultiViewPage;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\AuthorAnalytics\Views\AuthorsView;
use WP_Statistics\Service\AuthorAnalytics\Views\TabsView;
use WP_Statistics\Service\AuthorAnalytics\Views\SingleAuthorView;
use WP_Statistics\Service\Posts\WordCount;
use WP_Statistics\Utils\Request;

class AuthorAnalyticsPage extends MultiViewPage
{
    protected $pageSlug = 'author-analytics';

    protected $defaultView = 'tabs';
    
    protected $views = [
        'tabs'          => TabsView::class,
        'authors'       => AuthorsView::class,
        'single-author' => SingleAuthorView::class
    ];

    /**
     * @var WordCount
     */
    private $wordsCount;

    public function __construct()
    {
        parent::__construct();
    }

    protected function init()
    {
        $this->wordsCount = new WordCount();

        $this->disableScreenOption();
        $this->processWordCountMeta();
        $this->processWordCountInBackground();
        $this->singleAuthorNotices();
    }

    private function singleAuthorNotices()
    {
        if (Request::compare('type', 'single-author')) {
            $postType = Request::get('pt', 'post');

            if (!post_type_supports($postType, 'author')) {
                $message = sprintf(
                    __('This post type doesn’t support authors, affecting the accuracy of author performance data. To fix this, please enable author support for this post type. <a href="%s">Learn more</a>.', 'wp-statistics'),
                    'https://wp-statistics.com/resources/enabling-author-support-for-your-post-types/?utm_source=wp-statistics&utm_medium=link&utm_campaign=doc'
                );
    
                Notice::addNotice($message, 'unsupported_post_type_author', 'warning', false);
            }
        }
    }

    /**
     * Check for posts without word count meta key
     *
     * @return void
     */
    private function processWordCountMeta()
    {
        if (count($this->wordsCount->getPostsWithoutWordCountMeta()) && !Option::getOptionGroup('jobs', 'word_count_process_started')) {
            $actionUrl = add_query_arg(
                [
                    'action' => 'process_word_count',
                    'nonce'  => wp_create_nonce('process_word_count_nonce')
                ],
                Menus::admin_url('author-analytics')
            );

            $message = sprintf(
                __('Please <a href="%s">click here</a> to process the word count in the background. This is necessary for accurate analytics.', 'wp-statistics'),
                esc_url($actionUrl)
            );

            Notice::addNotice($message, 'word_count_prompt', 'info', false);
        }
    }

    private function processWordCountInBackground()
    {
        // Check the action and nonce
        if (!Request::compare('action', 'process_word_count')) {
            return;
        }

        check_admin_referer('process_word_count_nonce', 'nonce');

        // Check if already processed
        if (Option::getOptionGroup('jobs', 'word_count_process_started')) {
            Notice::addFlashNotice(__('Word count processing is already started.', 'wp-statistics'));

            wp_redirect(Menus::admin_url('author-analytics'));
            exit;
        }

        // Initialize and dispatch the CalculatePostWordsCount class
        $remoteRequestAsync      = WP_Statistics()->getRemoteRequestAsync();
        $calculatePostWordsCount = $remoteRequestAsync['calculate_post_words_count'];

        foreach ($this->wordsCount->getPostsWithoutWordCountMeta() as $postId) {
            $calculatePostWordsCount->push_to_queue(['post_id' => $postId]);
        }

        // Mark as processed
        Option::saveOptionGroup('word_count_process_started', true, 'jobs');

        $calculatePostWordsCount->save()->dispatch();

        // Show notice
        //Notice::addFlashNotice(__('Word count processing started.', 'wp-statistics'));

        wp_redirect(Menus::admin_url('author-analytics'));
        exit;
    }
}
