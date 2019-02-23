<script type="text/javascript">
    jQuery(document).ready(function () {
        postboxes.add_postbox_toggles(pagenow);
    });
</script>
<?php
//Set Default Time Picker Option
list( $daysToDisplay, $rangestart, $rangeend ) = wp_statistics_prepare_range_time_picker();
list( $daysToDisplay, $rangestart_utime, $rangeend_utime ) = wp_statistics_date_range_calculator(
	$daysToDisplay,
	$rangestart,
	$rangeend
);

//Load ISO
$ISOCountryCode = $WP_Statistics->get_country_codes();

//Get Custom Country
$country_name  = '';
$total_visitor = 0;
if ( isset( $_REQUEST['country'] ) ) {
	if ( array_key_exists( $_REQUEST['country'], $ISOCountryCode ) ) {
		$country_name = $ISOCountryCode[ $_REQUEST['country'] ];
		$total        = $wpdb->get_var( "SELECT COUNT(`location`) AS `count` FROM `{$wpdb->prefix}statistics_visitor` WHERE `location` = '" . $_REQUEST['country'] . "'" );
	} else {
		echo '<script>window.location.href = "' . WP_Statistics_Admin_Pages::admin_url( 'countries' ) . '";</script>';
	}
}

?>
<div class="wrap wps-wrap">
	<?php
	//Show Time Range only in all list
	if ( ! isset( $_REQUEST['country'] ) ) {
		WP_Statistics_Admin_Pages::show_page_title( __( 'Top Countries', 'wp-statistics' ) );
		wp_statistics_date_range_selector( WP_Statistics::$page['countries'], $daysToDisplay );
	} else {
		WP_Statistics_Admin_Pages::show_page_title( $country_name . ' ' . __( 'Visitors', 'wp-statistics' ) );
		?>
        <br/>
        <ul class="subsubsub">
            <li class="all">
                <a href="<?php echo WP_Statistics_Admin_Pages::admin_url( 'countries' ); ?>"><?php _e( 'All', 'wp-statistics' ); ?></a>
            </li>
            |
            <li>
                <a class="current" href="<?php echo WP_Statistics_Admin_Pages::admin_url( 'countries', array( 'country' => $_REQUEST['country'] ) ) ?>">
					<?php echo $country_name; ?>
                    <span class="count">(<?php echo number_format_i18n( $total ); ?>)</span></a>
            </li>
        </ul>
		<?php
	}
	?>
    <div class="postbox-container" id="last-log">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
					<?php
					if ( ! isset( $_REQUEST['country'] ) ) {
						$paneltitle = __( 'Top Countries', 'wp-statistics' );
					} else {
						$paneltitle = $country_name;
					}
					?>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf( __( 'Toggle panel: %s', 'wp-statistics' ), $paneltitle ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></h2>

                    <div class="inside">
						<?php
						if ( ! isset( $_REQUEST['country'] ) ) {
							?>

                            <table class="widefat table-stats" id="last-referrer" style="width: 100%;">
                                <tr>
                                    <td width="10%"><?php _e( 'Rank', 'wp-statistics' ); ?></td>
                                    <td width="30%" style="text-align: center;"><?php _e( 'Flag', 'wp-statistics' ); ?></td>
                                    <td width="30%" style="text-align: center;"><?php _e( 'Country', 'wp-statistics' ); ?></td>
                                    <td width="30%" style="text-align: center;"><?php _e( 'Visitor Count', 'wp-statistics' ); ?></td>
                                </tr>

								<?php
								$rangestartdate = $WP_Statistics->real_current_date( 'Y-m-d', '-0', $rangestart_utime );
								$rangeenddate   = $WP_Statistics->real_current_date( 'Y-m-d', '-0', $rangeend_utime );

								$result = $wpdb->get_results(
									sprintf( "SELECT `location`, COUNT(`location`) AS `count` FROM `{$wpdb->prefix}statistics_visitor` WHERE `last_counter` BETWEEN '%s' AND '%s' GROUP BY `location` ORDER BY `count` DESC",
										$rangestartdate,
										$rangeenddate
									)
								);
								$i      = 0;

								foreach ( $result as $item ) {
									$i ++;
									$item->location = strtoupper( $item->location );

									echo "<tr>";
									echo "<td>$i</td>";
									echo "<td style=\"text-align: center;\"><img src='" . plugins_url( 'wp-statistics/assets/images/flags/' . $item->location . '.png' ) . "' title='{$ISOCountryCode[$item->location]}'/></td>";
									echo "<td style='text-align: left; padding-" . ( is_rtl() === true ? 'right' : 'left' ) . ": 12.8%;'>{$ISOCountryCode[$item->location]}</td>";
									echo "<td style=\"text-align: center;\"><a href='" . WP_Statistics_Admin_Pages::admin_url( 'countries', array( 'country' => $item->location ) ) . "'>" . number_format_i18n( $item->count ) . "</a></td>";
									echo "</tr>";
								}
								?>
                            </table>
						<?php } else {
							/*
							 * Show Custom Country
							 */

							// Retrieve MySQL data
							$sql = "SELECT count(*) FROM `{$wpdb->prefix}statistics_visitor` WHERE `location` = '" . $_REQUEST['country'] . "'";

							// Instantiate pagination object with appropriate arguments
							$total          = $wpdb->get_var( $sql );
							$items_per_page = 15;
							$page           = isset( $_GET['pagination-page'] ) ? abs( (int) $_GET['pagination-page'] ) : 1;
							$offset         = ( $page * $items_per_page ) - $items_per_page;

							//Get Query Result
							$query  = str_replace( "SELECT count(*) FROM", "SELECT * FROM", $sql ) . "  ORDER BY `{$wpdb->prefix}statistics_visitor`.`ID` DESC LIMIT {$offset}, {$items_per_page}";
							$result = $wpdb->get_results( $query );

							echo "<table width=\"100%\" class=\"widefat table-stats\" id=\"last-referrer\"><tr>";
							echo "<td>" . __( 'Browser', 'wp-statistics' ) . "</td>";
							if ( $WP_Statistics->get_option( 'geoip' ) ) {
								echo "<td>" . __( 'Country', 'wp-statistics' ) . "</td>";
							}
							if ( $WP_Statistics->get_option( 'geoip_city' ) ) {
								echo "<td>" . __( 'City', 'wp-statistics' ) . "</td>";
							}
							echo "<td>" . __( 'Date', 'wp-statistics' ) . "</td>";
							echo "<td>" . __( 'IP', 'wp-statistics' ) . "</td>";
							echo "<td>" . __( 'Referrer', 'wp-statistics' ) . "</td>";
							echo "</tr>";

							// Load city name
							$geoip_reader = false;
							if ( $WP_Statistics->get_option( 'geoip_city' ) ) {
								$geoip_reader = $WP_Statistics::geoip_loader( 'city' );
							}

							foreach ( $result as $items ) {
								echo "<tr>";
								echo "<td style=\"text-align: left\">";
								if ( array_search( strtolower( $items->agent ), wp_statistics_get_browser_list( 'key' ) ) !== false ) {
									$agent = "<img src='" . plugins_url( 'wp-statistics/assets/images/' ) . $items->agent . ".png' class='log-tools' title='{$items->agent}'/>";
								} else {
									$agent = wp_statistics_icons( 'dashicons-editor-help', 'unknown' );
								}
								echo "<a href='" . WP_Statistics_Admin_Pages::admin_url( 'overview', array( 'type' => 'last-all-visitor', 'agent' => $items->agent ) ) . "'>{$agent}</a>";
								echo "</td>";
								$city = '';
								if ( $WP_Statistics->get_option( 'geoip_city' ) ) {
									if ( $geoip_reader != false ) {
										try {
											$reader = $geoip_reader->city( $items->ip );
											$city   = $reader->city->name;
										} catch ( Exception $e ) {
											$city = __( 'Unknown', 'wp-statistics' );
										}

										if ( ! $city ) {
											$city = __( 'Unknown', 'wp-statistics' );
										}
									}
								}

								if ( $WP_Statistics->get_option( 'geoip' ) ) {
									echo "<td style=\"text-align: left\">";
									echo "<img src='" . plugins_url( 'wp-statistics/assets/images/flags/' . $items->location . '.png' ) . "' title='{$ISOCountryCode[$items->location]}' class='log-tools'/>";
									echo "</td>";
								}

								if ( $WP_Statistics->get_option( 'geoip_city' ) ) {
									echo "<td style=\"text-align: left\">";
									echo $city;
									echo "</td>";
								}

								echo "<td style=\"text-align: left\">";
								echo date_i18n( get_option( 'date_format' ), strtotime( $items->last_counter ) );
								echo "</td>";

								echo "<td style=\"text-align: left\">";
								if ( substr( $items->ip, 0, 6 ) == '#hash#' ) {
									$ip_string = __( '#hash#', 'wp-statistics' );
								} else {
									$ip_string = "<a href='" . WP_Statistics_Admin_Pages::admin_url( 'visitors', array( 'type' => 'last-all-visitor', 'ip' => $items->ip ) ) . "'>{$items->ip}</a>";
								}
								echo $ip_string;
								echo "</td>";

								echo "<td style=\"text-align: left\">";
								echo $WP_Statistics->get_referrer_link( $items->referred );
								echo "</td>";

								echo "</tr>";
							}
							echo "</table>";
						} ?>
                    </div>
                </div>
            </div>
        </div>
		<?php
		if ( isset( $_REQUEST['country'] ) ) {
			//Show Pagination
			wp_statistics_paginate_links( array(
				'item_per_page' => $items_per_page,
				'total'         => $total,
				'current'       => $page,
			) );
		}
		?>
    </div>
</div>
