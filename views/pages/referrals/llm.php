<?php

use WP_Statistics\Components\View;

$isLocked = apply_filters('wp_statistics_referrals_llm_locked', true);

if ($isLocked) {
    $args = [
        'page_title'         => esc_html__('AI Insights Add-on', 'wp-statistics'),
        'page_second_title'  => esc_html__('WP Statistics Premium: Unlock More Than Just AI Insights', 'wp-statistics'),
        'addon_name'         => esc_html__('AI Insights', 'wp-statistics'),
        'addon_slug'         => 'wp-statistics-ai-insights',
        'campaign'           => 'ai-insights',
        'more_title'         => esc_html__('Learn More About the AI Insights Add-on', 'wp-statistics'),
        'premium_btn_title'  => esc_html__('Upgrade Now to Unlock All Premium Features', 'wp-statistics'),
        'images'             => ['ai-lock.png'],
        'description'        => __('The <b>AI Insights Add-on</b> is a premium feature for WP Statistics that brings data-driven insights directly into your dashboard. It helps you understand your visitors, discover growth opportunities, and make smarter decisions.', 'wp-statistics'),
        'second_description' => __('When you upgrade to <b>WP Statistics Premium</b>, you get access to the AI Insights add - on and all other premium add - ons . WP Statistics Premium delivers advanced analytics, detailed reports, and powerful insights for every part of your site.', 'wp - statistics'),
     ];
    View::load("pages/lock-page", $args);
}