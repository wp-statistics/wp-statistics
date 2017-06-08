<?php
global $wp_roles;

$role_list = $wp_roles->get_names();

if ( $wps_nonce_valid ) {

	foreach ( $role_list as $role ) {
		$role_post = 'wps_exclude_' . str_replace( " ", "_", strtolower( $role ) );

		if ( array_key_exists( $role_post, $_POST ) ) {
			$value = $_POST[ $role_post ];
		} else {
			$value = '';
		}

		$new_option = str_replace( "wps_", "", $role_post );
		$WP_Statistics->store_option( $new_option, $value );

	}

	if ( array_key_exists( 'wps_create_honeypot', $_POST ) ) {
		$my_post = array(
			'post_type'    => 'page',
			'post_title'   => __( 'WP Statistics Honey Pot Page', 'wp_statistics' ) . ' [' . $WP_Statistics->Current_Date() . ']',
			'post_content' => __( 'This is the honey pot for WP Statistics to use, do not delete.', 'wp_statistics' ),
			'post_status'  => 'publish',
			'post_author'  => 1,
		);

		$_POST['wps_honeypot_postid'] = wp_insert_post( $my_post );
	}

	$wps_option_list = array_merge( $wps_option_list, array(
		'wps_record_exclusions',
		'wps_robotlist',
		'wps_exclude_ip',
		'wps_exclude_loginpage',
		'wps_exclude_adminpage',
		'wps_force_robot_update',
		'wps_excluded_countries',
		'wps_included_countries',
		'wps_excluded_hosts',
		'wps_robot_threshold',
		'wps_use_honeypot',
		'wps_honeypot_postid',
		'wps_exclude_feeds',
		'wps_excluded_urls',
		'wps_exclude_404s',
		'wps_corrupt_browser_info',
		'wps_exclude_ajax'
	) );

	foreach ( $wps_option_list as $option ) {
		$new_option = str_replace( "wps_", "", $option );

		if ( array_key_exists( $option, $_POST ) ) {
			$value = $_POST[ $option ];
		} else {
			$value = '';
		}
		$WP_Statistics->store_option( $new_option, $value );
	}
}

?>

    <table class="form-table">
        <tbody>

        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e( 'Exclusions', 'wp_statistics' ); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row"><label for="wps-exclusions"><?php _e( 'Record exclusions', 'wp_statistics' ); ?></label>:
            </th>
            <td>
                <input id="wps-exclusions" type="checkbox" value="1" name="wps_record_exclusions" <?php echo $WP_Statistics->get_option( 'record_exclusions' ) == true ? "checked='checked'" : ''; ?>><label for="wps-exclusions"><?php _e( 'Enable', 'wp_statistics' ); ?></label>
                <p class="description"><?php _e( 'This will record all the excluded hits in a separate table with the reasons why it was excluded but no other information.  This will generate a lot of data but is useful if you want to see the total number of hits your site gets, not just actual user visits.', 'wp_statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e( 'Exclude User Roles', 'wp_statistics' ); ?></h3></th>
        </tr>
		<?php
		$role_option_list = '';

		foreach ( $role_list as $role ) {
			$store_name       = 'exclude_' . str_replace( " ", "_", strtolower( $role ) );
			$option_name      = 'wps_' . $store_name;
			$role_option_list .= $option_name . ',';

			$translated_role_name = translate_user_role( $role );
			?>

            <tr valign="top">
                <th scope="row"><label for="<?php echo $option_name; ?>"><?php echo $translated_role_name; ?>:</label>
                </th>
                <td>
                    <input id="<?php echo $option_name; ?>" type="checkbox" value="1" name="<?php echo $option_name; ?>" <?php echo $WP_Statistics->get_option( $store_name ) == true ? "checked='checked'" : ''; ?>><label for="<?php echo $option_name; ?>"><?php _e( 'Exclude', 'wp_statistics' ); ?></label>
                    <p class="description"><?php echo sprintf( __( 'Exclude %s role from data collection.', 'wp_statistics' ), $translated_role_name ); ?></p>
                </td>
            </tr>
		<?php } ?>

        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e( 'IP/Robot Exclusions', 'wp_statistics' ); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row"><?php _e( 'Robot list', 'wp_statistics' ); ?>:</th>
            <td>
				<textarea name="wps_robotlist" class="code" dir="ltr" rows="10" cols="60" id="wps_robotlist"><?php
					$robotlist = $WP_Statistics->get_option( 'robotlist' );

					include_once( dirname( __FILE__ ) . '/../../../robotslist.php' );

					if ( $robotlist == '' ) {
						$robotlist = implode( "\n", $wps_robotarray );
						update_option( 'wps_robotlist', $robotlist );
					}

					echo htmlentities( $robotlist, ENT_QUOTES ); ?></textarea>
                <p class="description"><?php echo __( 'A list of words (one per line) to match against to detect robots.  Entries must be at least 4 characters long or they will be ignored.', 'wp_statistics' ); ?></p>
                <a onclick="var wps_robotlist = getElementById('wps_robotlist'); wps_robotlist.value = '<?php echo implode( '\n', $wps_robotarray ); ?>';" class="button"><?php _e( 'Reset to Default', 'wp_statistics' ); ?></a>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="force_robot_update"><?php _e( 'Force robot list update after upgrades', 'wp_statistics' ); ?>
                    :</label></th>
            <td>
                <input id="force_robot_update" type="checkbox" value="1" name="wps_force_robot_update" <?php echo $WP_Statistics->get_option( 'force_robot_update' ) == true ? "checked='checked'" : ''; ?>><label for="force_robot_update"><?php _e( 'Enable', 'wp_statistics' ); ?></label>
                <p class="description"><?php echo sprintf( __( 'Force the robot list to be reset to the default after an update to WP Statistics takes place.  Note if this option is enabled any custom robots you have added to the list will be lost.', 'wp_statistics' ), $role ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row"><label for="wps_robot_threshold"><?php _e( 'Robot visit threshold', 'wp_statistics' ); ?>
                    :</label></th>
            <td>
                <input id="wps_robot_threshold" type="text" size="5" name="wps_robot_threshold" value="<?php echo $WP_Statistics->get_option( 'robot_threshold' ); ?>">
                <p class="description"><?php echo __( 'Treat visitors with more than this number of visits per day as robots.  0 = disabled.', 'wp_statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php _e( 'Excluded IP address list', 'wp_statistics' ); ?>:</th>
            <td>
                <textarea id="wps_exclude_ip" name="wps_exclude_ip" rows="5" cols="60" class="code" dir="ltr"><?php echo htmlentities( $WP_Statistics->get_option( 'exclude_ip' ), ENT_QUOTES ); ?></textarea>
                <p class="description"><?php echo __( 'A list of IP addresses and subnet masks (one per line) to exclude from statistics collection.', 'wp_statistics' ); ?></p>
                <p class="description"><?php echo __( 'For IPv4 addresses, both 192.168.0.0/24 and 192.168.0.0/255.255.255.0 formats are accepted.  To specify an IP address only, use a subnet value of 32 or 255.255.255.255.', 'wp_statistics' ); ?></p>
                <p class="description"><?php echo __( 'For IPv6 addresses use the fc00::/7 format.', 'wp_statistics' ); ?></p>
                <a onclick="var wps_exclude_ip = getElementById('wps_exclude_ip'); if( wps_exclude_ip != null ) { wps_exclude_ip.value = jQuery.trim( wps_exclude_ip.value + '\n10.0.0.0/8' ); }" class="button"><?php _e( 'Add 10.0.0.0', 'wp_statistics' ); ?></a>
                <a onclick="var wps_exclude_ip = getElementById('wps_exclude_ip'); if( wps_exclude_ip != null ) { wps_exclude_ip.value = jQuery.trim( wps_exclude_ip.value + '\n172.16.0.0/12' ); }" class="button"><?php _e( 'Add 172.16.0.0', 'wp_statistics' ); ?></a>
                <a onclick="var wps_exclude_ip = getElementById('wps_exclude_ip'); if( wps_exclude_ip != null ) { wps_exclude_ip.value = jQuery.trim( wps_exclude_ip.value + '\n192.168.0.0/16' ); }" class="button"><?php _e( 'Add 192.168.0.0', 'wp_statistics' ); ?></a>
                <a onclick="var wps_exclude_ip = getElementById('wps_exclude_ip'); if( wps_exclude_ip != null ) { wps_exclude_ip.value = jQuery.trim( wps_exclude_ip.value + '\nfc00::/7' ); }" class="button"><?php _e( 'Add fc00::/7', 'wp_statistics' ); ?></a>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php _e( 'Use honey pot', 'wp_statistics' ); ?>:</th>
            <td>
                <input id="use_honeypot" type="checkbox" value="1" name="wps_use_honeypot" <?php echo $WP_Statistics->get_option( 'use_honeypot' ) == true ? "checked='checked'" : ''; ?>><label for="wps_use_honeypot"><?php _e( 'Enable', 'wp_statistics' ); ?></label>
                <p class="description"><?php echo __( 'Use a honey pot page to identify robots.', 'wp_statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row"><label for="honeypot_postid"><?php _e( 'Honey pot post id', 'wp_statistics' ); ?>:</label>
            </th>
            <td>
                <input id="honeypot_postid" type="text" value="<?php echo htmlentities( $WP_Statistics->get_option( 'honeypot_postid' ), ENT_QUOTES ); ?>" size="5" name="wps_honeypot_postid">
                <p class="description"><?php echo __( 'The post id to use for the honeypot page.', 'wp_statistics' ); ?></p>
                <input id="wps_create_honeypot" type="checkbox" value="1" name="wps_create_honeypot"><label for="wps_create_honeypot"><?php _e( 'Create a new honey pot page', 'wp_statistics' ); ?></label>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="corrupt_browser_info"><?php _e( 'Treat corrupt browser info as a bot', 'wp_statistics' ); ?>
                    :</label></th>
            <td>
                <input id="corrupt_browser_info" type="checkbox" value="1" name="wps_corrupt_browser_info" <?php echo $WP_Statistics->get_option( 'corrupt_browser_info' ) == true ? "checked='checked'" : ''; ?>><label for="wps_corrupt_browser_info"><?php _e( 'Enable', 'wp_statistics' ); ?></label>
                <p class="description"><?php echo __( 'Treat any visitor with corrupt browser info (missing IP address or empty user agent string) as a robot.', 'wp_statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e( 'GeoIP Exclusions', 'wp_statistics' ); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row"><?php _e( 'Excluded countries list', 'wp_statistics' ); ?>:</th>
            <td>
                <textarea id="wps_excluded_countries" name="wps_excluded_countries" rows="5" cols="10" class="code" dir="ltr"><?php echo htmlentities( $WP_Statistics->get_option( 'excluded_countries' ), ENT_QUOTES ); ?></textarea>
                <p class="description"><?php echo __( 'A list of country codes (one per line, two letters each) to exclude from statistics collection.  Use "000" (three zeros) to exclude unknown countries.', 'wp_statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php _e( 'Included countries list', 'wp_statistics' ); ?>:</th>
            <td>
                <textarea id="wps_included_countries" name="wps_included_countries" rows="5" cols="10" class="code" dir="ltr"><?php echo htmlentities( $WP_Statistics->get_option( 'included_countries' ), ENT_QUOTES ); ?></textarea>
                <p class="description"><?php echo __( 'A list of country codes (one per line, two letters each) to include in statistics collection, if this list is not empty, only visitors from the included countries will be recorded.  Use "000" (three zeros) to exclude unknown countries.', 'wp_statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e( 'Host Exclusions', 'wp_statistics' ); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row"><?php _e( 'Excluded hosts list', 'wp_statistics' ); ?>:</th>
            <td>
                <textarea id="wps_excluded_hosts" name="wps_excluded_hosts" rows="5" cols="80" class="code" dir="ltr"><?php echo htmlentities( $WP_Statistics->get_option( 'excluded_hosts' ), ENT_QUOTES ); ?></textarea>
                <p class="description"><?php echo __( 'A list of fully qualified host names (ie. server.example.com, one per line) to exclude from statistics collection.', 'wp_statistics' ); ?></p>
                <br>
                <p class="description"><?php echo __( 'Note: this option will NOT perform a reverse DNS lookup on each page load but instead cache the IP address for the provided hostnames for one hour.  If you are excluding dynamically assigned hosts you may find some degree of overlap when the host changes it\'s IP address and when the cache is updated resulting in some hits recorded.', 'wp_statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e( 'Site URL Exclusions', 'wp_statistics' ); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row"><?php _e( 'Excluded login page', 'wp_statistics' ); ?>:</th>
            <td>
                <input id="wps-exclude-loginpage" type="checkbox" value="1" name="wps_exclude_loginpage" <?php echo $WP_Statistics->get_option( 'exclude_loginpage' ) == true ? "checked='checked'" : ''; ?>><label for="wps-exclude-loginpage"><?php _e( 'Exclude', 'wp_statistics' ); ?></label>
                <p class="description"><?php _e( 'Exclude the login page for registering as a hit.', 'wp_statistics' ); ?></p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e( 'Excluded admin pages', 'wp_statistics' ); ?>:</th>
            <td>
                <input id="wps-exclude-adminpage" type="checkbox" value="1" name="wps_exclude_adminpage" <?php echo $WP_Statistics->get_option( 'exclude_adminpage' ) == true ? "checked='checked'" : ''; ?>><label for="wps-exclude-adminpage"><?php _e( 'Exclude', 'wp_statistics' ); ?></label>
                <p class="description"><?php _e( 'Exclude the admin pages for registering as a hit.', 'wp_statistics' ); ?></p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e( 'Excluded RSS feeds', 'wp_statistics' ); ?>:</th>
            <td>
                <input id="wps-exclude-feeds" type="checkbox" value="1" name="wps_exclude_feeds" <?php echo $WP_Statistics->get_option( 'exclude_feeds' ) == true ? "checked='checked'" : ''; ?>><label for="wps-exclude-feeds"><?php _e( 'Exclude', 'wp_statistics' ); ?></label>
                <p class="description"><?php _e( 'Exclude the RSS feeds for registering as a hit.', 'wp_statistics' ); ?></p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e( 'Excluded 404 pages', 'wp_statistics' ); ?>:</th>
            <td>
                <input id="wps-exclude-404s" type="checkbox" value="1" name="wps_exclude_404s" <?php echo $WP_Statistics->get_option( 'exclude_404s' ) == true ? "checked='checked'" : ''; ?>><label for="wps-exclude-404s"><?php _e( 'Exclude', 'wp_statistics' ); ?></label>
                <p class="description"><?php _e( 'Exclude any URL that returns a "404 - Not Found" message.', 'wp_statistics' ); ?></p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e( 'Excluded AJAX calls', 'wp_statistics' ); ?>:</th>
            <td>
                <input id="wps-exclude-ajax" type="checkbox" value="1" name="wps_exclude_ajax" <?php echo $WP_Statistics->get_option( 'exclude_ajax' ) == true ? "checked='checked'" : ''; ?>><label for="wps-exclude-ajax"><?php _e( 'Exclude', 'wp_statistics' ); ?></label>
                <p class="description"><?php _e( 'Exclude any call to the WordPress AJAX system.', 'wp_statistics' ); ?></p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e( 'Excluded URLs list', 'wp_statistics' ); ?>:</th>
            <td>
                <textarea id="wps_excluded_urls" name="wps_excluded_urls" rows="5" cols="80" class="code" dir="ltr"><?php echo htmlentities( $WP_Statistics->get_option( 'excluded_urls' ), ENT_QUOTES ); ?></textarea>
                <p class="description"><?php echo __( 'A list of local urls (ie. /wordpress/about, one per line) to exclude from statistics collection.', 'wp_statistics' ); ?></p>
                <br>
                <p class="description"><?php echo __( 'Note: this option will NOT handle url parameters (anything after the ?), only to the script name.  Entries less than two characters will be ignored.', 'wp_statistics' ); ?></p>
            </td>
        </tr>

        </tbody>
    </table>

<?php submit_button( __( 'Update', 'wp_statistics' ), 'primary', 'submit' ); ?>