<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Option;
use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;

/** @var VisitorDecorator $visitor */
$visitor = $data['visitor'];
?>

<div class="postbox-container-holder postbox-container--two-col postbox-container--visitor wps-visitor-single">
    <div class="postbox-container postbox-container--first-col">
        <?php
        View::load('components/visitor-profile', [
            'visitor' => $visitor,
            'summary' => $data['summary'] ?? [],
            'limit'   => $data['sessions_limit'] ?? 0,
        ]);
        ?>

        <?php if (!empty($visitor->isLoggedInUser()) && Option::get('visitors_log')) : ?>
            <div class="wps-card">
                <div class="wps-card__title">
                    <h2><?php esc_html_e('Account Information', 'wp-statistics'); ?></h2>
                </div>
                <?php View::load("components/account-information", ['user' => $visitor->getUser()]); ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="postbox-container postbox-container--second-col">
        <?php
        View::load('components/visitor-journey', [
            'visits'  => $data['visits'] ?? [],
            'visitor' => $visitor,
        ]);
        ?>
    </div>
</div>
