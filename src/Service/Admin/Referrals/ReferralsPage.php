<?php

namespace WP_Statistics\Service\Admin\Referrals;

use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Utils\Request;
use WP_Statistics\Abstracts\MultiViewPage;
use WP_Statistics\Async\BackgroundProcessFactory;
use WP_Statistics\Async\SourceChannelUpdater;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Admin\FilterHandler\FilterGenerator;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\Referrals\Views\TabsView;
use WP_Statistics\Service\Analytics\Referrals\SourceChannels;

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

        $this->setFilters();
    }

    protected function init()
    {
        $this->disableScreenOption();
        $this->incompleteSourceChannelsNotice();
        $this->processSourceChannelBackgroundAction();
    }

    protected function setFilters() {
        $searchChannelsData = $this->getSearchChannels();
        $socialChannelsData = $this->getSocialChannels();
        $sourceChannelsData = $this->getSourceChannels();

        $referrer            = Request::get('referrer', '');
        $referrerPlaceholder = ! empty($referrer) ? $referrer : esc_html__('All', 'wp-statistics');

        $this->filters = FilterGenerator::create()
            ->hidden('pageName', [
                'name' => 'page',
                'attributes' => [
                    'value' => Menus::get_page_slug('referrals')
                ]
            ])
            ->select('referrers', [
                'name' => 'referrer',
                'placeholder' => $referrerPlaceholder,
                'classes' => 'wps-width-100 wps-select2',
                'attributes'  => [
                    'data-type'       => 'referrers',
                    'data-source'     => 'getReferrer',
                    'data-searchable' => true,
                    'data-default'    => $referrer,
                ],
            ])
            ->dropdown('search_channel', [
                'name' => 'source_channel',
                'label' => esc_html__('Source Category', 'wp-statistics'),
                'panel' => true,
                'searchable' => true,
                'attributes'  => [
                    'data-type' => 'search-channels',
                ],
                'predefined' => $searchChannelsData
            ])
            ->dropdown('social_channel', [
                'name' => 'source_channel',
                'label' => esc_html__('Source Category', 'wp-statistics'),
                'panel' => true,
                'searchable' => true,
                'attributes'  => [
                    'data-type' => 'social-channels',
                ],
                'predefined' => $socialChannelsData
            ])
            ->dropdown('source_channel', [
                'label' => esc_html__('Source Category', 'wp-statistics'),
                'panel' => true,
                'searchable' => true,
                'attributes'  => [
                    'data-type' => 'source-channels',
                ],
                'predefined' => $sourceChannelsData
            ])
            ->button('resetButton', [
                'name' => 'reset',
                'type' => 'button',
                'classes' => 'wps-reset-filter wps-modal-reset-filter',
                'label' => esc_html__('Reset', 'wp-statistics'),
            ])
            ->button('submitButton', [
                'name' => 'filter',
                'type' => 'button',
                'classes' => 'button-primary',
                'label' => esc_html__('Filter', 'wp-statistics'),
                'attributes'  => [
                    'type' => 'submit',
                ],
            ])
            ->get();
        
        return $this->filters;
    }

    /**
     * Retrieves filtered search channels and generates corresponding data.
     * 
     * @return array
     */
    private function getSearchChannels() 
    {
        $currentPage = esc_url_raw(home_url($_SERVER['REQUEST_URI']));

        $channels = Helper::filterArrayByKeys(SourceChannels::getList(), ['search', 'paid_search']);
        $baseUrl  = htmlspecialchars_decode(esc_url(remove_query_arg(['source_channel', 'pid'], $currentPage)));
        
        foreach ($channels as $key => $channel) {
            $args[] = [
                'slug' => esc_attr($key),
                'name' => esc_html($channel),
                'url' => add_query_arg(['source_channel' => $key], $baseUrl),
            ];
        }

        return [
            'args' => $args,
            'baseUrl' => $baseUrl,
            'selectedOption' => Request::get('source_channel'),
        ];
    }

    /**
     * Retrieves filtered social channels and generates corresponding data.
     * 
     * @return array
     */
    private function getSocialChannels()
    {
        $currentPage = esc_url_raw(home_url($_SERVER['REQUEST_URI']));

        $channels = Helper::filterArrayByKeys(SourceChannels::getList(), ['social', 'paid_social']);
        $baseUrl  = htmlspecialchars_decode(esc_url(remove_query_arg(['source_channel', 'pid'], $currentPage)));

        foreach ($channels as $key => $channel) {
            $args[] = [
                'slug' => esc_attr($key),
                'name' => esc_html($channel),
                'url' => add_query_arg(['source_channel' => $key], $baseUrl),
            ];
        }

        return [
            'args' => $args,
            'baseUrl' => $baseUrl,
            'selectedOption' => Request::get('source_channel'),
        ];
    }

     /**
     * Retrieves filtered source channels and generates corresponding data.
     * 
     * @return array
     */
    private function getSourceChannels()
    {
        $currentPage = esc_url_raw(home_url($_SERVER['REQUEST_URI']));

        $channels = SourceChannels::getList();
        unset($channels['direct']);

        $baseUrl = htmlspecialchars_decode(esc_url(remove_query_arg(['source_channel', 'pid'], $currentPage)));

        foreach ($channels as $key => $channel) {
            $args[] = [
                'slug' => esc_attr($key),
                'name' => esc_html($channel),
                'url' => add_query_arg(['source_channel' => $key], $baseUrl),
            ];
        }

        return [
            'args' => $args,
            'baseUrl' => $baseUrl,
            'selectedOption' => Request::get('source_channel')
        ];
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
