<?php
/**
 * Small locked card: a title, one line on what the add-on shows, and an unlock link to its popup.
 *
 * Args:
 *  - title   (string) Card title.
 *  - text    (string) One line on what the card would show.
 *  - addon   (string) Add-on slug, e.g. "wp-statistics-marketing".
 *  - surface (string) Where it is shown, for the upsell link.
 *  - label   (string) Link text, e.g. "Unlock with Marketing".
 *
 * Example:
 *  View::load('components/lock-sections/locked-card', ['title' => '...', 'text' => '...', 'addon' => 'wp-statistics-data-plus', 'surface' => 'single-content-clicks', 'label' => '...']);
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Service\Admin\LicenseManagement\UpsellLink;

if (empty($title) || empty($addon)) return;

$surface = !empty($surface) ? (string)$surface : 'locked-card';
?>
<div class="wps-card wps-locked-card">
    <div class="wps-card__title">
        <h2>
            <svg class="wps-locked-card__icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true" focusable="false"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
            <?php echo esc_html($title); ?>
        </h2>
    </div>
    <div class="inside">
        <?php if (!empty($text)) : ?>
            <p class="wps-locked-card__text"><?php echo esc_html($text); ?></p>
        <?php endif; ?>
        <a class="wps-locked-card__link js-wps-openPremiumModal" href="<?php echo esc_url(UpsellLink::addon($addon, $surface)); ?>"<?php echo UpsellLink::popupAttributes($addon, $surface); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper ?>><?php echo esc_html(!empty($label) ? $label : __('Unlock', 'wp-statistics')); ?></a>
    </div>
</div>
