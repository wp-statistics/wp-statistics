<?php
/**
 * Source category (channel) icon: the 24x24 stroke icon in the category's fixed colour, the same in
 * the Top Source Categories widget, the Referrals > Source Categories table and the chart lines
 * (SourceChannels::getStyles()).
 *
 * Args (one of):
 *  - key   (string) Channel key, e.g. "search", "direct". Unknown or empty keys get the unassigned style.
 *  - svg   (string) Inner SVG markup (paths) and `color` (string) '#hex', for callers that already hold the style.
 *  - class (string) Extra classes.
 *
 * View::load('components/objects/source-channel-icon', ['key' => 'search']);
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Service\Analytics\Referrals\SourceChannels;

if (!empty($svg)) {
    $style = ['icon' => (string)$svg, 'color' => isset($color) ? (string)$color : ''];
} else {
    $style = SourceChannels::getStyle(isset($key) ? $key : '');
}

if (empty($style['icon'])) {
    return;
}

$iconColor = !empty($style['color']) ? sanitize_hex_color($style['color']) : '';
$classes   = trim('wps-source-channel-icon ' . (isset($class) ? $class : ''));
$svgTags   = [
    'path'     => ['d' => true],
    'circle'   => ['cx' => true, 'cy' => true, 'r' => true],
    'rect'     => ['x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true],
    'line'     => ['x1' => true, 'y1' => true, 'x2' => true, 'y2' => true],
    'polyline' => ['points' => true],
];
?>
<svg class="<?php echo esc_attr($classes); ?>" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"<?php echo $iconColor ? ' style="color:' . esc_attr($iconColor) . '"' : ''; ?>><?php echo wp_kses($style['icon'], $svgTags); ?></svg>
