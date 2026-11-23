<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;

?>

<div class="metabox-holder wps-referral-overview wps-google-search">
    <div class="postbox-container" id="wps-postbox-container-1">

        <?php
            $metrics = [
                // "Referred Visitors" moved to the Referred Traffic card below (with its trend per period)
                [
                    'label'      => esc_html__('Top Referrer', 'wp-statistics'),
                    'link-href'  => !empty($data['summary']['referrer']) ? esc_url($data['summary']['referrer']) : null,
                    'link-title' => $data['summary']['referrer'] ?? null
                ],
                [
                    'label' => esc_html__('Top Country', 'wp-statistics'),
                    'value' => $data['summary']['country'] ?? null
                ],
                [
                    'label' => esc_html__('Top Browser', 'wp-statistics'),
                    'value' => $data['summary']['browser'] ?? null
                ],
                [
                    'label' => esc_html__('Top Search Engine', 'wp-statistics'),
                    'value' => $data['summary']['search_engine'] ?? null
                ],
                [
                    'label' => esc_html__('Top Social Media', 'wp-statistics'),
                    'value' => $data['summary']['social_media'] ?? null
                ]
            ];

            $metrics = apply_filters('wp_statistics_referrals_overview_glance_metrics', $metrics);

            View::load("components/objects/glance-card", ['metrics' => $metrics]);
        ?>

        <?php if (!empty($data['traffic_summary'])) : ?>
            <?php View::load('components/traffic-summary', ['data' => ['summary' => $data['traffic_summary']], 'title' => __('Referred Traffic', 'wp-statistics'), 'tooltip' => __('Visitors who came from another site or a campaign (not Direct), and their views.', 'wp-statistics')]); ?>
        <?php endif; ?>
        <div class="wps-card">
            <?php View::load('components/objects/card-header', ['title' => __('Top Referrers', 'wp-statistics'), 'context' => 'card']); ?>
            <?php View::load("components/tables/top-referrers", ['data' => $data['referrers']]); ?>
            <?php View::load('components/objects/card-footer', ['href' => Menus::admin_url('referrals', ['tab' => 'referrers']), 'title' => __('View Referrers', 'wp-statistics')]); ?>
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
            'title'     => esc_html__('Referred Visitors', 'wp-statistics'),
            'unique_id' => 'referralVisitorChart',
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

            do_action('wp_statistics_referrals_overview_gsc_widgets');

            View::load("components/charts/top-referrer", [
                'title'        => esc_html__('Top Social Media', 'wp-statistics'),
                'unique_id'    => 'referral-social-media-chart',
                'footer_title' => esc_html__('View Social Media', 'wp-statistics'),
                'footer_link'  => Menus::admin_url('referrals', ['tab' => 'social-media'])
            ]);
        ?>

        <?php do_action('wp_statistics_referrals_overview_entry_pages_widget'); ?>

        <div class="wps-card">
            <?php View::load('components/objects/card-header', ['title' => __('Latest Referrals', 'wp-statistics'), 'context' => 'card']); ?>
            <?php
            View::load("components/tables/latest-referrals", ['visitors' => $data['visitors']]);
            ?>
            <?php View::load('components/objects/card-footer', ['href' => Menus::admin_url('referrals', ['tab' => 'referred-visitors']), 'title' => __('View Referred Visitors', 'wp-statistics')]); ?>
        </div>
    </div>
</div>