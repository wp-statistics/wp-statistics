<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly

// A page row without a title or report URL (deleted page, missing page record) shows the "-" unknown value.
if ((!isset($title) || trim((string)$title) === '') && empty($url)) {
    echo \WP_STATISTICS\Admin_Template::UnknownColumn(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    return;
}
$title = isset($title) && trim((string)$title) !== '' ? $title : __('(not set)', 'wp-statistics');
?>
<a class="wps-ellipsis-parent wps-internal-link" href="<?php echo esc_url($url); ?>" aria-label="<?php echo esc_html($title) ?>">
    <span class="wps-ellipsis-text"><?php echo esc_html($title) ?></span>
    <?php if (!empty($tooltip)): ?>
        <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
    <?php endif; ?>
</a>
