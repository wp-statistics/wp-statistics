<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// A short tour of what Premium adds, for sites with no license. The pictures ship inside the plugin.
$buyUrl    = 'https://wp-statistics.com/pricing/?utm_source=wp-statistics&utm_medium=link&utm_campaign=addons-showcase';
$addonUrl  = function ($slug) {
    return WP_STATISTICS_SITE_URL . '/add-ons/' . $slug . '/?utm_source=wp-statistics&utm_medium=link&utm_campaign=addons-showcase';
};
$freeFeatures = [
    __('Visitor, page and referrer statistics', 'wp-statistics'),
    __('Country, browser and device reports', 'wp-statistics'),
    __('Scheduled email reports', 'wp-statistics'),
    __('Privacy tools and GDPR readiness', 'wp-statistics'),
];
$features  = [
    ['emoji' => '🎯', 'title' => __('Campaign reports and UTM analytics', 'wp-statistics'), 'tip' => __('Marketing add-on. See which campaign links bring visitors, and build and check UTM links.', 'wp-statistics'), 'slug' => 'wp-statistics-marketing'],
    ['emoji' => '🏁', 'title' => __('Goals and conversions', 'wp-statistics'), 'tip' => __('Marketing add-on. Set goals and see how many visitors reach them.', 'wp-statistics'), 'slug' => 'wp-statistics-marketing'],
    ['emoji' => '⚡', 'title' => __('Real-time dashboard', 'wp-statistics'), 'tip' => __('Real-Time Stats add-on. Watch visitors and views as they happen.', 'wp-statistics'), 'slug' => 'wp-statistics-realtime-stats'],
    ['emoji' => '🔎', 'title' => __('Advanced filtering and tracking', 'wp-statistics'), 'tip' => __('Data Plus add-on. Filter any report, and track outbound and download links.', 'wp-statistics'), 'slug' => 'wp-statistics-data-plus'],
    ['emoji' => '📈', 'title' => __('Admin-bar mini chart', 'wp-statistics'), 'tip' => __('Mini Chart add-on. A small traffic chart in the WordPress admin bar.', 'wp-statistics'), 'slug' => 'wp-statistics-mini-chart'],
    ['emoji' => '📬', 'title' => __('Branded email reports', 'wp-statistics'), 'tip' => __('Advanced Reporting add-on. Email reports with your own branding.', 'wp-statistics'), 'slug' => 'wp-statistics-advanced-reporting'],
    ['emoji' => '🔌', 'title' => __('REST API', 'wp-statistics'), 'tip' => __('REST API add-on. Read your statistics from other tools and apps.', 'wp-statistics'), 'slug' => 'wp-statistics-rest-api'],
    ['emoji' => '🧩', 'title' => __('Front-end widgets and blocks', 'wp-statistics'), 'tip' => __('Advanced Widgets add-on. Show stats to your visitors with blocks and widgets.', 'wp-statistics'), 'slug' => 'wp-statistics-widgets'],
    ['emoji' => '🎨', 'title' => __('White-label and custom branding', 'wp-statistics'), 'tip' => __('Customisation add-on. Your colours and your brand in the admin and in reports.', 'wp-statistics'), 'slug' => 'wp-statistics-customization'],
];
?>
<section class="wps-showcase wps-lock-page--container" id="wps-premium-showcase" aria-label="<?php esc_attr_e('What Premium adds', 'wp-statistics'); ?>">
    <div class="wps-lock-page__head">🚀 <?php esc_html_e('Unlock more with WP Statistics Premium', 'wp-statistics'); ?></div>
    <div class="wps-lock-page__description"><?php esc_html_e('WP Statistics stays free. Premium adds these, with a 14-day money-back guarantee. Click a feature to see it.', 'wp-statistics'); ?></div>
    <div class="wps-lock-page__actions">
        <a target="_blank" rel="noopener noreferrer" class="wps-lock-page__action wps-lock-page__action--premium" href="<?php echo esc_url($buyUrl); ?>"><?php esc_html_e('Go Premium', 'wp-statistics'); ?></a>
    </div>

    <table class="wps-showcase__table">
        <thead>
            <tr>
                <th><?php esc_html_e('Feature', 'wp-statistics'); ?></th>
                <th><?php esc_html_e('Free', 'wp-statistics'); ?> <small>(<?php esc_html_e('you are here', 'wp-statistics'); ?>)</small></th>
                <th class="is-premium"><?php esc_html_e('Premium', 'wp-statistics'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($freeFeatures as $label) : ?>
                <tr>
                    <td><?php echo esc_html($label); ?></td>
                    <td class="wps-showcase__yes">✓</td>
                    <td class="is-premium wps-showcase__yes">✓</td>
                </tr>
            <?php endforeach; ?>
            <?php foreach ($features as $feature) : ?>
                <tr>
                    <td>
                        <span class="wps-showcase__label"><?php echo esc_html($feature['title']); ?></span>
                        <a class="wps-showcase__info js-wps-openPremiumModal" href="<?php echo esc_url($addonUrl($feature['slug'])); ?>" data-target="<?php echo esc_attr($feature['slug']); ?>" aria-label="<?php echo esc_attr(sprintf(/* translators: %s: feature name */ __('More about %s', 'wp-statistics'), $feature['title'])); ?>">
                            <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><circle cx="12" cy="12" r="9.5" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M12 11v5.5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/><circle cx="12" cy="7.6" r="1.2" fill="currentColor"/></svg>
                        </a>
                    </td>
                    <td class="wps-showcase__no">–</td>
                    <td class="is-premium wps-showcase__yes">✓</td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td><?php esc_html_e('Priority support', 'wp-statistics'); ?></td>
                <td><?php esc_html_e('Email and community', 'wp-statistics'); ?></td>
                <td class="is-premium wps-showcase__support"><svg class="wps-showcase__seal" aria-hidden="true" viewBox="0 0 24 24" width="18" height="18"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z" fill="#5fae62"/><path d="m8.5 12.3 2.4 2.4 4.6-4.9" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg> <?php esc_html_e('Premium Support', 'wp-statistics'); ?></td>
            </tr>
        </tbody>
    </table>
</section>
