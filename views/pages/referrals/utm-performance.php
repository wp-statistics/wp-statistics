<?php
use WP_Statistics\Components\View;

$isLocked = apply_filters('wp_statistics_referrals_utm-performance_locked', true);

if ($isLocked) {
    $args = [
        'page_title'        => esc_html__('Marketing Add-on: Maximize Campaign Impact and Conversions', 'wp-statistics'),
        'page_second_title' => esc_html__('WP Statistics Premium: Unlock More Than Just Marketing Insights', 'wp-statistics'),
        'addon_name'        => esc_html__('Marketing', 'wp-statistics'),
        'addon_slug'        => 'wp-statistics-marketing',
        'campaign'          => 'marketing',
        'more_title'        => esc_html__('Learn More About the Marketing Add-on', 'wp-statistics'),
        'premium_btn_title' => esc_html__('Upgrade Now to Unlock All Premium Features!', 'wp-statistics'),
        'images'            => ['marketing-lock.png','campaign-lock.png','goals-lock.png'],
        'description'       => esc_html__('The Marketing add-on is a premium feature for WP Statistics that brings powerful marketing insights directly to your dashboard. Track your campaigns, monitor search traffic, manage UTM links, and set conversion goals—all in one place. With detailed reports and Google Search Console integration, you can make data-driven decisions to boost traffic and increase conversions.', 'wp-statistics'),
        'second_description'=> esc_html__('When you upgrade to WP Statistics Premium, you don’t just get access to the Marketing add-on—you unlock all premium add-ons, delivering advanced analytics tools and detailed reports for every aspect of your site.', 'wp-statistics')
    ];
    View::load("pages/lock-page", $args);
}