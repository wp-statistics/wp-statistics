<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Consent item, passed: the consent plugin works and every visitor can be counted.
 *
 * @var string $message      Why every visitor is counted (plain text).
 * @var bool   $showOptInTip Show the opt-in tip (WP Consent API with an opt-out banner).
 */
?>
<div class="wps-mb-16"><?php echo esc_html($message); ?></div>
<?php if (!empty($showOptInTip)) : ?>
    <div class="wps-audit-card__suggestion">
        <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Good to Know', 'wp-statistics') ?></div>
        <div class="wps-audit-card__suggestion-text">
            <?php echo esc_html__('If you switch your banner to opt-in, only visitors who accept are counted.', 'wp-statistics'); ?>
        </div>
    </div>
<?php endif; ?>
