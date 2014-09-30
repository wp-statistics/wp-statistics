<div class="wrap">
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row" colspan="2"><h3><?php _e('Database Setup', 'wp_statistics'); ?></h3></th>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="index-submit"><?php _e('Re-run Install', 'wp_statistics'); ?>:</label>
				</th>

				<td>
					<input id="install-submit" class="button button-primary" type="button" value="<?php _e('Install Now!', 'wp_statistics'); ?>" name="install-submit" onclick="location.href=document.URL+'&install=1&tab=database'">
					<p class="description"><?php _e('If for some reason your installation of WP Statistics is missing the database tables or other core items, this will re-execute the install process.', 'wp_statistics'); ?></p>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row" colspan="2"><h3><?php _e('Database Index', 'wp_statistics'); ?></h3></th>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="index-submit"><?php _e('Countries', 'wp_statistics'); ?>:</label>
				</th>

				<td>
<?php 
		GLOBAL $wpdb;
		$wp_prefix = $wpdb->prefix;
		
		// Check the number of index's on the visitors table, if it's only 5 we need to check for duplicate entries and remove them
		$result = $wpdb->query("SHOW INDEX FROM {$wp_prefix}statistics_visitor WHERE Key_name = 'date_ip_agent'");

		// Note, the result will be the number of fields contained in the index, so in our case 5.
		if( $result != 5 ) {
?>			
					<input id="index-submit" class="button button-primary" type="button" value="<?php _e('Update Now!', 'wp_statistics'); ?>" name="index-submit" onclick="location.href=document.URL+'&index=1&tab=database'">
					<p class="description"><?php _e('Older installs of WP Statistics allow for duplicate entries in the visitors table in a corner case.  Newer installs protect against this with a unique index on the table.  To create the index on the older installs duplicate entries must be deleted first.  Clicking "Update Now" will scan the vistitors table, delete duplicate entries and add the index.', 'wp_statistics'); ?></p>
					<p class="description"><?php _e('This operation could take a long time on installs with many rows in the visitors table.', 'wp_statistics'); ?></p>
<?php
		}
		else {
?>
					<p class="description"><?php _e('Older installs of WP Statistics allow for duplicate entries in the visitors table in a corner case.  Newer installs protect against this with a unique index on the table.', 'wp_statistics'); ?></p>
					<p class="description"><?php _e('Congratulations, your installation is already up to date, nothing to do.', 'wp_statistics'); ?></p>
<?php
		}
?>
				</td>
			</tr>
		</tbody>
	</table>
</div>