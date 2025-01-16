<?php

use WP_Statistics\Components\View;

?>
<div class="wps-notification-sidebar">
    <div class="wps-notification-sidebar__menu">
        <div class="wps-notification-sidebar__header">
            <div>
                <h2 class="wps-notification-sidebar__title"><?php esc_html_e('Notifications', 'wp-statistics'); ?></h2>
                <span class="wps-notification-sidebar__close"></span>
            </div>
            <div>
                <ul class="wps-notification-sidebar__tabs">
                    <li class="wps-notification-sidebar__tab wps-notification-sidebar__tab--active"
                        data-tab="tab-1"><?php esc_html_e('Inbox', 'wp-statistics'); ?></li>
                    <li class="wps-notification-sidebar__tab"
                        data-tab="tab-2"><?php esc_html_e('Dismissed', 'wp-statistics'); ?></li>
                </ul>
                <a href="#"
                   class="wps-notification-sidebar__dismiss-all"><?php esc_html_e('Dismiss all', 'wp-statistics'); ?></a>
            </div>
        </div>
        <div class="wps-notification-sidebar__content">
            <div class="wps-notification-sidebar__tab-pane wps-notification-sidebar__tab-pane--active" id="tab-1">
                <div class="wps-notification-sidebar__cards">

                    <?php
                    $data1 = [
                        'title' => __('🎄 Christmas Sale!', 'wp-statistics'),
                        'icon' => WP_STATISTICS_URL . '/assets/images/notifications/sale.png',
                        'date' => __('2 day ago', 'wp-statistics'),
                        'content' => sprintf('<p>%1$s</p><p>%2$s</p>',
                            esc_html__('Get into the festive spirit with our Christmas Sale! Enjoy 20% OFF on all purchases
                                        for a limited time.', 'wp-statistics'),
                            esc_html__('Don’t miss out—grab your holiday deal now!', 'wp-statistics')),
                        'actions' => [
                            [
                                'href' => '',
                                'title' => __('Upgrade Now', 'wp-statistics'),
                                'class' => 'wps-notification-sidebar__button',
                            ]
                        ]
                    ];
                    View::load("components/notification/card", $data1);

                    $data2 = [
                        'title' => __('Take Action to Enhance Privacy Compliance', 'wp-statistics'),
                        'icon' => WP_STATISTICS_URL . '/assets/images/notifications/alert.png',
                        'date' => __('3 day ago', 'wp-statistics'),
                        'content' => sprintf('<p>%1$s</p>',
                            esc_html__('Your Privacy Audit indicates some
                                settings need adjustments to meet privacy standards and protect user data. Address these
                                issues now to ensure full compliance and build user trust.', 'wp-statistics')),
                        'actions' => [
                            [
                                'href' => '',
                                'title' => __('Review and Resolve Privacy Issues', 'wp-statistics'),
                                'class' => 'wps-notification-sidebar__button',
                            ]
                        ]
                    ];
                    View::load("components/notification/card", $data2);

                    $data3 = [
                        'title' => __('WP Statistics 14.11: Major Upgrades!', 'wp-statistics'),
                        'icon' => WP_STATISTICS_URL . '/assets/images/notifications/rocket.png',
                        'date' => __('3 day ago', 'wp-statistics'),
                        'content' => sprintf('<p>%1$s</p><ul>
                                    <li>💎 <b>%2$s</b> %3$s</li>
                            </ul>',
                            esc_html__('The latest update brings powerful enhancements to WP Statistics:', 'wp-statistics'),
                            esc_html__('WP Statistics Premium:', 'wp-statistics'),
                            esc_html__('All premium features in one package! Existing bundle users upgraded automatically.', 'wp-statistics')
                        ),
                        'actions' => [
                            [
                                'href' => '',
                                'title' => __('Read More', 'wp-statistics'),
                                'class' => 'wps-notification-sidebar__button',
                            ],
                            [
                                'href' => '',
                                'title' => __('Upgrade to Premium Now', 'wp-statistics'),
                                'class' => 'wps-notification-sidebar__button',
                            ]
                        ]
                    ];
                    View::load("components/notification/card", $data3);

                    ?>
                </div>
            </div>
            <div class="wps-notification-sidebar__tab-pane" id="tab-2">
                <div class="wps-notification-sidebar__cards">
                    <div class="wps-notification-sidebar__card">
                        <div class="wps-notification-sidebar__card-body">
                            <div class="wps-notification-sidebar__card-content"><?php esc_html_e('No dismissed notifications.', 'wp-statistics'); ?></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="wps-notification-sidebar__overlay"></div>
</div>