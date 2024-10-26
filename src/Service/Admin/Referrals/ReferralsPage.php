<?php

namespace WP_Statistics\Service\Admin\Referrals;

use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Utils\Request;
use WP_Statistics\Abstracts\MultiViewPage;
use WP_Statistics\Async\BackgroundProcessFactory;
use WP_Statistics\Async\SourceChannelUpdater;
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
        /** @var SourceChannelUpdater $backgroundProcess */
        $backgroundProcess = WP_Statistics()->getBackgroundProcess('update_visitors_source_channel');

        // Show migration notice if the process is not already initiated
        if (!$backgroundProcess->is_initiated()) {
            $actionUrl = add_query_arg(
                [
                    'action' => 'update_visitor_source_channel',
                    'nonce'  => wp_create_nonce('update_visitor_source_channel_nonce')
                ],
                Menus::admin_url('referrals')
            );

            $message = sprintf(
                __('We’ve updated the referral structure in this version. To ensure accurate reports, please initiate the background data process <a href="%s">by clicking here</a>.', 'wp-statistics'),
                esc_url($actionUrl)
            );

            Notice::addNotice($message, 'update_visitors_source_channel_notice', 'info', false);
        }

        // Show notice if already running
        if ($backgroundProcess->is_active()) {
            $message = __('The referrals process is running in the background and may take a while depending on your data size. <br> <i>Note: The accuracy of the results may be affected as we only retain whitelisted query parameters.</i>', 'wp-statistics');
            Notice::addNotice($message, 'running_visitors_source_channel_notice', 'info', true);
        }
    }

    private function processSourceChannelBackgroundAction()
    {
        // Check the action and nonce
        if (!Request::compare('action', 'update_visitor_source_channel')) {
            return;
        }

        check_admin_referer('update_visitor_source_channel_nonce', 'nonce');

        // Check if already processed
        if (Option::getOptionGroup('jobs', 'update_source_channel_process_running')) {
            wp_redirect(Menus::admin_url('referrals'));
            exit;
        }

        BackgroundProcessFactory::batchUpdateSourceChannelForVisitors();

        wp_redirect(Menus::admin_url('referrals'));
        exit;
    }
}
