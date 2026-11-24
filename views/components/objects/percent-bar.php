<?php
/**
 * Percent component: share bar, rate bar or ring.
 *
 * Args:
 *  - value     (float)  Percent 0-100. Optional when count + total are given.
 *  - count     (int)    Part of the whole (used with total).
 *  - total     (int)    The whole. Share = count / total.
 *  - variant   (string) 'share' (120px track, default), 'rate' (56px track) or 'ring' (18px SVG ring).
 *  - label     (string) Optional accessible label, e.g. "Share of all visitors". Read by screen readers only.
 *  - show_value (bool)  Print the number next to the visual (default true). The visual is always aria-hidden.
 *  - class     (string) Extra classes for the wrapper.
 *
 * Example:
 *  View::load('components/objects/percent-bar', ['count' => 578, 'total' => 3501]);
 *  View::load('components/objects/percent-bar', ['value' => 41.6, 'variant' => 'rate']);
 *  View::load('components/objects/percent-bar', ['value' => 43.4, 'variant' => 'ring']);
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Helper;

$variant    = isset($variant) && in_array($variant, ['share', 'rate', 'ring'], true) ? $variant : 'share';
$show_value = isset($show_value) ? (bool) $show_value : true;

if (isset($count, $total)) {
    $value = Helper::calculatePercentage((float) $count, (float) $total);
}

$value   = isset($value) && is_numeric($value) ? (float) $value : 0.0;
$value   = max(0.0, min(100.0, $value));
$rounded = round($value, 1);
$text    = number_format_i18n($rounded, floor($rounded) == $rounded ? 0 : 1) . '%';

// Keep a sliver visible for tiny non-zero values so the row never looks empty.
$fill = $value > 0 ? max($value, 2) : 0;

$classes = trim('wps-percent wps-percent--' . $variant . ' ' . ($class ?? ''));
?>
<span class="<?php echo esc_attr($classes); ?>"<?php echo !empty($label) ? ' title="' . esc_attr($label) . '"' : ''; ?>>
    <?php if ($variant === 'ring') :
        $radius        = 7;
        $circumference = 2 * M_PI * $radius;
        $dash          = $circumference * $value / 100;
        ?>
        <svg class="wps-percent__ring" viewBox="0 0 18 18" width="18" height="18" aria-hidden="true" focusable="false">
            <circle class="wps-percent__ring-track" cx="9" cy="9" r="<?php echo esc_attr($radius); ?>"></circle>
            <circle class="wps-percent__ring-fill" cx="9" cy="9" r="<?php echo esc_attr($radius); ?>" stroke-dasharray="<?php echo esc_attr(round($dash, 2) . ' ' . round($circumference, 2)); ?>" transform="rotate(-90 9 9)"></circle>
        </svg>
    <?php endif; ?>
    <?php if ($show_value) : ?>
        <span class="wps-percent__value"><?php echo esc_html($text); ?></span>
    <?php else : ?>
        <span class="screen-reader-text"><?php echo esc_html($text); ?></span>
    <?php endif; ?>
    <?php if (!empty($label)) : ?>
        <span class="screen-reader-text"><?php echo esc_html($label); ?></span>
    <?php endif; ?>
    <?php if ($variant !== 'ring') : ?>
        <span class="wps-percent__track" aria-hidden="true"><span class="wps-percent__fill" style="width:<?php echo esc_attr(round($fill, 2)); ?>%"></span></span>
    <?php endif; ?>
</span>
