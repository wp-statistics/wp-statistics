<?php 
if( $wps_nonce_valid ) {

	if( array_key_exists( 'wps_remove_plugin', $_POST ) ) {
		if( is_super_admin() ) {
			update_option('wp_statistics_removal', 'true' );
		}
	}
}

?>
<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row" colspan="2"><h3><?php _e('WP Statisitcs Removal', 'wp_statistics'); ?></h3></th>
		</tr>

		<tr valign="top">
			<th scope="row" colspan="2">
				<?php echo __('Uninstalling WP Statistics will not remove the data and settings, you can use this option to remove the WP Statistics data from your install before uninstalling the plugin.', 'wp_statistics'); ?>
				<br>
				<br>
				<?php echo __('Once you submit this form the settings will be deleted during the page load, however WP Statistics will still show up in your Admin menu until another page load is executed.', 'wp_statistics'); ?>
			</th>
		</tr>
		
		<tr valign="top">
			<th scope="row">
				<label for="remove-plugin"><?php _e('Remove data and settings', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input id="remove-plugin" type="checkbox" name="wps_remove_plugin">
				<label for="remove-plugin"><?php _e('Remove', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('Remove data and settings, this action cannot be undone.', 'wp_statistics'); ?></p>
			</td>
		</tr>

	</tbody>
</table>

<?php submit_button(__('Update', 'wp_statistics'), 'primary', 'submit'); ?>