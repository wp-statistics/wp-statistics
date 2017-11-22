<?php

/**
 * Class WP_Statistics_Frontend
 */
class WP_Statistics_Frontend {

	public function __construct() {
		global $WP_Statistics;

		add_filter('widget_text', 'do_shortcode');

		new \WP_Statistics_Schedule;

		// Add the honey trap code in the footer.
		add_action('wp_footer', 'WP_Statistics_Frontend::add_honeypot');

		// If we've been told to exclude the feeds from the statistics
		// add a detection hook when WordPress generates the RSS feed.
		if ( $WP_Statistics->get_option('exclude_feeds') ) {
			add_filter('the_title_rss', 'WP_Statistics_Frontend::is_feed');
		}

		add_action('wp_footer', 'WP_Statistics_Frontend::add_ajax_script', 1000);

		if (defined('DOING_AJAX') && DOING_AJAX && $_REQUEST['action'] == 'wp_statistics_log_visit') {
			add_action('wp_ajax_wp_statistics_log_visit', 'WP_Statistics_Frontend::log_visit');
			add_action('wp_ajax_nopriv_wp_statistics_log_visit', 'WP_Statistics_Frontend::log_visit');
		}else{
			// We can wait until the very end of the page to process the statistics,
			// that way the page loads and displays quickly.
			add_action('wp', 'WP_Statistics_Frontend::init');
		}

	}

	static function add_ajax_script() {
		$nonce    = wp_create_nonce('wp-statistics-ajax-nonce');
		$ajax_url = admin_url('admin-ajax.php');
		?>
		<script>
			jQuery(document).ready(function ($) {
				var data = {
					'action': 'wp_statistics_log_visit',
					'nonce': '<?php echo $nonce;?>',
				};
				jQuery.post('<?php echo $ajax_url; ?>', data, function (response) {
					alert('Got this from the server: ' + response);
				});
			});
		</script>
		<?php
	}

	static function log_visit() {
		check_ajax_referer('wp-statistics-ajax-nonce', 'nonce');
		WP_Statistics_Frontend::init();
		wp_die();
	}

	/**
	 * Footer Action
	 */
	static function add_honeypot() {
		global $WP_Statistics;
		if ( $WP_Statistics->get_option('use_honeypot') && $WP_Statistics->get_option('honeypot_postid') > 0 ) {
			$post_url = get_permalink($WP_Statistics->get_option('honeypot_postid'));
			echo '<a href="' . $post_url . '" style="display: none;">&nbsp;</a>';
		}
	}

	/**
	 * Shutdown Action
	 */
	static function init() {

		global $WP_Statistics;

		// If something has gone horribly wrong and $WP_Statistics isn't an object, bail out.
		// This seems to happen sometimes with WP Cron calls.
		if ( ! is_object($WP_Statistics) ) {
			return;
		}

		$h = new WP_Statistics_GEO_IP_Hits;

		// Call the online users tracking code.
		if ( $WP_Statistics->get_option('useronline') ) {
			$h->Check_online();
		}

		// Call the visitor tracking code.
		if ( $WP_Statistics->get_option('visitors') ) {
			$h->Visitors();
		}

		// Call the visit tracking code.
		if ( $WP_Statistics->get_option('visits') ) {
			$h->Visits();
		}

		// Call the page tracking code.
		if ( $WP_Statistics->get_option('pages') ) {
			$h->Pages();
		}

		// Check to see if the GeoIP database needs to be downloaded and do so if required.
		if ( $WP_Statistics->get_option('update_geoip') ) {
			WP_Statistics_Updates::download_geoip();
		}

		// Check to see if the browscap database needs to be downloaded and do so if required.
		if ( $WP_Statistics->get_option('update_browscap') ) {
			WP_Statistics_Updates::download_browscap();
		}

		// Check to see if the referrerspam database needs to be downloaded and do so if required.
		if ( $WP_Statistics->get_option('update_referrerspam') ) {
			WP_Statistics_Updates::download_referrerspam();
		}

		if ( $WP_Statistics->get_option('send_upgrade_email') ) {
			$WP_Statistics->update_option('send_upgrade_email', false);

			$blogname  = get_bloginfo('name');
			$blogemail = get_bloginfo('admin_email');

			$headers[] = "From: $blogname <$blogemail>";
			$headers[] = "MIME-Version: 1.0";
			$headers[] = "Content-type: text/html; charset=utf-8";

			if ( $WP_Statistics->get_option('email_list') == '' ) {
				$WP_Statistics->update_option('email_list', $blogemail);
			}

			wp_mail(
					$WP_Statistics->get_option('email_list'),
					sprintf(__('WP Statistics %s installed on', 'wp-statistics'), WP_Statistics::$reg['version']) .
					' ' .
					$blogname,
					"Installation/upgrade complete!",
					$headers
			);
		}
	}

}