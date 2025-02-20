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
                <div class="wps-notification-sidebar__cards wps-notification-sidebar__cards--active">
                    <?php
                    if (!empty($notifications)) :
                        foreach ($notifications as $notification) :
                            if ($notification->getDismiss()) continue;
                            View::load("components/notification/card", ['notification' => $notification]);
                        endforeach;
                    else :
                        ?>
                        <div class="wps-notification-sidebar__card">
                            <div class="wps-notification-sidebar__card-body">
                                <div class="wps-notification-sidebar__card-content"><?php esc_html_e('No notifications.', 'wp-statistics'); ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="wps-notification-sidebar__tab-pane" id="tab-2">
                <div class="wps-notification-sidebar__cards wps-notification-sidebar__cards--dismissed">
                    <?php
                    if (!empty($notifications)) :
                        foreach ($notifications as $notification) :
                            if (!$notification->getDismiss()) continue;
                            View::load("components/notification/card", ['notification' => $notification]);
                        endforeach;
                    else :
                        ?>
                        <div class="wps-notification-sidebar__card">
                            <div class="wps-notification-sidebar__card-body">
                                <div class="wps-notification-sidebar__card-content"><?php esc_html_e('No dismissed notifications.', 'wp-statistics'); ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="wps-notification-sidebar__overlay"></div>
</div>