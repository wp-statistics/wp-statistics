<?php
/**
 * Small feature chips (settings banner). A chip that mentions MCP gets the "ai" look.
 *
 * Args:
 *  - items (string[]) Required. Plain text.
 *  - label (string)   Accessible name of the list.
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (empty($items) || !is_array($items)) {
    return;
}
?>
<ul class="wps-upsell-chips"<?php echo !empty($label) ? ' aria-label="' . esc_attr($label) . '"' : ''; ?>>
    <?php foreach ($items as $item) : ?>
        <li class="wps-upsell-chips__chip<?php echo stripos($item, 'MCP') !== false ? ' wps-upsell-chips__chip--ai' : ''; ?>"><?php echo esc_html($item); ?></li>
    <?php endforeach; ?>
</ul>
