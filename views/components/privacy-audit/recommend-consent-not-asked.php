<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Menus;

/**
 * Consent item: the consent plugin does not ask visitors about WP Statistics, while settings record
 * personal data.
 *
 * @var string   $pluginName Consent plugin name.
 * @var string[] $settings   Settings that record personal data.
 */
?>
<div class="wps-mb-16">
    <?php
    printf(
        /* translators: %s: consent plugin name, for example "Real Cookie Banner". */
        esc_html__('%s has no WP Statistics service (or its banner is off), so visitors are counted without being asked.', 'wp-statistics'),
        esc_html($pluginName)
    );
    ?>
</div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Why This Matters', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <p><?php echo esc_html__('These settings make WP Statistics record personal data:', 'wp-statistics'); ?></p>
        <ul>
            <?php foreach ($settings as $setting) : ?>
                <li><?php echo esc_html($setting); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<div class="wps-audit-card__suggestion">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('How to Fix', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <p>
            <?php
            printf(
                /* translators: %s: consent plugin name. */
                esc_html__('Add the WP Statistics service in %s, or change the settings above.', 'wp-statistics'),
                esc_html($pluginName)
            );
            ?>
        </p>
        <p><a href="<?php echo esc_url(Menus::admin_url('settings', ['tab' => 'privacy-settings'])); ?>"><?php echo esc_html__('Open privacy settings ›', 'wp-statistics'); ?></a></p>
    </div>
</div>
