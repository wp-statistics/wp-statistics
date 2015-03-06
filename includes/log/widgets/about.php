<?php
	function wp_statistics_generate_about_postbox($ISOCountryCode, $search_engines) {
	
		global $wpdb, $WP_Statistics;
?>
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php echo sprintf(__('About WP Statistics Version %s', 'wp_statistics'), WP_STATISTICS_VERSION); ?></span></h3>
					<div class="inside">
					<?php wp_statistics_generate_about_postbox_content(); ?>
					</div>
				</div>
<?php		
	}

	function wp_statistics_generate_about_postbox_content() {
	
		global $wpdb, $WP_Statistics;
?>
						<div style="text-align: center;">
							<a href="http://wp-statistics.com" target="_blank"><img src="<?php echo plugins_url('wp-statistics/assets/images/logo-250.png'); ?>"></a>
						</div>

						<div id="about-links" style="text-align: center;">
							<p><a href="http://wp-statistics.com" target="_blank"><?php _e('Website', 'wp_statistics'); ?></a></p>
							| <p><a href="http://wordpress.org/support/view/plugin-reviews/wp-statistics" target="_blank"><?php _e('Rate and Review', 'wp_statistics'); ?></a></p>
<?php
							if(current_user_can(wp_statistics_validate_capability($WP_Statistics->get_option('manage_capability', 'manage_options')))) {
?>
							| <p><a href="?page=wp-statistics/settings&tab=about"><?php _e('More Information', 'wp_statistics'); ?></a></p>
<?php
							}
?>
						</div>

						<hr />
						
						<div>
							<?php echo sprintf(__('This product includes GeoLite2 data created by MaxMind, available from %s.', 'wp_statistics'), '<a href="http://www.maxmind.com" target=_blank>http://www.maxmind.com</a>'); ?>
						</div>
<?php		
	}

