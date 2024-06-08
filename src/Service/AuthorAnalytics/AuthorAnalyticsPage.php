<?php

namespace WP_Statistics\Service\AuthorAnalytics;

use WP_STATISTICS\Menus;
use WP_Statistics\Components\Singleton;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\AuthorAnalytics\Views\AuthorsView;
use WP_Statistics\Service\AuthorAnalytics\Views\TabsView;
use WP_Statistics\Service\AuthorAnalytics\Views\SingleAuthorView;
use WP_Statistics\Service\Posts\WordCount;
use Exception;

class AuthorAnalyticsPage extends Singleton
{
    /**
     * List of all author analytics views
     *
     * @var array
     */
    private $views = [
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
        // Check if in Author Analytics page
        if (Menus::in_page('author-analytics')) {
            $this->wordsCount = new WordCount();

            $this->preparePageRequirements();
            $this->processWordCountMeta();
            $this->processWordCountInBackground();
        }
    }

    private function preparePageRequirements()
    {
        add_filter('screen_options_show_screen', '__return_false');
    }

    /**
     * Check for posts without word count meta key
     *
     * @return void
     */
    private function processWordCountMeta()
    {
        if (count($this->wordsCount->getPostsWithoutWordCountMeta()) && !Option::getOptionGroup('jobs', 'word_count_process_started')) {
            $actionUrl  = add_query_arg(
                [
                    'action' => 'process_word_count',
                    'nonce'  => wp_create_nonce('process_word_count_nonce')
                ],
                Menus::admin_url('author-analytics')
            );

            $message    = sprintf(
                __('Please <a href="%s">click here</a> to process the word count in the background. This is necessary for accurate analytics.', 'wp-statistics'),
                esc_url($actionUrl)
            );

            Notice::addNotice($message, 'word_count_prompt', 'info', false);
        }
    }

    private function processWordCountInBackground()
    {
        // Check the action and nonce
        if (!isset($_GET['action']) || $_GET['action'] !== 'process_word_count') {
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

    /**
     * Get all views
     *
     * @return array
     */
    public function getViews()
    {
        return apply_filters('wp_statistics_author_analytics_views', $this->views);
    }


    /**
     * Get current view
     *
     * @return string
     */
    public function getCurrentView()
    {
        $views = $this->getViews();

        // Set current view to tabs by default
        $currentView = 'tabs';

        // Get page type
        $pageType = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : false;

        // If page type is set and is a valid view
        if ($pageType && array_key_exists($pageType, $views)) {
            $currentView = $pageType;
        }

        return $currentView;
    }

    /**
     * Display HTML
     */
    public function view()
    {

        // Get all views
        $views = $this->getViews();

        // Get current view
        $currentView = $this->getCurrentView();

        // Check if the view does not exist, throw exception
        if (!isset($views[$currentView])) {
            throw new Exception(esc_html__('View is not valid.', 'wp-statistics'));
        }

        // Check if the class does not have view method, throw exception
        if (!method_exists($views[$currentView], 'view')) {
            throw new Exception(sprintf(esc_html__('View method is not defined within %s class.', 'wp-statistics'), $currentView));
        }

        // Instantiate the view class and render content
        $class = new $views[$currentView];
        $class->view();
    }
}
