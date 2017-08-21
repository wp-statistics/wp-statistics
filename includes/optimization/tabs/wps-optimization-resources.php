<?php
/* format size of file 
* @author Mike Zriel
* @date 7 March 2011
* @website www.zriel.com
*/
function formatSize( $size ) {
	$sizes = array( " Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB" );
	if ( $size == 0 ) {
		return ( 'n/a' );
	} else {
		return ( round( $size / pow( 1024, ( $i = floor( log( $size, 1024 ) ) ) ), 2 ) . $sizes[ $i ] );
	}
}

?>
<div class="wrap">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e( 'Resources', 'wp-statistics' ); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php _e( 'Memory usage in PHP', 'wp-statistics' ); ?>:
            </th>

            <td>
                <strong><?php echo number_format_i18n( memory_get_usage() ); ?></strong> <?php _e( 'Bytes', 'wp-statistics' ); ?>
                <p class="description"><?php _e( 'Memory usage in PHP', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php _e( 'PHP Memory Limit', 'wp-statistics' ); ?>:
            </th>

            <td>
                <strong><?php echo ini_get( 'memory_limit' ); ?></strong>
                <p class="description"><?php _e( 'The memory limit a script is allowed to consume, set in php.ini.', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php echo sprintf( __( 'Number of rows in the %s table', 'wp-statistics' ), '<code>' . $wpdb->prefix . 'statistics_' . 'useronline' . '</code>' ); ?>
                :
            </th>

            <td>
                <strong><?php echo number_format_i18n( $result['useronline'] ); ?></strong> <?php echo _n( 'Row', 'Rows', number_format_i18n( $result['useronline'] ), 'wp-statistics' ); ?>
                <p class="description"><?php _e( 'Number of rows', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php echo sprintf( __( 'Number of rows in the %s table', 'wp-statistics' ), '<code>' . $wpdb->prefix . 'statistics_' . 'visit' . '</code>' ); ?>
                :
            </th>

            <td>
                <strong><?php echo number_format_i18n( $result['visit'] ); ?></strong> <?php echo _n( 'Row', 'Rows', number_format_i18n( $result['visit'] ), 'wp-statistics' ); ?>
                <p class="description"><?php _e( 'Number of rows', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php echo sprintf( __( 'Number of rows in the %s table', 'wp-statistics' ), '<code>' . $wpdb->prefix . 'statistics_' . 'visitor' . '</code>' ); ?>
                :
            </th>

            <td>
                <strong><?php echo number_format_i18n( $result['visitor'] ); ?></strong> <?php echo _n( 'Row', 'Rows', number_format_i18n( $result['visitor'] ), 'wp-statistics' ); ?>
                <p class="description"><?php _e( 'Number of rows', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php echo sprintf( __( 'Number of rows in the %s table', 'wp-statistics' ), '<code>' . $wpdb->prefix . 'statistics_' . 'exclusions' . '</code>' ); ?>
                :
            </th>

            <td>
                <strong><?php echo number_format_i18n( $result['exclusions'] ); ?></strong> <?php echo _n( 'Row', 'Rows', number_format_i18n( $result['exclusions'] ), 'wp-statistics' ); ?>
                <p class="description"><?php _e( 'Number of rows', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php echo sprintf( __( 'Number of rows in the %s table', 'wp-statistics' ), '<code>' . $wpdb->prefix . 'statistics_' . 'pages' . '</code>' ); ?>
                :
            </th>

            <td>
                <strong><?php echo number_format_i18n( $result['pages'] ); ?></strong> <?php echo _n( 'Row', 'Rows', number_format_i18n( $result['pages'] ), 'wp-statistics' ); ?>
                <p class="description"><?php _e( 'Number of rows', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php echo sprintf( __( 'Number of rows in the %s table', 'wp-statistics' ), '<code>' . $wpdb->prefix . 'statistics_' . 'historical' . '</code>' ); ?>
                :
            </th>

            <td>
                <strong><?php echo number_format_i18n( $result['historical'] ); ?></strong> <?php echo _n( 'Row', 'Rows', number_format_i18n( $result['historical'] ), 'wp-statistics' ); ?>
                <p class="description"><?php _e( 'Number of rows', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php echo sprintf( __( 'Number of rows in the %s table', 'wp-statistics' ), '<code>' . $wpdb->prefix . 'statistics_' . 'search' . '</code>' ); ?>
                :
            </th>

            <td>
                <strong><?php echo number_format_i18n( $result['search'] ); ?></strong> <?php echo _n( 'Row', 'Rows', number_format_i18n( $result['search'] ), 'wp-statistics' ); ?>
                <p class="description"><?php _e( 'Number of rows', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e( 'Version Info', 'wp-statistics' ); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php _e( 'WP Statistics Version', 'wp-statistics' ); ?>:
            </th>

            <td>
                <strong><?php echo WP_STATISTICS_VERSION; ?></strong>
                <p class="description"><?php _e( 'The WP Statistics version you are running.', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php _e( 'PHP Version', 'wp-statistics' ); ?>:
            </th>

            <td>
                <strong><?php echo phpversion(); ?></strong>
                <p class="description"><?php _e( 'The PHP version you are running.', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php _e( 'PHP Safe Mode', 'wp-statistics' ); ?>:
            </th>

            <td>
                <strong><?php if ( ini_get( 'safe_mode' ) ) {
						_e( 'Yes', 'wp-statistics' );
					} else {
						_e( 'No', 'wp-statistics' );
					} ?></strong>
                <p class="description"><?php _e( 'Is PHP Safe Mode active.  The GeoIP code is not supported in Safe Mode.', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php _e( 'PHP IPv6 Enabled', 'wp-statistics' ); ?>:
            </th>

            <td>
                <strong><?php if ( defined( 'AF_INET6' ) ) {
						_e( 'Yes', 'wp-statistics' );
					} else {
						_e( 'No', 'wp-statistics' );
					} ?></strong>
                <p class="description"><?php _e( 'Is PHP compiled with IPv6 support.  You may see warning messages in your PHP log if it is not and you receive HTTP headers with IPv6 addresses in them.', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php _e( 'jQuery Version', 'wp-statistics' ); ?>:
            </th>

            <td>
                <strong>
                    <script type="text/javascript">document.write(jQuery().jquery);</script>
                </strong>
                <p class="description"><?php _e( 'The jQuery version you are running.', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php _e( 'cURL Version', 'wp-statistics' ); ?>:
            </th>

            <td>
                <strong><?php if ( function_exists( 'curl_version' ) ) {
						$curl_ver = curl_version();
						echo $curl_ver['version'];
					} else {
						_e( 'cURL not installed', 'wp-statistics' );
					} ?></strong>
                <p class="description"><?php _e( 'The PHP cURL Extension version you are running.  cURL is required for the GeoIP code, if it is not installed GeoIP will be disabled.', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php _e( 'Zlib gzopen()', 'wp-statistics' ); ?>:
            </th>

            <td>
                <strong><?php if ( function_exists( 'gzopen' ) ) {
						_e( 'Installed', 'wp-statistics' );
					} else {
						_e( 'Not installed', 'wp-statistics' );
					} ?></strong>
                <p class="description"><?php _e( 'If the gzopen() function is installed.  gzopen() is required for the GeoIP database to be downloaded successfully.', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php _e( 'GMP PHP extension', 'wp-statistics' ); ?>:
            </th>

            <td>
                <strong><?php if ( extension_loaded( 'gmp' ) ) {
						_e( 'Installed', 'wp-statistics' );
					} else {
						_e( 'Not installed', 'wp-statistics' );
					} ?></strong>
                <p class="description"><?php _e( 'If the GMP Math PHP extension is loaded, either GMP or BCMath is required for the GeoIP database to be read successfully.', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php _e( 'BCMath PHP extension', 'wp-statistics' ); ?>:
            </th>

            <td>
                <strong><?php if ( extension_loaded( 'bcmath' ) ) {
						_e( 'Installed', 'wp-statistics' );
					} else {
						_e( 'Not installed', 'wp-statistics' );
					} ?></strong>
                <p class="description"><?php _e( 'If the BCMath PHP extension is loaded, either GMP or BCMath is required for the GeoIP database to be read successfully.', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e( 'File Info', 'wp-statistics' ); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php _e( 'GeoIP Database', 'wp-statistics' ); ?>:
            </th>

            <td>
                <strong><?php $upload_dir = wp_upload_dir();
					$GeoIP_filename       = $upload_dir['basedir'] . '/wp-statistics/GeoLite2-Country.mmdb';
					$GeoIP_filedate       = @filemtime( $GeoIP_filename );

					if ( $GeoIP_filedate === false ) {
						_e( 'Database file does not exist.', 'wp-statistics' );
					} else {
						echo formatSize( @filesize( $GeoIP_filename ) ) . __( ', created on ', 'wp-statistics' ) . date_i18n( get_option( 'date_format' ) . ' @ ' . get_option( 'time_format' ), $GeoIP_filedate );
					} ?></strong>
                <p class="description"><?php _e( 'The file size and date of the GeoIP database.', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php _e( 'browscap.ini File', 'wp-statistics' ); ?>:
            </th>

            <td>
                <strong><?php
					$browscap_filename = $upload_dir['basedir'] . '/wp-statistics/browscap.ini';
					$browscap_filedate = @filemtime( $browscap_filename );

					if ( $browscap_filedate === false ) {
						_e( 'browscap.ini file does not exist.', 'wp-statistics' );
					} else {
						echo formatSize( @filesize( $browscap_filename ) ) . __( ', created on ', 'wp-statistics' ) . date_i18n( get_option( 'date_format' ) . ' @ ' . get_option( 'time_format' ), $browscap_filedate );
					} ?></strong>
                <p class="description"><?php _e( 'The file size and date of the browscap.ini file.', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php _e( 'browscap Cache File', 'wp-statistics' ); ?>:
            </th>

            <td>
                <strong><?php
					$browscap_filename = $upload_dir['basedir'] . '/wp-statistics/cache.php';
					$browscap_filedate = @filemtime( $browscap_filename );

					if ( $browscap_filedate === false ) {
						_e( 'browscap cache file does not exist.', 'wp-statistics' );
					} else {
						echo formatSize( @filesize( $browscap_filename ) ) . __( ', created on ', 'wp-statistics' ) . date_i18n( get_option( 'date_format' ) . ' @ ' . get_option( 'time_format' ), $browscap_filedate );
					} ?></strong>
                <p class="description"><?php _e( 'The file size and date of the browscap cache file.', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e( 'Client Info', 'wp-statistics' ); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php _e( 'Client IP', 'wp-statistics' ); ?>:
            </th>

            <td>
                <strong><?php echo $WP_Statistics->get_IP(); ?></strong>
                <p class="description"><?php _e( 'The client IP address.', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php _e( 'User Agent', 'wp-statistics' ); ?>:
            </th>

            <td>
                <strong><?php echo htmlentities( $_SERVER['HTTP_USER_AGENT'], ENT_QUOTES ); ?></strong>
                <p class="description"><?php _e( 'The client user agent string.', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php _e( 'Browser', 'wp-statistics' ); ?>:
            </th>

            <td>
                <strong><?php $agent = $WP_Statistics->get_UserAgent();
					echo $agent['browser'];
					?></strong>
                <p class="description"><?php _e( 'The detected client browser.', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php _e( 'Version', 'wp-statistics' ); ?>:
            </th>

            <td>
                <strong><?php echo $agent['version']; ?></strong>
                <p class="description"><?php _e( 'The detected client browser version.', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
				<?php _e( 'Platform', 'wp-statistics' ); ?>:
            </th>

            <td>
                <strong><?php echo $agent['platform']; ?></strong>
                <p class="description"><?php _e( 'The detected client platform.', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        </tbody>
    </table>
</div>