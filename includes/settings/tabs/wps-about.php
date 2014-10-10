<table class="form-table">
	<tbody>
		<tr valign="top">
			<td scope="row" align="center"><img src="<?php echo plugins_url('wp-statistics/assets/images/logo-250.png'); ?>"></td>
		</tr>

		<tr valign="top">
			<td scope="row" align="center"><h2><?php echo sprintf(__('WP Statistics V%s', 'wp_statistics'), WP_STATISTICS_VERSION); ?></h2></td>
		</tr>

		<tr valign="top">
			<td scope="row" align="center"><?php echo sprintf(__('This product includes GeoLite2 data created by MaxMind, available from %s.', 'wp_statistics'), '<a href="http://www.maxmind.com" target=_blank>http://www.maxmind.com</a>'); ?></td>
		</tr>

		<tr valign="top">
			<td scope="row" align="center"><hr /></td>
		</tr>

		<tr valign="top">
			<td scope="row" colspan="2"><h2><?php _e('Visit Us Online', 'wp_statistics'); ?></h2></td>
		</tr>
		
		<tr valign="top">
			<td scope="row" colspan="2"><?php echo sprintf( __('Come visit our great new %s and keep up to date on the latest news about WP Statistics.', 'wp_statistics'), '<a href="http://wp-statistics.com" target="_blank">' . __('website', 'wp_statistics') . '</a>');?></td>
		</tr>
		
		<tr valign="top">
			<td scope="row" colspan="2"><h2><?php _e('Rate and Review at WordPress.org', 'wp_statistics'); ?></h2></td>
		</tr>
		
		<tr valign="top">
			<td scope="row" colspan="2"><?php _e('Thanks for installing WP Statistics, we encourage you to submit a ', 'wp_statistics');?> <a href="http://wordpress.org/support/view/plugin-reviews/wp-statistics" target="_blank"><?php _e('rating and review', 'wp_statistics'); ?></a> <?php _e('over at WordPress.org.  Your feedback is greatly appreciated!','wp_statistics');?></td>
		</tr>
		
		<tr valign="top">
			<td scope="row" colspan="2"><h2><?php _e('Translations', 'wp_statistics'); ?></h2></td>
		</tr>
		
		<tr valign="top">
			<td scope="row" colspan="2"><?php echo sprintf( __('WP Statistics supports internationalization and we encourage our users to submit translations, please visit our %s to see the current status and %s if you would like to help.', 'wp_statistics'), '<a href="http://teamwork.wp-parsi.com/projects/wp-statistics" target="_blank">' . __('translation collaboration site', 'wp_statistics') . '</a>', '<a href="http://wp-statistics.com/contact/" target="_blank">' . __( 'drop us a line', 'wp_statistics') . '</a>');?></td>
		</tr>
		
		<tr valign="top">
			<td scope="row" colspan="2"><h2><?php _e('Support', 'wp_statistics'); ?></h2></td>
		</tr>

		<tr valign="top">
			<td scope="row" colspan="2">
				<p><?php _e("We're sorry you're having problem with WP Statistics and we're happy to help out.  Here are a few things to do before contacting us:", 'wp_statistics'); ?></p>

				<ul style="list-style-type: disc; list-style-position: inside; padding-left: 25px;">
					<li><?php echo sprintf( __('Have you read the %s?', 'wp_statistics' ), '<a title="' . __('FAQs', 'wp_statistics') . '" href="http://wp-statistics.com/?page_id=19" target="_blank">' . __('FAQs', 'wp_statistics'). '</a>');?></li>
					<li><?php echo sprintf( __('Have you read the %s?', 'wp_statistics' ), '<a title="' . __('manual', 'wp_statistics') . '" href="?page=wps_manual_menu">' . __('manual', 'wp_statistics') . '</a>');?></li>
					<li><?php echo sprintf( __('Have you search the %s for a similar issue?', 'wp_statistics' ), '<a href="http://wordpress.org/support/plugin/wp-statistics" target="_blank">' . __('support forum', 'wp_statistics') . '</a>');?></li>
					<li><?php _e('Have you search the Internet for any error messages you are receiving?', 'wp_statistics' );?></li>
				</ul>

				<p><?php _e('And a few things to double-check:', 'wp_statistics' );?></p>

				<ul style="list-style-type: disc; list-style-position: inside; padding-left: 25px;">
					<li><?php _e('How\'s your memory_limit in php.ini?', 'wp_statistics' );?></li>
					<li><?php _e('Have you tried disabling any other plugins you may have installed?', 'wp_statistics' );?></li>
					<li><?php _e('Have you tried using the default WordPress theme?', 'wp_statistics' );?></li>
					<li><?php _e('Have you double checked the plugin settings?', 'wp_statistics' );?></li>
					<li><?php _e('Do you have all the required PHP extensions installed?', 'wp_statistics' );?></li>
					<li><?php _e('Are you getting a blank or incomplete page displayed in your browser?  Did you view the source for the page and check for any fatal errors?', 'wp_statistics' );?></li>
					<li><?php _e('Have you checked your PHP and web server error logs?', 'wp_statistics' );?></li>
				</ul>

				<p><?php _e('Still not having any luck?', 'wp_statistics' );?> <?php echo sprintf(__('Then please open a new thread on the %s and we\'ll respond as soon as possible.', 'wp_statistics' ), '<a href="http://wordpress.org/support/plugin/wp-statistics" target="_blank">' . __('WordPress.org support forum', 'wp_statistics') . '</a>');?></p>

				<p><br /></p>
				
				<p><?php echo sprintf( __('Alternatively %s support is available as well.', 'wp_statistics' ), '<a href="http://forum.wp-parsi.com/forum/17-%D9%85%D8%B4%DA%A9%D9%84%D8%A7%D8%AA-%D8%AF%DB%8C%DA%AF%D8%B1/" target="_blank">' . __('Farsi', 'wp_statistics' ) .'</a>');?></p>
			</td>
		</tr>

	</tbody>
</table>