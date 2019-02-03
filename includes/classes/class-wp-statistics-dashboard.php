<?php

/**
 * Class WP_Statistics_Dashboard
 */
class WP_Statistics_Dashboard {

	/**
	 * User Meta Set Dashboard Option name
	 * @var string
	 */
	public static $dashboard_set = 'dashboard_set';

	/**
	 * WP_Statistics_Dashboard constructor.
	 */
	public function __construct() {

		//Register Dashboard Widget
		add_action( 'wp_dashboard_setup', array( $this, 'load_dashboard_widget' ) );

		//Add Inline Script in Admin Footer
		add_action( 'admin_footer', array( $this, 'inline_javascript' ) );

	}

	/**
	 * Widget Setup Key
	 *
	 * @param $key
	 * @return string
	 */
	public static function widget_setup_key( $key ) {
		return 'wp-statistics-' . $key . '-widget';
	}

	/**
	 * Get Widget List
	 *
	 * @param bool $widget
	 * @return array|mixed
	 */
	public static function widget_list( $widget = false ) {

		/**
		 * List of WP-Statistics Widget
		 *
		 * --- Array Arg -----
		 * page_url : link of Widget Page @see WP_Statistics::$page
		 * name     : Name Of Widget Box
		 * require  : the Condition From Wp-statistics Option if == true
		 * hidden   : if set true , Default Hidden Dashboard in Wordpress Admin
		 *
		 */
		$list = array(
			'quickstats'       => array(
				'page_url' => 'overview',
				'name'     => __( 'Quick Stats', 'wp-statistics' )
			),
			'summary'          => array(
				'name'   => __( 'Summary', 'wp-statistics' ),
				'hidden' => true
			),
			'browsers'         => array(
				'page_url' => 'browser',
				'name'     => __( 'Top 10 Browsers', 'wp-statistics' ),
				'require'  => array( 'visitors' ),
				'hidden'   => true
			),
			'countries'        => array(
				'page_url' => 'countries',
				'name'     => __( 'Top 10 Countries', 'wp-statistics' ),
				'require'  => array( 'geoip', 'visitors' ),
				'hidden'   => true
			),
			'hits'             => array(
				'page_url' => 'hits',
				'name'     => __( 'Hit Statistics', 'wp-statistics' ),
				'require'  => array( 'visits' ),
				'hidden'   => true
			),
			'pages'            => array(
				'page_url' => 'pages',
				'name'     => __( 'Top 10 Pages', 'wp-statistics' ),
				'require'  => array( 'pages' ),
				'hidden'   => true
			),
			'referring'        => array(
				'page_url' => 'referrers',
				'name'     => __( 'Top Referring Sites', 'wp-statistics' ),
				'require'  => array( 'visitors' ),
				'hidden'   => true
			),
			'search'           => array(
				'page_url' => 'searches',
				'name'     => __( 'Search Engine Referrals', 'wp-statistics' ),
				'require'  => array( 'visitors' ),
				'hidden'   => true
			),
			'words'            => array(
				'page_url' => 'words',
				'name'     => __( 'Latest Search Words', 'wp-statistics' ),
				'require'  => array( 'visitors' ),
				'hidden'   => true
			),
			'top-visitors'     => array(
				'page_url' => 'top-visitors',
				'name'     => __( 'Top 10 Visitors Today', 'wp-statistics' ),
				'require'  => array( 'visitors' ),
				'hidden'   => true
			),
			'recent'           => array(
				'page_url' => 'visitors',
				'name'     => __( 'Recent Visitors', 'wp-statistics' ),
				'require'  => array( 'visitors' ),
				'hidden'   => true
			),
			'hitsmap'          => array(
				'name'    => __( 'Today\'s Visitors Map', 'wp-statistics' ),
				'require' => array( 'visitors' ),
				'hidden'  => true
			)
		);

		//Print List of Dashboard
		if ( $widget === false ) {
			return $list;
		} else {
			if ( array_key_exists( $widget, $list ) ) {
				return $list[ $widget ];
			}
		}

		return array();
	}

	/**
	 * This function Register Wp-statistics Dashboard to wordpress Admin
	 */
	public static function register_dashboard_widget() {

		//Check Dashboard Widget
		if ( ! function_exists( 'wp_add_dashboard_widget' ) ) {
			return;
		}

		//Get List Of Wp-statistics Dashboard Widget
		$list = self::widget_list();
		foreach ( $list as $widget_key => $dashboard ) {

			//Register Dashboard Widget
			if ( wp_statistics_check_option_require( $dashboard ) === true ) {
				wp_add_dashboard_widget( self::widget_setup_key( $widget_key ), $dashboard['name'], 'WP_Statistics_Dashboard::generate_postbox_contents', $control_callback = null, array( 'widget' => $widget_key ) );
			}

		}
	}

	/**
	 * Load Dashboard Widget
	 * This Function add_action to `wp_dashboard_setup`
	 */
	static function load_dashboard_widget() {
		global $WP_Statistics;

		//Load User Options
		$WP_Statistics->load_user_options();

		// If the user does not have at least read access to the status plugin, just return without adding the widgets.
		if ( ! current_user_can( wp_statistics_validate_capability( $WP_Statistics->get_option( 'read_capability', 'manage_option' ) ) ) ) {
			return;
		}

		//Check Hidden User Dashboard Option
		$user_dashboard = $WP_Statistics->get_user_option( self::$dashboard_set );
		if ( $user_dashboard === false || $user_dashboard != WP_Statistics::$reg['version'] ) {
			self::set_user_hidden_dashboard_option();
		}

		// If the admin has disabled the widgets, don't display them.
		if ( ! $WP_Statistics->get_option( 'disable_dashboard' ) ) {
			self::register_dashboard_widget();
		}

	}

	/**
	 * Set Default Hidden Dashboard User Option
	 */
	public static function set_user_hidden_dashboard_option() {
		global $WP_Statistics;

		//Get List Of Wp-statistics Dashboard Widget
		$dashboard_list = self::widget_list();
		$hidden_opt     = 'metaboxhidden_dashboard';

		//Create Empty Option and save in User meta
		$WP_Statistics->update_user_option( self::$dashboard_set, WP_Statistics::$reg['version'] );

		//Get Dashboard Option User Meta
		$hidden_widgets = get_user_meta( $WP_Statistics->user_id, $hidden_opt, true );
		if ( ! is_array( $hidden_widgets ) ) {
			$hidden_widgets = array();
		}

		//Set Default Hidden Dashboard in Admin Wordpress
		foreach ( $dashboard_list as $widget => $dashboard ) {
			if ( array_key_exists( 'hidden', $dashboard ) ) {
				$hidden_widgets[] = self::widget_setup_key( $widget );
			}
		}

		update_user_meta( $WP_Statistics->user_id, $hidden_opt, $hidden_widgets );
	}

	/**
	 * Load Widget Script/style
	 */
	static function load_widget_css_and_scripts() {
		global $WP_Statistics;

		// Load the css we use for the statistics pages.
		wp_enqueue_style( 'wpstatistics-log-css', WP_Statistics::$reg['plugin-url'] . 'assets/css/log.css', true, '1.2' );

		// Load the map code.
		if ( ! $WP_Statistics->get_option( 'disable_dashboard' ) ) {
			wp_enqueue_style( 'jqvmap-css', WP_Statistics::$reg['plugin-url'] . 'assets/jqvmap/jqvmap.css', true, '1.5.1' );
			wp_enqueue_script( 'jquery-vmap', WP_Statistics::$reg['plugin-url'] . 'assets/jqvmap/jquery.vmap.js', true, '1.5.1' );
			wp_enqueue_script( 'jquery-vmap-world', WP_Statistics::$reg['plugin-url'] . 'assets/jqvmap/maps/jquery.vmap.world.js', true, '1.5.1' );
		}

		// Load chart library
		if ( ! isset( $_GET['post'] ) ) {
			wp_enqueue_script( 'wp-statistics-chart-js', WP_Statistics::$reg['plugin-url'] . 'assets/js/Chart.bundle.min.js', false, '2.7.3', false );
		}

		//Get current screen page
		$screen = get_current_screen();

		// Load our custom widgets handling javascript.
		if ( 'post' == $screen->id || 'page' == $screen->id ) {
			wp_enqueue_script( 'wp_statistics_editor', WP_Statistics::$reg['plugin-url'] . 'assets/js/editor.js' );
		} else {
			wp_enqueue_script( 'wp_statistics_dashboard', WP_Statistics::$reg['plugin-url'] . 'assets/js/dashboard.js' );
		}
	}

	/**
	 * Add inline Script
	 * For Add button Refresh/Direct Button Link in Top of Meta Box
	 */
	static function inline_javascript() {

		//if not Dashboard Page
		$screen = get_current_screen();
		if ( 'dashboard' != $screen->id ) {
			return;
		}

		//Load Of Require Jquery Library Function
		WP_Statistics_Dashboard::load_widget_css_and_scripts();

		//Prepare List Of Dashboard
		$page_urls  = array();
		$dashboards = self::widget_list();
		foreach ( $dashboards as $widget_key => $dashboard ) {
			if ( array_key_exists( 'page_url', $dashboard ) ) {
				$page_urls[ 'wp-statistics-' . $widget_key . '-widget_more_button' ] = WP_Statistics_Admin_Pages::admin_url( $dashboard['page_url'] );
			}
		}

		//Add Extra Pages For Overview Page
		foreach ( array( 'exclusions' => 'exclusions', 'users_online' => 'online' ) as $p_key => $p_link ) {
			$page_urls[ 'wp-statistics-' . $p_key . '-widget_more_button' ] = WP_Statistics_Admin_Pages::admin_url( $p_link );
		}

		?>
        <script type="text/javascript">
            var wp_statistics_destinations = <?php echo json_encode( $page_urls ); ?>;
            var wp_statistics_loading_image = '<?php echo WP_Statistics_Admin_Pages::loading_meta_box(); ?>';

            function wp_statistics_wait_for_postboxes() {

                if (!jQuery('#show-settings-link').is(':visible')) {
                    setTimeout(wp_statistics_wait_for_postboxes, 500);
                }

                jQuery('.wps-refresh').unbind('click').on('click', wp_statistics_refresh_widget);
                jQuery('.wps-more').unbind('click').on('click', wp_statistics_goto_more);

                jQuery('.hide-postbox-tog').on('click', wp_statistics_refresh_on_toggle_widget);
            }

            jQuery(document).ready(function () {

                // Add the "more" and "refresh" buttons.
                jQuery('.postbox').each(function () {
                    var temp = jQuery(this);
                    var temp_id = temp.attr('id');

                    if (temp_id.substr(0, 14) != 'wp-statistics-') {
                        return;
                    }

                    var temp_html = temp.html();
                    if (temp_id == '<?php echo self::widget_setup_key( 'summary' ); ?>') {
                        new_text = '<?php echo WP_Statistics_Admin_Pages::meta_box_button( 'refresh' );?>';
                        new_text = new_text.replace('{{refreshid}}', temp_id + '_refresh_button');
                        temp_html = temp_html.replace('</button>', new_text);
                    } else {
                        new_text = '<?php echo WP_Statistics_Admin_Pages::meta_box_button();?>';
                        new_text = new_text.replace('{{refreshid}}', temp_id + '_refresh_button');
                        new_text = new_text.replace('{{moreid}}', temp_id + '_more_button');
                        temp_html = temp_html.replace('</button>', new_text);
                    }
                    temp.html(temp_html);
                });

                // We have use a timeout here because we don't now what order this code will run in comparison to the postbox code.
                // Any timeout value should work as the timeout won't run until the rest of the javascript as run through once.
                setTimeout(wp_statistics_wait_for_postboxes, 100);
            });
        </script>
		<?php
	}

	/**
	 * Generate widget Post Box
	 *
	 * @param $post
	 * @param $args
	 */
	static function generate_postbox_contents( $post, $args ) {
		$widget       = $args['args']['widget'];
		$container_id = 'wp-statistics-' . str_replace( '.', '-', $widget ) . '-div';

		echo '<div id="' . $container_id . '">' . WP_Statistics_Admin_Pages::loading_meta_box() . '</div>';
		wp_statistics_generate_widget_load_javascript( $widget, $container_id );
	}

}
