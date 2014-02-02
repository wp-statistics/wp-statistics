<?php
	if( array_key_exists( 'populate', $_GET ) ) {
		if( $_GET['populate'] == 1 ) {
			require_once( plugin_dir_path( __FILE__ ) . '../functions/geoip-populate.php' );
			echo wp_statistics_populate_geoip_info();
		}
	}
?>
<div class="wrap">
	<h2 class="nav-tab-wrapper">
		<a href="?page=wp-statistics/optimization" class="nav-tab<?php if($_GET['tab'] == '') { echo " nav-tab-active";} ?>"><?php _e('Resources/Information', 'wp_statistics'); ?></a>
		<a href="?page=wp-statistics/optimization&tab=export" class="nav-tab<?php if($_GET['tab'] == 'export') { echo " nav-tab-active"; } ?>"><?php _e('Export', 'wp_statistics'); ?></a>
		<a href="?page=wp-statistics/optimization&tab=purging" class="nav-tab<?php if($_GET['tab'] == 'purging') { echo " nav-tab-active"; } ?>"><?php _e('Purging', 'wp_statistics'); ?></a>
		<?php if( version_compare(phpversion(), WP_STATISTICS_REQUIRED_GEOIP_PHP_VERSION, '>') ) { ?>
		<a href="?page=wp-statistics/optimization&tab=updates" class="nav-tab<?php if($_GET['tab'] == 'updates') { echo " nav-tab-active"; } ?>"><?php _e('Updates', 'wp_statistics'); ?></a>
		<?php } ?>
	</h2>
	
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row" colspan="2"><h3><?php _e('GeoIP Options', 'wp_statistics'); ?></h3></th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<label for="populate-submit"><?php _e('Countries', 'wp_statistics'); ?>:</label>
				</th>
				
				<td>
					<input id="populate-submit" class="button button-primary" type="button" value="<?php _e('Update Now!', 'wp_statistics'); ?>" name="populate-submit" onclick="location.href=document.URL+'&populate=1'">
					<p class="description"><?php _e('Get updates for the location and the countries, this may take a while', 'wp_statistics'); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
</div>