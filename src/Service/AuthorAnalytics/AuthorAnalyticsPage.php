<?php

namespace WP_Statistics\Service\AuthorAnalytics;

use WP_STATISTICS\Menus;
use WP_Statistics\Components\Singleton;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\AuthorAnalytics\Views\AuthorsView;
use WP_Statistics\Service\AuthorAnalytics\Views\PostsView;
use WP_Statistics\Service\AuthorAnalytics\Views\SingleAuthorView;
use WP_Statistics\Service\AuthorAnalytics\Views\TabsView;
use Exception;
use WP_Statistics\Service\Posts\WordCount;

class AuthorAnalyticsPage extends Singleton
{
    /**
     * List of all author analytics views
     *
     * @var array
     */
    private $views = [
        'tabs'          => TabsView::class,
        'posts'         => PostsView::class,
        'authors'       => AuthorsView::class,
        'single-author' => SingleAuthorView::class
    ];

    public function __construct()
    {
        // Check if in Author Analytics page
        if (Menus::in_page('author-analytics')) {
            add_filter('screen_options_show_screen', '__return_false');

            $wordsCount = new WordCount();

            // Check for posts without word count meta key
            if (count($wordsCount->getPostsWithoutWordCountMeta())) {
                $message = sprintf(
                    __('Please <a data-id="%s" href="#">click here</a> to process the word count in the background. This is necessary for accurate analytics.', 'wp-statistics'),
                    esc_url(admin_url('admin.php?page=author-analytics&action=process_word_count'))
                );

                Notice::addNotice($message);
            }
        }
    }

    /**
     * Get current view
     *
     * @return string
     */
    public function getCurrentView()
    {
        // Set current view to tabs by default
        $currentView = 'tabs';

        // Get page type
        $pageType = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : false;

        // If page type is set and is a valid view
        if ($pageType && array_key_exists($pageType, $this->views)) {
            $currentView = $pageType;
        }

        return $currentView;
    }

    /**
     * Display HTML
     */
    public function view()
    {
        // Get current view
        $currentView = $this->getCurrentView();

        // Check if the view does not exist, throw exception
        if (!isset($this->views[$currentView])) {
            throw new Exception(esc_html__('View is not valid.', 'wp-statistics'));
        }

        // Check if the class does not have view method, throw exception
        if (!method_exists($this->views[$currentView], 'view')) {
            throw new Exception(sprintf(esc_html__('View method is not defined within %s class.', 'wp-statistics'), $currentView));
        }

        // Instantiate the view class and render content
        $class = new $this->views[$currentView];
        $class->view();
    }
}
