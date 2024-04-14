<div class="wps-wrap__top">
    <h2 class="wps_title"><?php echo(isset($title) ? esc_attr($title) : (function_exists('get_admin_page_title') ? get_admin_page_title() : '')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?></h2>
    <?php do_action('wp_statistics_after_admin_page_title'); ?>
</div>
<div class="wps-wrap__main">
    <div class="wp-header-end"></div>


