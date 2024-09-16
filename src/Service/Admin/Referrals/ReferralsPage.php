<?php

namespace WP_Statistics\Service\Admin\Referrals;

use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Utils\Request;
use WP_Statistics\Abstracts\MultiViewPage;
use WP_Statistics\Async\BackgroundProcessFactory;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\Referrals\Views\TabsView;

class ReferralsPage extends MultiViewPage
{
    protected $pageSlug = 'referrals';
    protected $defaultView = 'tabs';
    protected $views = [
        'tabs' => TabsView::class
    ];

    public function __construct()
    {
        parent::__construct();
    }

    protected function init()
    {
        $this->disableScreenOption();
        $this->incompleteSourceChannelsNotice();
        $this->processSourceChannelBackgroundAction();
    }

    /**
     * Check for visitors with incomplete source channel data
     *
     * @return void
     */
    private function incompleteSourceChannelsNotice()
    {
        $actionUrl = add_query_arg(
            [
                'action' => 'update_visitor_source_channel',
                'nonce'  => wp_create_nonce('update_visitor_source_channel_nonce')
            ],
            Menus::admin_url('referrals')
        );

        $message = sprintf(
            __('Please <a href="%s">click here</a> to update the source channel data in the background. This is necessary for accurate analytics.', 'wp-statistics'),
            esc_url($actionUrl)
        );

        Notice::addNotice($message, 'update_visitors_source_channel');
    }

    private function processSourceChannelBackgroundAction()
    {
        // Check the action and nonce
        if (!Request::compare('action', 'update_visitor_source_channel')) {
            return;
        }

        check_admin_referer('update_visitor_source_channel_nonce', 'nonce');

        // Check if already processed
        if (Option::getOptionGroup('jobs', 'update_source_channel_process_started')) {
            Notice::addFlashNotice(__('Source channel update is already in progress.', 'wp-statistics'));

            wp_redirect(Menus::admin_url('referrals'));
            exit;
        }

        BackgroundProcessFactory::batchUpdateSourceChannelForVisitors();

        wp_redirect(Menus::admin_url('referrals'));
        exit;
    }
}
