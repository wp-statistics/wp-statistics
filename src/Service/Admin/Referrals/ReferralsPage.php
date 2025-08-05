<?php

namespace WP_Statistics\Service\Admin\Referrals;

use WP_Statistics\Abstracts\MultiViewPage;
use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\BackgroundProcessFactory;
use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs\SourceChannelUpdater;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Utils\Request;
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

    protected function setCampaignsFilter()
    {
        $pageId = Request::get('pid', '', 'number');

        $this->filters = FilterGenerator::create()
            ->hidden('pageName', [
                'name'       => 'page',
                'attributes' => [
                    'value' => Menus::get_page_slug('referrals')
                ]
            ])
            ->input('text', 'utm_source', [
                'label' => esc_html__('UTM Source', 'wp-statistics'),
            ])
            ->input('text', 'utm_medium', [
                'label' => esc_html__('UTM Medium', 'wp-statistics'),
            ])
            ->input('text', 'utm_campaign', [
                'label' => esc_html__('UTM Campaign', 'wp-statistics'),
            ])
            ->select('pid', [
                'label'         => esc_html__('Entry Page', 'wp-statistics'),
                'classes'       => 'wps-width-100 wps-select2',
                'placeholder'   => $pageId ? get_the_title($pageId) : esc_html__('All', 'wp-statistics'),
                'attributes'    => [
                    'data-source'       => 'getPageId',
                    'data-searchable'   => true,
                    'data-default'      => $pageId
                ],
            ]);

            if (!Request::compare('type', 'single-campaign')) {
                $this->filters
                    ->select('referrer', [
                        'name'          => 'referrer',
                        'classes'       => 'wps-width-100 wps-select2',
                        'attributes'    => [
                            'data-type'       => 'getReferrer',
                            'data-searchable' => true,
                        ],
                    ]);
            }

            if (Request::compare('tab', 'campaigns')) {
                $this->filters
                    ->hidden('tab', [
                        'name'       => 'tab',
                        'attributes' => [
                            'value' => 'campaigns'
                        ]
                    ]);
            }

            if (Request::compare('type', 'single-campaign')) {
                $this->filters
                    ->hidden('type', [
                        'name'       => 'type',
                        'attributes' => [
                            'value' => 'single-campaign'
                        ]
                    ]);
            }

            $this->filters = $this->filters
                ->button('submitButton', [
                    'name'      => 'filter',
                    'type'      => 'button',
                    'classes'   => 'button-primary',
                    'label'     => esc_html__('Filter', 'wp-statistics'),
                    'attributes'  => [
                        'type' => 'submit',
                    ],
                ])
                ->get();
    }

    protected function setFilters() {
        // Campaigns tab filter
        if (Request::compare('tab', 'campaigns') || Request::compare('type', 'single-campaign')) {
            return $this->setCampaignsFilter();
        }

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
            ->hidden('tabName', [
                'name'  => 'tab',
                'attributes' => [
                    'value' => Request::get('tab')
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
                    'data-type' => 'source-channels'
                ],
                'predefined' => $sourceChannelsData
            ])
            ->dropdown('utm_params', [
                'label'         => esc_html__('Campaigns', 'wp-statistics'),
                'panel'         => true,
                'attributes'    => [
                    'data-type'     => 'utm_params',
                    'data-default'  => '',
                ],
                'predefined' => $this->getUtmParamsFilter()
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
     * Retrieves UTM filter items.
     *
     * @return array
     */
    public function getUtmParamsFilter()
    {
        $queryKey   = 'utm_param';
        $baseUrl    = htmlspecialchars_decode(esc_url(remove_query_arg([$queryKey])));

        $args = [
            [
                'slug'  => 'utm_campaign',
                'name'  => esc_html__('UTM Campaign', 'wp-statistics'),
                'url'   => add_query_arg([$queryKey => 'utm_campaign'], $baseUrl),
            ],
            [
                'slug'  => 'utm_source',
                'name'  => esc_html__('UTM Source', 'wp-statistics'),
                'url'   => add_query_arg([$queryKey => 'utm_source'], $baseUrl),
            ],
            [
                'slug'  => 'utm_medium',
                'name'  => esc_html__('UTM Medium', 'wp-statistics'),
                'url'   => add_query_arg([$queryKey => 'utm_medium'], $baseUrl),
            ]
        ];

        return [
            'args'              => $args,
            'baseUrl'           => $baseUrl,
            'selectedOption'    => Request::get($queryKey, 'utm_campaign'),
        ];
    }

    /**
     * Retrieves filtered search channels and generates corresponding data.
     *
     * @return array
     */
    private function getSearchChannels()
    {
        $channels = Helper::filterArrayByKeys(SourceChannels::getList(), ['search', 'paid_search']);
        $baseUrl  = htmlspecialchars_decode(esc_url(remove_query_arg(['source_channel', 'pid'])));

        foreach ($channels as $key => $channel) {
            $args[] = [
                'slug'  => esc_attr($key),
                'name'  => esc_html($channel),
                'url'   => add_query_arg(['source_channel' => $key]),
            ];
        }

        return [
            'args'           => $args,
            'baseUrl'        => $baseUrl,
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
        $channels = Helper::filterArrayByKeys(SourceChannels::getList(), ['social', 'paid_social']);
        $baseUrl  = htmlspecialchars_decode(esc_url(remove_query_arg(['source_channel', 'pid'])));

        foreach ($channels as $key => $channel) {
            $args[] = [
                'slug'  => esc_attr($key),
                'name'  => esc_html($channel),
                'url'   => add_query_arg(['source_channel' => $key]),
            ];
        }

        return [
            'args'           => $args,
            'baseUrl'        => $baseUrl,
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
        $channels = SourceChannels::getList();
        unset($channels['direct']);

        $baseUrl = htmlspecialchars_decode(esc_url(remove_query_arg(['source_channel', 'pid'])));

        foreach ($channels as $key => $channel) {
            $args[] = [
                'slug'  => esc_attr($key),
                'name'  => esc_html($channel),
                'url'   => add_query_arg(['source_channel' => $key]),
            ];
        }

        return [
            'args'           => $args,
            'baseUrl'        => $baseUrl,
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
