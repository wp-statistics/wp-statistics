<?php

namespace WP_Statistics\Service\Admin\ContentAnalytics;

use WP_Statistics\Async\BackgroundProcessFactory;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Utils\Request;
use WP_Statistics\Abstracts\MultiViewPage;
use WP_Statistics\Service\Admin\Posts\WordCountService;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\ContentAnalytics\Views\TabsView;
use WP_Statistics\Service\Admin\ContentAnalytics\Views\SingleView;

class ContentAnalyticsPage extends MultiViewPage
{

    protected $pageSlug = 'content-analytics';
    protected $defaultView = 'tabs';
    protected $views = [
        'tabs'      => TabsView::class,
        'single'    => SingleView::class
    ];
    private $wordsCount;

    public function __construct()
    {

        parent::__construct();
    }

    protected function init()
    {
        $this->wordsCount = new WordCountService();

        $this->disableScreenOption();
        $this->processWordCountMeta();
        $this->processWordCountInBackground();
    }

    private function processWordCountMeta()
    {
        if (count($this->wordsCount->getPostsWithoutWordCountMeta()) && !Option::getOptionGroup('jobs', 'word_count_process_started')) {
            $actionUrl = add_query_arg(
                [
                    'action' => 'process_word_count',
                    'nonce'  => wp_create_nonce('process_word_count_nonce')
                ],
                Menus::admin_url($this->pageSlug)
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

            wp_redirect(Menus::admin_url($this->pageSlug));
            exit;
        }

        // Initialize and dispatch the CalculatePostWordsCount class
        BackgroundProcessFactory::processWordCountForPosts();

        wp_redirect(Menus::admin_url($this->pageSlug));
        exit;
    }
}
