<?php 
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
?>
<div class="wrap">
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row" colspan="2"><h3><?php _e('Resources', 'wp_statistics'); ?></h3></th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<?php _e('Memory usage in PHP', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php echo number_format_i18n(memory_get_usage()); ?></strong> <?php _e('Byte', 'wp_statistics'); ?>
					<p class="description"><?php _e('Memory usage in PHP', 'wp_statistics'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<?php _e('Last Overview page memory usage', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php echo number_format_i18n($WP_Statistics->get_option('last_overview_memory')); ?></strong> <?php _e('Byte', 'wp_statistics'); ?>
					<p class="description"><?php _e('Memory usage in the overview page', 'wp_statistics'); ?></p>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<?php _e('PHP Memory Limit', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php echo ini_get('memory_limit'); ?></strong>
					<p class="description"><?php _e('The memory limit a script is allowed to consume, set in php.ini.', 'wp_statistics'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<?php echo sprintf(__('Number of rows in the %s table', 'wp_statistics'), '<code>' . $table_prefix . 'statistics_' . 'useronline' . '</code>'); ?>:
				</th>
				
				<td>
					<strong><?php echo number_format_i18n($result['useronline']); ?></strong> <?php _e('Row', 'wp_statistics'); ?>
					<p class="description"><?php _e('Number of rows', 'wp_statistics'); ?></p>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<?php echo sprintf(__('Number of rows in the %s table', 'wp_statistics'), '<code>' . $table_prefix . 'statistics_' . 'visit' . '</code>'); ?>:
				</th>
				
				<td>
					<strong><?php echo number_format_i18n($result['visit']); ?></strong> <?php _e('Row', 'wp_statistics'); ?>
					<p class="description"><?php _e('Number of rows', 'wp_statistics'); ?></p>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<?php echo sprintf(__('Number of rows in the %s table', 'wp_statistics'), '<code>' . $table_prefix . 'statistics_' . 'visitor' . '</code>'); ?>:
				</th>
				
				<td>
					<strong><?php echo number_format_i18n($result['visitor']); ?></strong> <?php _e('Row', 'wp_statistics'); ?>
					<p class="description"><?php _e('Number of rows', 'wp_statistics'); ?></p>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<?php echo sprintf(__('Number of rows in the %s table', 'wp_statistics'), '<code>' . $table_prefix . 'statistics_' . 'exclusions' . '</code>'); ?>:
				</th>
				
				<td>
					<strong><?php echo number_format_i18n($result['exclusions']); ?></strong> <?php _e('Row', 'wp_statistics'); ?>
					<p class="description"><?php _e('Number of rows', 'wp_statistics'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<?php echo sprintf(__('Number of rows in the %s table', 'wp_statistics'), '<code>' . $table_prefix . 'statistics_' . 'pages' . '</code>'); ?>:
				</th>
				
				<td>
					<strong><?php echo number_format_i18n($result['pages']); ?></strong> <?php _e('Row', 'wp_statistics'); ?>
					<p class="description"><?php _e('Number of rows', 'wp_statistics'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" colspan="2"><h3><?php _e('Version Info', 'wp_statistics'); ?></h3></th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<?php _e('WP Statistics Version', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php echo WP_STATISTICS_VERSION; ?></strong>
					<p class="description"><?php _e('The WP Statistics version you are running.', 'wp_statistics'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<?php _e('PHP Version', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php echo phpversion(); ?></strong>
					<p class="description"><?php _e('The PHP version you are running.', 'wp_statistics'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<?php _e('PHP Safe Mode', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php if( ini_get('safe_mode') ) { echo 'Yes'; } else { echo 'No'; } ?></strong>
					<p class="description"><?php _e('Is PHP Safe Mode active.  The GeoIP code is not supported in Safe Mode.', 'wp_statistics'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<?php _e('jQuery Version', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><script type="text/javascript">document.write(jQuery().jquery);</script></strong>
					<p class="description"><?php _e('The jQuery version you are running.', 'wp_statistics'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<?php _e('cURL Version', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php if( function_exists('curl_version') ) { $curl_ver = curl_version(); echo $curl_ver['version']; } else { _e('cURL not installed', 'wp_statistics'); }?></strong>
					<p class="description"><?php _e('The PHP cURL Extension version you are running.  cURL is required for the GeoIP code, if it is not installed GeoIP will be disabled.', 'wp_statistics'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<?php _e('BC Math', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php if( function_exists('bcadd') ) { _e('Installed','wp_statistics'); } else { _e('Not installed', 'wp_statistics'); }?></strong>
					<p class="description"><?php _e('If the PHP BC Math Extension is installed.  BC Math is no longer required for the GeoIP code and is listed here only for historical reasons.', 'wp_statistics'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" colspan="2"><h3><?php _e('File Info', 'wp_statistics'); ?></h3></th>
			</tr>

			<tr valign="top">
				<th scope="row">
					<?php _e('GeoIP Database', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php $upload_dir =  wp_upload_dir();
					$GeoIP_filename = $upload_dir['basedir'] . '/wp-statistics/GeoLite2-Country.mmdb'; 
					$GeoIP_filedate = @filemtime( $GeoIP_filename );
				
					if( $GeoIP_filedate === FALSE ) {
						_e('Database file does not exist.', 'wp_statistics');
					} else {
						echo formatSize( @filesize( $GeoIP_filename ) ) . __(', created on ', 'wp_statistics') . date_i18n(get_option('date_format') . ' @ ' . get_option('time_format'), $GeoIP_filedate); 
					}?></strong>
					<p class="description"><?php _e('The file size and date of the GeoIP database.', 'wp_statistics'); ?></p>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<?php _e('browscap.ini File', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php 
					$browscap_filename = $upload_dir['basedir'] . '/wp-statistics/browscap.ini'; 
					$browscap_filedate = @filemtime( $browscap_filename );
				
					if( $browscap_filedate === FALSE ) {
						_e('browscap.ini file does not exist.', 'wp_statistics');
					} else {
						echo formatSize( @filesize( $browscap_filename ) ) . __(', created on ', 'wp_statistics') . date_i18n(get_option('date_format') . ' @ ' . get_option('time_format'), $browscap_filedate); 
					}?></strong>
					<p class="description"><?php _e('The file size and date of the browscap.ini file.', 'wp_statistics'); ?></p>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<?php _e('browscap Cache File', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php 
					$browscap_filename = $upload_dir['basedir'] . '/wp-statistics/cache.php'; 
					$browscap_filedate = @filemtime( $browscap_filename );
				
					if( $browscap_filedate === FALSE ) {
						_e('browscap cache file does not exist.', 'wp_statistics');
					} else {
						echo formatSize( @filesize( $browscap_filename ) ) . __(', created on ', 'wp_statistics') . date_i18n(get_option('date_format') . ' @ ' . get_option('time_format'), $browscap_filedate); 
					}?></strong>
					<p class="description"><?php _e('The file size and date of the browscap cache file.', 'wp_statistics'); ?></p>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row" colspan="2"><h3><?php _e('Client Info', 'wp_statistics'); ?></h3></th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<?php _e('Client IP', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php echo $WP_Statistics->get_IP(); ?></strong>
					<p class="description"><?php _e('The client IP address.', 'wp_statistics'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<?php _e('User Agent', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php echo $_SERVER['HTTP_USER_AGENT']; ?></strong>
					<p class="description"><?php _e('The client user agent string.', 'wp_statistics'); ?></p>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<?php _e('Browser', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php $agent = $WP_Statistics->get_UserAgent();
					echo $agent['browser'];
					?></strong>
					<p class="description"><?php _e('The detected client browser.', 'wp_statistics'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<?php _e('Version', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php echo $agent['version'];?></strong>
					<p class="description"><?php _e('The detected client browser version.', 'wp_statistics'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<?php _e('Platform', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php echo $agent['platform'];?></strong>
					<p class="description"><?php _e('The detected client platform.', 'wp_statistics'); ?></p>
				</td>
			</tr>
			
		</tbody>
	</table>
</div>