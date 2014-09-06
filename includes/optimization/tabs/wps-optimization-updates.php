<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#hash-ips-submit").click(function(){
			var agree = confirm('<?php _e('This will replace all IP addresses in the database with hash values and cannot be undo, are you sure?', 'wp_statistics'); ?>');

			if(agree)
				location.href=document.URL+'&tab=updates&hash-ips=1';
		
		});
	});
</script>
<div class="wrap">
	<table class="form-table">
		<tbody>
				<tr valign="top">
				<th scope="row" colspan="2"><h3><?php _e('GeoIP File Info', 'wp_statistics'); ?></h3></th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<?php _e('File Date', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php $upload_dir =  wp_upload_dir();
					$GeoIP_filename = $upload_dir['basedir'] . '/wp-statistics/GeoLite2-Country.mmdb'; 
					$GeoIP_filedate = @filemtime( $GeoIP_filename );
				
					if( $GeoIP_filedate === FALSE ) {
						_e('Database file does not exist.', 'wp_statistics');
					} else {
						echo date_i18n(get_option('date_format') . ' @ ' . get_option('time_format'), $GeoIP_filedate); 
					}?></strong>
					<p class="description"><?php _e('The file date of the GeoIP database.', 'wp_statistics'); ?></p>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<?php _e('File Size', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php echo formatSize( @filesize( $GeoIP_filename ) );
					
					/* format size of file 
					* @author Mike Zriel
					* @date 7 March 2011
					* @website www.zriel.com
					*/
					function formatSize($size) {
					$sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
					if ($size == 0) { return('n/a'); } else {
					return (round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]); }
					}
					?></strong>
					<p class="description"><?php _e('The file size of the GeoIP database.', 'wp_statistics'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" colspan="2"><h3><?php _e('GeoIP Options', 'wp_statistics'); ?></h3></th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<label for="populate-submit"><?php _e('Countries', 'wp_statistics'); ?>:</label>
				</th>
				
				<td>
					<input id="populate-submit" class="button button-primary" type="button" value="<?php _e('Update Now!', 'wp_statistics'); ?>" name="populate-submit" onclick="location.href=document.URL+'&tab=updates&populate=1'">
					<p class="description"><?php _e('Get updates for the location and the countries, this may take a while', 'wp_statistics'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" colspan="2"><h3><?php _e('IP Addresses', 'wp_statistics'); ?></h3></th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<label for="populate-submit"><?php _e('Hash IP Addresses', 'wp_statistics'); ?>:</label>
				</th>
				
				<td>
					<input id="hash-ips-submit" class="button button-primary" type="button" value="<?php _e('Update Now!', 'wp_statistics'); ?>" name="hash-ips-submit">
					<p class="description"><?php _e('Get updates for the location and the countries, this may take a while', 'wp_statistics'); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
</div>