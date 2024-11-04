<?php

use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;

/** @var VisitorDecorator $visitor */
$visitor = $data['visitor'];
?>

<div class="postbox-container-holder postbox-container--two-col postbox-container--visitor">
    <div class="postbox-container postbox-container--first-col">
        <div class="wps-card">
            <div class="wps-card__title">
                <h2><?php esc_html_e('Visitor Details', 'wp-statistics'); ?></h2>
            </div>
            <?php View::load("components/session-details", ['visitor' => $visitor]); ?>
        </div>

        <?php if (!empty($visitor->isLoggedInUser())) : ?>
            <div class="wps-card">
                <div class="wps-card__title">
                    <h2><?php esc_html_e('Account Information', 'wp-statistics'); ?></h2>
                </div>
                <?php View::load("components/account-information", ['user' => $visitor->getUser()]); ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="postbox-container postbox-container--second-col" >
        <div class="wps-card wps-card wps-card--table">
            <div class="wps-card__title">
                <h2><?php esc_html_e('Recent Views', 'wp-statistics'); ?></h2>
            </div>
            <div class="wps-card--table__body">
                <?php
                $args = [
                    'data'       => $data['visitor_journey'],
                    'pagination' => $pagination ?? null
                ];
                View::load("components/tables/recent-views", $args);
                ?>
            </div>

        </div>
        <!-- <div class="wps-card wps-card--table">
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
        </div> -->
    </div>
</div>