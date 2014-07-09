<?php 
$wps_nonce_valid = false;

if( array_key_exists( 'wp-statistics-nonce', $_POST ) ) {
	if( wp_verify_nonce( $_POST['wp-statistics-nonce'], 'update-options' ) ) { $wps_nonce_valid = true; }
}

$wps_admin = false;

if(current_user_can(wp_statistics_validate_capability($WP_Statistics->get_option('manage_capability', 'manage_options')))) {
	$wps_admin = true;
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
		<div id="tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
			<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
<?php if( $wps_admin ) { ?>				<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active ui-state-focus"><a class="ui-tabs-anchor" href="#general-settings"><span><?php _e('General', 'wp_statistics'); ?></span></a></li><?php } ?>
				<li class="ui-state-default ui-corner-top"><a class="ui-tabs-anchor" href="#overview-display-settings"><span><?php _e('Overview', 'wp_statistics'); ?></span></a></li>
<?php if( $wps_admin ) { ?>				<li class="ui-state-default ui-corner-top"><a class="ui-tabs-anchor" href="#access-settings"><span><?php _e('Access/Exclusions', 'wp_statistics'); ?></span></a></li><?php } ?>
<?php if( $wps_admin ) { ?>				<li class="ui-state-default ui-corner-top"><a class="ui-tabs-anchor" href="#geoip-settings"><span><?php _e('GeoIP', 'wp_statistics'); ?></span></a></li><?php } ?>
<?php if( $wps_admin ) { ?>				<li class="ui-state-default ui-corner-top"><a class="ui-tabs-anchor" href="#maintenance-settings"><span><?php _e('Maintenance', 'wp_statistics'); ?></span></a></li><?php } ?>
			</ul>

			<div id="general-settings">
			<?php if( $wps_admin ) { include( dirname( __FILE__ ) . '/tabs/wps-general.php' ); } ?>
			</div>
		
			<div id="overview-display-settings">
			<?php include( dirname( __FILE__ ) . '/tabs/wps-overview-display.php' ); ?>
			</div>

			<div id="access-settings">
			<?php if( $wps_admin ) { include( dirname( __FILE__ ) . '/tabs/wps-access-level.php' ); } ?>
			</div>

			<div id="geoip-settings">
			<?php if( $wps_admin ) { include( dirname( __FILE__ ) . '/tabs/wps-geoip.php' ); } ?>
			</div>

			<div id="maintenance-settings">
			<?php if( $wps_admin ) { include( dirname( __FILE__ ) . '/tabs/wps-maintenance.php' ); } ?>
			</div>

		</div>

		<div class="submit">
			<input type="submit" class="button button-primary" name="Submit" value="<?php _e('Update', 'wp_statistics'); ?>" />
		</div>
	</form>
</div>

<?php
if( $wps_nonce_valid ) {
	if( $wps_admin ) { $WP_Statistics->save_options(); }
	$WP_Statistics->save_user_options();
}
