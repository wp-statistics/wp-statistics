<?php
/**
 * Card header: title, optional info tooltip, optional tools on the right.
 *
 * Args:
 *  - title   (string) Required.
 *  - tooltip (string) Optional info tooltip next to the title.
 *  - tools   (string) Optional HTML for 24px icon buttons on the right (already escaped by the caller).
 *  - context (string) 'postbox' (default, keeps the legacy .postbox-header class used inside .postbox)
 *                     or 'card' (keeps the legacy .wps-card__title class used inside .wps-card).
 *
 * Both contexts share the .wps-card-header class and the card tokens.
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$context     = isset($context) && $context === 'card' ? 'card' : 'postbox';
$legacyClass = $context === 'card' ? 'wps-card__title' : 'postbox-header';
?>
<div class="<?php echo esc_attr($legacyClass); ?> wps-card-header">
    <h2 class="wps-card-header__title">
        <?php echo esc_html($title ?? ''); ?>
        <?php if (!empty($tooltip)) : ?>
            <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
        <?php endif; ?>
    </h2>
    <?php if (!empty($tools)) : ?>
        <div class="wps-card-header__tools"><?php echo $tools; // phpcs:ignore WordPress.Security.EscapeOutput -- caller passes escaped HTML ?></div>
    <?php endif; ?>
</div>
