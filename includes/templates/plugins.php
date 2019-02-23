<div class="wrap wps-wrap">
	<?php WP_Statistics_Admin_Pages::show_page_title( __( 'Extensions for WP-Statistics', 'wp-statistics' ) ); ?>

    <p><p><?php _e( 'These extensions add functionality to your WP-Statistics.', 'wp-statistics' ); ?></p><br/></p>
    <?php include( WP_Statistics::$reg['plugin-dir'] . "includes/templates/add-ons.php" ); ?>
</div>