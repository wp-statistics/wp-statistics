<?php
function wp_statistics_generate_about_postbox_content() {

	global $wpdb, $WP_Statistics;
	?>
	<div style="text-align: center;">
		<a href="http://wp-statistics.com" target="_blank"><img
				src="<?php echo plugins_url('wp-statistics/assets/images/logo-250.png'); ?>"></a>
	</div>

	<div id="about-links" style="text-align: center;">
		<p><a href="http://wp-statistics.com" target="_blank"><?php _e('Website', 'wp-statistics'); ?></a></p>
		| <p>
			<a href="https://wordpress.org/support/plugin/wp-statistics/reviews/?rate=5#new-post" target="_blank"><?php _e(
					'Rate and Review',
					'wp-statistics'
				); ?></a>
		</p>
		<?php
		if ( current_user_can(
			wp_statistics_validate_capability($WP_Statistics->get_option('manage_capability', 'manage_options'))
		) ) {
			?>
			| <p>
				<a href="?page=<?php echo WP_Statistics::$page['settings']; ?>&tab=about"><?php _e(
						'More Information',
						'wp-statistics'
					); ?></a>
			</p>
			<?php
		}
		?>
	</div>

	<hr/>

	<div>
		<?php echo sprintf(
			__('This product includes GeoLite2 data created by %s.', 'wp-statistics'),
			'<a href="http://www.maxmind.com" target=_blank>MaxMind</a>'
		); ?>
	</div>
	<?php
}

