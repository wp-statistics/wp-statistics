<?php
/**
 * Change chip: "▲ 12.3%" versus the previous period.
 *
 * The arrow always follows the sign of the change. The color follows the metric's polarity:
 *  - positive (default): up is good (green), down is bad (red)
 *  - negative: down is good (bounce rate, exit rate)
 *  - neutral:  grey both ways (shares where neither direction is better)
 *
 * Args:
 *  - change    (float|null) Signed change. Null prints nothing.
 *  - polarity  (string)     'positive' | 'negative' | 'neutral'.
 *  - decimals  (int)        Default 1.
 *  - hint      (bool)       Print "Lower is better" under the chip (negative polarity only).
 *  - class     (string)     Extra classes (use for backwards-compatible aliases).
 *  - tag       (string)     Wrapper tag, 'span' (default) or 'div'.
 *
 * Example:
 *  View::load('components/objects/change-chip', ['change' => -0.6, 'polarity' => 'negative']);
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!isset($change) || $change === null || $change === '' || !is_numeric($change)) {
    return;
}

$change   = (float) $change;
$polarity = isset($polarity) && in_array($polarity, ['positive', 'negative', 'neutral'], true) ? $polarity : 'positive';
$decimals = isset($decimals) ? (int) $decimals : 1;
$tag      = isset($tag) && $tag === 'div' ? 'div' : 'span';

$direction = $change > 0 ? 'up' : ($change < 0 ? 'down' : 'flat');

if ($direction === 'flat' || $polarity === 'neutral') {
    $tone = 'flat';
} else {
    $isGood = ($polarity === 'negative') ? $direction === 'down' : $direction === 'up';
    $tone   = $isGood ? 'good' : 'bad';
}

$classes = trim(sprintf('wps-change wps-change--%s wps-change--%s %s', $direction, $tone, $class ?? ''));
// Same format as the other change chips (Traffic Summary): one decimal, no trailing ".0" ("50%", "12.5%").
$rounded = round(abs($change), $decimals);
$text    = number_format_i18n($rounded, (fmod($rounded, 1.0) == 0.0) ? 0 : $decimals) . '%';

if ($direction === 'up') {
    /* translators: %s: change value, e.g. 12.3% */
    $srText = sprintf(__('Up %s compared to the previous period', 'wp-statistics'), $text);
} elseif ($direction === 'down') {
    /* translators: %s: change value, e.g. 12.3% */
    $srText = sprintf(__('Down %s compared to the previous period', 'wp-statistics'), $text);
} else {
    $srText = __('No change compared to the previous period', 'wp-statistics');
}
?>
<<?php echo $tag; // phpcs:ignore -- fixed whitelist ?> class="<?php echo esc_attr($classes); ?>" title="<?php echo esc_attr($srText); ?>"><span aria-hidden="true"><?php echo esc_html($text); ?></span><span class="screen-reader-text"><?php echo esc_html($srText); ?></span></<?php echo $tag; // phpcs:ignore ?>>
<?php if (!empty($hint) && $polarity === 'negative') : ?>
    <span class="wps-change-hint"><?php esc_html_e('Lower is better', 'wp-statistics'); ?></span>
<?php endif; ?>
