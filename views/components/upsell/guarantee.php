<?php
/**
 * One line: "14-day money-back guarantee" with a shield icon, then optional extra text after a dot
 * (settings card and lock page: "· Included in Premium with all 8 add-ons").
 *
 * Args:
 *  - extra (string) Optional text after the guarantee.
 *  - class (string) Extra classes.
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>
<p class="wps-upsell-guarantee<?php echo !empty($class) ? ' ' . esc_attr($class) : ''; ?>">
    <span class="wps-upsell-guarantee__main">
        <svg class="wps-upsell-guarantee__icon" width="14" height="14" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2.8 4.5 5.6v5.6c0 4.6 3.1 8.3 7.5 10 4.4-1.7 7.5-5.4 7.5-10V5.6L12 2.8Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m8.6 12.2 2.4 2.4 4.5-4.8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span><?php esc_html_e('14-day money-back guarantee', 'wp-statistics'); ?></span>
    </span>
    <?php if (!empty($extra)) : ?>
        <span class="wps-upsell-guarantee__sep" aria-hidden="true">&middot;</span>
        <span class="wps-upsell-guarantee__extra"><?php echo esc_html($extra); ?></span>
    <?php endif; ?>
</p>
