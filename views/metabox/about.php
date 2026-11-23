<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly

$wpsAboutLinks = [
    [
        'key'   => 'help',
        'label' => __('Help', 'wp-statistics'),
        'title' => __('Help Center', 'wp-statistics'),
        'url'   => WP_STATISTICS_SITE_URL . '/documentation/?utm_source=wp-statistics&utm_medium=link&utm_campaign=help',
        'icon'  => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3.5"/><path d="M5.6 5.6l3.9 3.9M14.5 14.5l3.9 3.9M18.4 5.6l-3.9 3.9M9.5 14.5l-3.9 3.9"/>',
    ],
    [
        'key'   => 'add-ons',
        'label' => __('Add-ons', 'wp-statistics'),
        'title' => __('Add-ons', 'wp-statistics'),
        'url'   => WP_STATISTICS_SITE_URL . '/add-ons/?utm_source=wp-statistics&utm_medium=link&utm_campaign=add-ons',
        'icon'  => '<path d="M10 3.5a2 2 0 0 1 4 0V5h3a1 1 0 0 1 1 1v3h1.5a2 2 0 0 1 0 4H18v4a1 1 0 0 1-1 1h-3.5v-1.5a2 2 0 0 0-4 0V19H6a1 1 0 0 1-1-1v-4h1.5a2 2 0 0 0 0-4H5V6a1 1 0 0 1 1-1h4z"/>',
    ],
    [
        'key'   => 'release',
        'label' => __('Release notes', 'wp-statistics'),
        'title' => __('Release Notes', 'wp-statistics'),
        'url'   => WP_STATISTICS_SITE_URL . '/changelog/?utm_source=wp-statistics&utm_medium=link&utm_campaign=changelog',
        'icon'  => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/>',
    ],
    [
        'key'   => 'request',
        'label' => __('Ideas', 'wp-statistics'),
        'title' => __('Request a feature', 'wp-statistics'),
        'url'   => 'https://github.com/wp-statistics/wp-statistics/issues/new/choose',
        'icon'  => '<path d="M9 18h6M10 21h4"/><path d="M12 3a6 6 0 0 0-3.6 10.8c.7.6 1.1 1.3 1.1 2.2h5c0-.9.4-1.6 1.1-2.2A6 6 0 0 0 12 3z"/>',
    ],
    [
        'key'   => 'blog',
        'label' => __('Blog', 'wp-statistics'),
        'title' => __('Blog Updates', 'wp-statistics'),
        'url'   => WP_STATISTICS_SITE_URL . '/blog/?utm_source=wp-statistics&utm_medium=link&utm_campaign=blog',
        'icon'  => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10M7 12h10M7 16h6"/>',
    ],
];
$wpsAboutReleaseUrl = WP_STATISTICS_SITE_URL . '/changelog/?utm_source=wp-statistics&utm_medium=link&utm_campaign=changelog';
?>
<div class="o-wrap wps-about-widget js-wps-widget-customization-empty">
    <div class="c-about c-about--minimal">
        <?php // The card header already says "WP Statistics": no second logo here. The version badge moves next to that title (meta-box.js); this row is the no-JS fallback. ?>
        <div class="c-about__row c-about__row--version">
            <a class="c-about-badge" href="<?php echo esc_url($wpsAboutReleaseUrl); ?>" target="_blank" title="<?php esc_attr_e("What's new", 'wp-statistics'); ?>">
                <span class="screen-reader-text"><?php esc_html_e('Version', 'wp-statistics'); ?></span>
                <span>v<?php echo esc_html(WP_STATISTICS_VERSION); ?></span>
                <span class="screen-reader-text"><?php esc_html_e("What's new", 'wp-statistics'); ?></span>
            </a>
        </div>

        <div class="c-about__row c-about__links">
            <div class="c-about__links--title screen-reader-text"><?php esc_html_e('Quick Actions', 'wp-statistics'); ?></div>
            <div class="c-about__links--content">
                <?php foreach ($wpsAboutLinks as $wpsAboutLink) : ?>
                    <a class="c-about__chip c-about__chip--<?php echo esc_attr($wpsAboutLink['key']); ?>" href="<?php echo esc_url($wpsAboutLink['url']); ?>" target="_blank" <?php echo $wpsAboutLink['title'] !== $wpsAboutLink['label'] ? 'title="' . esc_attr($wpsAboutLink['title']) . '"' : ''; ?>>
                        <svg class="c-about__links--icon c-about__links--icon__<?php echo esc_attr($wpsAboutLink['key']); ?>" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><?php echo $wpsAboutLink['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG markup ?></svg>
                        <?php echo esc_html($wpsAboutLink['label']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="c-about__row c-about__rate">
            <p><?php esc_html_e('Is WP Statistics helping your site? A short review on WordPress.org helps other site owners find it.', 'wp-statistics'); ?></p>
            <a href="https://wordpress.org/plugins/wp-statistics/#reviews" target="_blank">
                <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="#E5484D" d="M12 21s-7.5-4.6-9.6-9.3C.9 8.1 3.2 4.5 6.8 4.5c2.1 0 3.6 1.1 5.2 3 1.6-1.9 3.1-3 5.2-3 3.6 0 5.9 3.6 4.4 7.2C19.5 16.4 12 21 12 21z"/></svg>
                <?php esc_html_e('Leave a review', 'wp-statistics'); ?>
            </a>
        </div>

        <div class="c-about__row c-about__footer">
            <?php if (!is_plugin_active('wp-statistics-customization/wp-statistics-customization.php')) { ?>
                <div class="c-about__customization">
                    <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/add-ons/wp-statistics-customization/?utm_source=wp-statistics&utm_medium=link&utm_campaign=customization'); ?>" target="_blank">
                        <?php esc_html_e('How to Customize This Widget', 'wp-statistics'); ?>
                    </a>
                </div>
            <?php } ?>
            <div class="c-about__veronalabs">
                <a href="https://veronalabs.com/?utm_source=wp-statistics&utm_medium=link&utm_campaign=overview" target="_blank" title="<?php esc_attr_e('Powered by VeronaLabs', 'wp-statistics'); ?>" aria-label="VeronaLabs">
                    <img class="c-about__veronalabs--logo" src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/veronalabs-mark.svg'); ?>" alt="VeronaLabs" width="18" height="15">
                </a>
            </div>
        </div>
    </div>
</div>
