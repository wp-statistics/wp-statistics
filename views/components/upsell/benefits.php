<?php
/**
 * Short benefit list with check marks (popup, lock pages).
 *
 * Args:
 *  - items (string[]) Required. Plain text, one line each.
 *  - class (string)   Extra classes.
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (empty($items) || !is_array($items)) {
    return;
}
?>
<ul class="wps-upsell-benefits<?php echo !empty($class) ? ' ' . esc_attr($class) : ''; ?>">
    <?php foreach ($items as $item) : ?>
        <li class="wps-upsell-benefits__item">
            <svg class="wps-upsell-benefits__icon" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10" fill="currentColor" opacity=".14"/><path d="m7.8 12.4 2.8 2.8 5.6-5.9" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span><?php echo esc_html($item); ?></span>
        </li>
    <?php endforeach; ?>
</ul>
