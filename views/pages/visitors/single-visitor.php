<?php

use WP_Statistics\Components\View;

?>
<div class="postbox-container-holder postbox-container--two-col postbox-container--visitor">
    <div class="postbox-container postbox-container--first-col">
        <div class="wps-card">
            <div class="wps-card__title">
                <h2>
                    <?php esc_html_e('Session Details', 'wp-statistics'); ?>
                    <span class="wps-tooltip" title="<?php esc_html_e('Session Details tooltip', 'wp-statistics'); ?>"><i class="wps-tooltip-icon info"></i></span>
                </h2>
            </div>
            <?php
            $session_args = [
                'data'       => ['test', 'test'],
            ];
            View::load("components/session-details", $session_args);
            ?>
        </div>
        <div class="wps-card">
            <div class="wps-card__title">
                <h2>
                    <?php esc_html_e('Account Information', 'wp-statistics'); ?>
                    <span class="wps-tooltip" title="<?php esc_html_e('Account Information tooltip', 'wp-statistics'); ?>"><i class="wps-tooltip-icon info"></i></span>
                </h2>
            </div>
            <?php
            $account_info = [
                'data'       => ['test', 'test'],
            ];
            View::load("components/account-information", $account_info);
            ?>
        </div>
    </div>
    <div class="postbox-container postbox-container--second-col" >
        <div class="wps-card wps-card wps-card--table">
            <div class="wps-card__title">
                <h2>
                    <?php esc_html_e('Recent Views', 'wp-statistics'); ?>
                    <span class="wps-tooltip" title="<?php esc_html_e('Recent Views tooltip', 'wp-statistics'); ?>"><i class="wps-tooltip-icon info"></i></span>
                </h2>
            </div>
            <div class="wps-card--table__body">
                <?php
                $views_args = [
                    'data'       => ['test', 'test'],
                    'pagination' => isset($pagination) ? $pagination : null
                ];
                View::load("components/tables/recent-views", $views_args);
                ?>
            </div>

        </div>
        <div class="wps-card wps-card--table">
            <div class="wps-card__title">
                <h2>
                    <?php esc_html_e('Recent Events', 'wp-statistics'); ?>
                    <span class="wps-tooltip" title="<?php esc_html_e('Recent Events tooltip', 'wp-statistics'); ?>"><i class="wps-tooltip-icon info"></i></span>
                </h2>
            </div>
            <div class="wps-card--table__body">
                <?php
                $events_args = [
                    'data'       => ['test', 'test'],
                    'pagination' => isset($pagination) ? $pagination : null
                ];
                View::load("components/tables/recent-events", $events_args);
                ?>
            </div>
        </div>
    </div>
</div>