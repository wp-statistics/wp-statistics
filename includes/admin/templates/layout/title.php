<?php require_once WP_STATISTICS_DIR . "/includes/admin/templates/header.php";  ?>
<div class="wps-wrap__main">
<h2 class="wps_title"><?php echo(isset($title) ? $title : (function_exists('get_admin_page_title') ? get_admin_page_title() : '')); ?></h2>
<?php do_action('wp_statistics_after_admin_page_title'); ?>
<div class="wp-clearfix"></div>
