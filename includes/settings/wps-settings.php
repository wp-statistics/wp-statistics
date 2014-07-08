<?php 
$wps_nonce_valid = false;

if( array_key_exists( 'wp-statistics-nonce', $_POST ) ) {
	if( wp_verify_nonce( $_POST['wp-statistics-nonce'], 'update-options' ) ) { $wps_nonce_valid = true; }
}
?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#tabs").tabs();
	} );
</script>
<a name="top"></a>
<div class="wrap">
	<form method="post">
		<?php wp_nonce_field('update-options', 'wp-statistics-nonce');?>
		<div id="tabs">
			<ul>
				<li><a href="#general-settings"><span><?php _e('General', 'wp_statistics'); ?></span></a></li>
				<li><a href="#overview-display-settings"><span><?php _e('Overview', 'wp_statistics'); ?></span></a></li>
				<li><a href="#access-settings"><span><?php _e('Access/Exclusions', 'wp_statistics'); ?></span></a></li>
				<li><a href="#geoip-settings"><span><?php _e('GeoIP', 'wp_statistics'); ?></span></a></li>
				<li><a href="#maintenance-settings"><span><?php _e('Maintenance', 'wp_statistics'); ?></span></a></li>
			</ul>

			<div id="general-settings">
			<?php include( dirname( __FILE__ ) . '/tabs/wps-general.php' ); ?>
			</div>
		
			<div id="overview-display-settings">
			<?php include( dirname( __FILE__ ) . '/tabs/wps-overview-display.php' ); ?>
			</div>

			<div id="access-settings">
			<?php include( dirname( __FILE__ ) . '/tabs/wps-access-level.php' ); ?>
			</div>

			<div id="geoip-settings">
			<?php include( dirname( __FILE__ ) . '/tabs/wps-geoip.php' ); ?>
			</div>

			<div id="maintenance-settings">
			<?php include( dirname( __FILE__ ) . '/tabs/wps-maintenance.php' ); ?>
			</div>

		</div>

		<div class="submit">
			<input type="submit" class="button button-primary" name="Submit" value="<?php _e('Update', 'wp_statistics'); ?>" />
		</div>
	</form>
</div>

<?php
if( $wps_nonce_valid ) {
	$WP_Statistics->save_options();
	$WP_Statistics->save_user_options();
}
