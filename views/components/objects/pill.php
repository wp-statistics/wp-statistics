<?php
/**
 * Small rounded label with an optional icon: visitor facts (country, browser, OS, device), "Latest", roles.
 *
 * Args:
 *  - text    (string) Required. Plain text.
 *  - icon    (string) Image URL shown before the text (flag, browser logo...).
 *  - shape   (string) Icon shape: `logo` (16px, default), `flag` (16x12), `avatar` (round).
 *  - url     (string) Makes the pill a link.
 *  - title   (string) Tooltip / longer text.
 *  - variant (string) `soft` (default, grey with hairline), `brand` (tinted brand), `tag` (small square label).
 *  - class   (string) Extra classes.
 *
 * Example:
 *  View::load('components/objects/pill', ['text' => 'Firefox 148', 'icon' => $visitor->getBrowser()->getLogo()]);
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!isset($text) || $text === '' || $text === null) {
    return;
}

$variant = in_array($variant ?? 'soft', ['soft', 'brand', 'tag'], true) ? ($variant ?? 'soft') : 'soft';
$shape   = in_array($shape ?? 'logo', ['logo', 'flag', 'avatar'], true) ? ($shape ?? 'logo') : 'logo';
$classes = trim('wps-pill wps-pill--' . $variant . ' ' . ($class ?? ''));
$tagName = !empty($url) ? 'a' : 'span';
?>
<<?php echo $tagName; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="<?php echo esc_attr($classes); ?>"<?php if (!empty($url)) : ?> href="<?php echo esc_url($url); ?>"<?php endif; ?><?php if (!empty($title)) : ?> title="<?php echo esc_attr($title); ?>"<?php endif; ?>><?php if (!empty($icon)) : ?><img class="wps-pill__icon wps-pill__icon--<?php echo esc_attr($shape); ?>" src="<?php echo esc_url($icon); ?>" alt="" aria-hidden="true"><?php endif; ?><span class="wps-pill__text"><?php echo esc_html($text); ?></span></<?php echo $tagName; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
