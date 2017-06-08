<?php
add_shortcode( 'wpstatistics', 'wp_statistics_shortcodes' );
add_filter( 'widget_text', 'do_shortcode' );

function wp_statistics_shortcodes( $atts ) {
	/*
		WP Statitics shortcode is in the format of:

			[wpstatistics stat=xxx time=xxxx provider=xxxx format=xxxxxx id=xxx]

		Where:
			stat = the statistic you want.
			time = is the timeframe, strtotime() (http://php.net/manual/en/datetime.formats.php) will be used to calculate it.
			provider = the search provider to get stats on.
			format = i18n, english, none.
			id = the page/post id to get stats on.
	*/

	if ( ! is_array( $atts ) ) {
		return;
	}
	if ( ! array_key_exists( 'stat', $atts ) ) {
		return;
	}

	if ( ! array_key_exists( 'time', $atts ) ) {
		$atts['time'] = null;
	}
	if ( ! array_key_exists( 'provider', $atts ) ) {
		$atts['provider'] = 'all';
	}
	if ( ! array_key_exists( 'format', $atts ) ) {
		$atts['format'] = null;
	}
	if ( ! array_key_exists( 'id', $atts ) ) {
		$atts['id'] = - 1;
	}

	$formatnumber = array_key_exists( 'format', $atts );

	switch ( $atts['stat'] ) {
		case 'usersonline':
			$result = wp_statistics_useronline();
			break;

		case 'visits':
			$result = wp_statistics_visit( $atts['time'] );
			break;

		case 'visitors':
			$result = wp_statistics_visitor( $atts['time'], null, true );
			break;

		case 'pagevisits':
			$result = wp_statistics_pages( $atts['time'], null, $atts['id'] );
			break;

		case 'searches':
			$result = wp_statistics_searchengine( $atts['provider'], $atts['time'] );
			break;

		case 'postcount':
			$result = wp_statistics_countposts();
			break;

		case 'pagecount':
			$result = wp_statistics_countpages();
			break;

		case 'commentcount':
			$result = wp_statistics_countcomment();
			break;

		case 'spamcount':
			$result = wp_statistics_countspam();
			break;

		case 'usercount':
			$result = wp_statistics_countusers();
			break;

		case 'postaverage':
			$result = wp_statistics_average_post();
			break;

		case 'commentaverage':
			$result = wp_statistics_average_comment();
			break;

		case 'useraverage':
			$result = wp_statistics_average_registeruser();
			break;

		case 'lpd':
			$result       = wp_statistics_lastpostdate();
			$formatnumber = false;
			break;
	}

	if ( $formatnumber ) {
		switch ( strtolower( $atts['format'] ) ) {
			case 'i18n':
				$result = number_format_i18n( $result );

				break;
			case 'english':
				$result = number_format( $result );

				break;
		}
	}

	return $result;
}

add_action( 'admin_init', 'wp_statistics_shortcake' );

function wp_statistics_shortcake() {
	// ShortCake support if loaded.
	if ( function_exists( 'shortcode_ui_register_for_shortcode' ) ) {
		$se_list = wp_statistics_searchengine_list();

		$se_options = array( '' => 'None' );

		foreach ( $se_list as $se ) {
			$se_options[ $se['tag'] ] = $se['translated'];
		}

		shortcode_ui_register_for_shortcode(
			'wpstatistics',
			array(

				// Display label. String. Required.
				'label'         => 'WP Statistics',

				// Icon/image for shortcode. Optional. src or dashicons-$icon. Defaults to carrot.
				'listItemImage' => '<img src="' . plugin_dir_url( __FILE__ ) . 'assets/images/logo-250.png" width="128" height="128">',

				// Available shortcode attributes and default values. Required. Array.
				// Attribute model expects 'attr', 'type' and 'label'
				// Supported field types: text, checkbox, textarea, radio, select, email, url, number, and date.
				'attrs'         => array(
					array(
						'label'       => __( 'Statistic', 'wp_statistics' ),
						'attr'        => 'stat',
						'type'        => 'select',
						'description' => __( 'Select the statistic you wish to display.', 'wp_statistics' ),
						'value'       => 'usersonline',
						'options'     => array(
							'usersonline'    => __( 'Users Online', 'wp_statistiscs' ),
							'visits'         => __( 'Visits', 'wp_statistiscs' ),
							'visitors'       => __( 'Visitors', 'wp_statistiscs' ),
							'pagevisits'     => __( 'Page Visits', 'wp_statistiscs' ),
							'searches'       => __( 'Searches', 'wp_statistiscs' ),
							'postcount'      => __( 'Post Count', 'wp_statistiscs' ),
							'pagecount'      => __( 'Page Count', 'wp_statistiscs' ),
							'commentcount'   => __( 'Comment Count', 'wp_statistiscs' ),
							'spamcount'      => __( 'Spam Count', 'wp_statistiscs' ),
							'usercount'      => __( 'User Count', 'wp_statistiscs' ),
							'postaverage'    => __( 'Post Average', 'wp_statistiscs' ),
							'commentaverage' => __( 'Comment Average', 'wp_statistiscs' ),
							'useraverage'    => __( 'User Average', 'wp_statistiscs' ),
							'lpd'            => __( 'Last Post Date', 'wp_statistiscs' ),
						),
					),
					array(
						'label'       => __( 'Time Frame', 'wp_statistics' ),
						'attr'        => 'time',
						'type'        => 'url',
						'description' => __( 'The time frame to get the statistic for, strtotime() (http://php.net/manual/en/datetime.formats.php) will be used to calculate it. Use "total" to get all recorded dates.', 'wp_statistics' ),
						'meta'        => array( 'size' => '10' ),
					),
					array(
						'label'       => __( 'Search Provider', 'wp_statistics' ),
						'attr'        => 'provider',
						'type'        => 'select',
						'description' => __( 'The search provider to get statistics on.', 'wp_statistics' ),
						'options'     => $se_options,
					),
					array(
						'label'       => __( 'Number Format', 'wp_statistics' ),
						'attr'        => 'format',
						'type'        => 'select',
						'description' => __( 'The format to display numbers in: i18n, english, none.', 'wp_statistics' ),
						'value'       => 'none',
						'options'     => array(
							'none'    => __( 'None', 'wp_statistics' ),
							'english' => __( 'English', 'wp_statistics' ),
							'i18n'    => __( 'International', 'wp_statistics' ),
						),
					),
					array(
						'label'       => __( 'Post/Page ID', 'wp_statistics' ),
						'attr'        => 'id',
						'type'        => 'number',
						'description' => __( 'The post/page id to get page statistics on.', 'wp_statistics' ),
						'meta'        => array( 'size' => '5' ),
					),
				),
			)
		);
	}

}

?>