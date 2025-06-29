<?php

use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;

?>
<div class="metabox-holder wps-referral-overview">
    <div class="postbox-container" id="wps-postbox-container-1">
        <div class="wps-card">
            <div class="wps-card__title">
                <h2><?php esc_html_e('Top Referrers', 'wp-statistics') ?></h2>
            </div>
            <?php View::load("components/tables/top-referrers", ['data' => $data['referrers']]); ?>
            <div class="wps-card__footer">
                <div class="wps-card__footer__more">
                    <a class="wps-card__footer__more__link" href="<?php echo Menus::admin_url('referrals', ['tab' => 'referrers']) ?>">
                        <?php esc_html_e('View Referrers', 'wp-statistics') ?>
                    </a>
                </div>
            </div>
        </div>

        <?php
            View::load("components/charts/horizontal-bar", [
                'title'     => esc_html__('Top Countries', 'wp-statistics'),
                'unique_id' => 'referral-top-countries'
            ]);

            View::load("components/charts/horizontal-bar", [
                'title'     => esc_html__('Top Browsers', 'wp-statistics'),
                'unique_id' => 'referral-top-browser'
            ]);

            View::load("components/charts/horizontal-bar", [
                'title'     => esc_html__('Device Type', 'wp-statistics'),
                'unique_id' => 'referral-device-type'
            ]);
        ?>
    </div>
    <div class="postbox-container" id="wps-postbox-container-2">
        <?php
            View::load("components/charts/top-referrer", [
                'title'        => esc_html__('Referred Visitors', 'wp-statistics'),
                'unique_id'    => 'referralVisitorChart',
            ]);
        ?>

        <?php do_action('wp_statistics_referrals_overview_source_categories_widget'); ?>

        <?php
            View::load("components/charts/top-referrer", [
                'title'        => esc_html__('Top Search Engines', 'wp-statistics'),
                'unique_id'    => 'referral-search-engines-chart',
                'footer_title' => esc_html__('View Search Engines', 'wp-statistics'),
                'footer_link'  => Menus::admin_url('referrals', ['tab' => 'search-engines'])
            ]);

            View::load("components/charts/top-referrer", [
                'title'        => esc_html__('Top Social Media', 'wp-statistics'),
                'unique_id'    => 'referral-social-media-chart',
                'footer_title' => esc_html__('View Social Media', 'wp-statistics'),
                'footer_link'  => Menus::admin_url('referrals', ['tab' => 'social-media'])
            ]);
        ?>

        <?php do_action('wp_statistics_referrals_overview_entry_pages_widget'); ?>

        <div class="wps-card">
            <div class="wps-card__title">
                <h2><?php esc_html_e('Latest Referrals', 'wp-statistics') ?></h2>
            </div>
            <?php
                View::load("components/tables/latest-referrals", ['visitors' => $data['visitors']]);
            ?>
            <div class="wps-card__footer">
                <div class="wps-card__footer__more">
                    <a class="wps-card__footer__more__link" href="<?php echo Menus::admin_url('referrals', ['tab' => 'referred-visitors']) ?>">
                        <?php esc_html_e('View Referred Visitors', 'wp-statistics') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>