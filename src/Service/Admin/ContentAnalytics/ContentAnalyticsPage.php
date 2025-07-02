<?php

namespace WP_Statistics\Service\Admin\ContentAnalytics;

use WP_Statistics\Abstracts\MultiViewPage;
use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\BackgroundProcessFactory;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_Statistics\Async\SourceChannelUpdater;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\Posts\WordCountService;
use WP_Statistics\Service\Admin\FilterHandler\FilterGenerator;
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

        $this->setFilters();
    }

    protected function setFilters() {
        $this->filters = FilterGenerator::create()
            ->dropdown('qp', [
                'label' => esc_html__('Query Parameter', 'wp-statistics'),
                'panel' => true,
                'searchable' => true,
                'attributes'  => [
                    'data-type' => 'query-params',
                    'data-source' => 'getQueryParameters',
                ],
            ])
            ->get();

        return $this->filters;
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
        /** @var SourceChannelUpdater $backgroundProcess */
        $backgroundProcess = WP_Statistics()->getBackgroundProcess('calculate_post_words_count');

        if (!$backgroundProcess->is_initiated()) {
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

        /** @var SourceChannelUpdater $backgroundProcess */
        $backgroundProcess = WP_Statistics()->getBackgroundProcess('calculate_post_words_count');

        if ($backgroundProcess->is_active()) {
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
