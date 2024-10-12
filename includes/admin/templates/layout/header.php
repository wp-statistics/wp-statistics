<div class="wrap wps-wrap<?php echo(isset($class) ? ' ' . esc_attr($class) : ''); ?>">
<?php
use WP_Statistics\Components\View;

require_once WP_STATISTICS_DIR . "/includes/admin/templates/header.php";
View::load("components/premium-pop-up/welcome-modal");
?>

