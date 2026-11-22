<?php
/**
 * Small ↗ icon link placed right after a linked title. Opens the live page in a new tab.
 *
 * Args:
 *  - url   (string) Required. The live page URL. Nothing is printed when empty.
 *  - label (string) Accessible name. Default "Open page in a new tab".
 *  - class (string) Extra classes.
 *
 * Example:
 *  <a href="<?php echo esc_url($reportUrl); ?>">Title</a>
 *  <?php View::load('components/objects/new-tab-link', ['url' => $page['link']]); ?>
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (empty($url)) {
    return;
}

$label   = !empty($label) ? $label : __('Open page in a new tab', 'wp-statistics');
$classes = trim('wps-new-tab-link ' . ($class ?? ''));
?>
<a class="<?php echo esc_attr($classes); ?>" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr($label); ?>" title="<?php echo esc_attr($label); ?>"><svg viewBox="0 0 12 12" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4.5 2.5h-2v7h7v-2"/><path d="M7 1.5h3.5V5"/><path d="M10.5 1.5 5.5 6.5"/></svg></a>
