<script type="text/javascript">
    jQuery(document).ready(function () {
        postboxes.add_postbox_toggles(pagenow);
    });
</script>
<div class="wrap wps-wrap">
	<?php WP_Statistics_Admin_Pages::show_page_title( __( 'Online Users', 'wp-statistics' ) ); ?>
    <div class="postbox-container" id="last-log">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
					<?php $paneltitle = __( 'Online Users', 'wp-statistics' ); ?>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf( __( 'Toggle panel: %s', 'wp-statistics' ), $paneltitle ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></span></h2>
                    <div class="inside">
						<?php
						//Get List ISO country
						$ISOCountryCode = $WP_Statistics->get_country_codes();

						//Get Total User Online
						$sql   = "SELECT COUNT(*) FROM {$wpdb->prefix}statistics_useronline";
						$total = $wpdb->get_var( $sql );

						if ( $total > 0 ) {

							// Load city name
							$geoip_reader = false;
							if ( $WP_Statistics->get_option( 'geoip_city' ) ) {
								$geoip_reader = $WP_Statistics::geoip_loader( 'city' );
							}

							//Show Table
							echo "<table width=\"100%\" class=\"widefat table-stats\" id=\"online-users\"><tr>";
							echo "<td>" . __( 'Browser', 'wp-statistics' ) . "</td>";
							if ( $WP_Statistics->get_option( 'geoip' ) ) {
								echo "<td>" . __( 'Country', 'wp-statistics' ) . "</td>";
							}
							if ( $WP_Statistics->get_option( 'geoip_city' ) ) {
								echo "<td>" . __( 'City', 'wp-statistics' ) . "</td>";
							}
							echo "<td>" . __( 'IP', 'wp-statistics' ) . "</td>";
							echo "<td>" . __( 'Online For', 'wp-statistics' ) . "</td>";
							echo "<td>" . __( 'Page', 'wp-statistics' ) . "</td>";
							echo "<td>" . __( 'Referrer', 'wp-statistics' ) . "</td>";
							echo "<td></td>";
							echo "</tr>";

							// Instantiate pagination object with appropriate arguments
							$items_per_page = 10;
							$page           = isset( $_GET['pagination-page'] ) ? abs( (int) $_GET['pagination-page'] ) : 1;
							$offset         = ( $page * $items_per_page ) - $items_per_page;
							$start          = $offset;
							$end            = $offset + $items_per_page;

							//Get Query Result
							$query  = str_replace( "SELECT COUNT(*) FROM", "SELECT * FROM", $sql ) . "  ORDER BY `{$wpdb->prefix}statistics_useronline`.`ID` DESC LIMIT {$offset}, {$items_per_page}";
							$result = $wpdb->get_results( $query );

							foreach ( $result as $items ) {

								//Sanitize Online Table
								if ( substr( $items->ip, 0, 6 ) == '#hash#' ) {
									$ip_string  = __( '#hash#', 'wp-statistics' );
									$map_string = "";
								} else {
									$ip_string  = "{$items->ip}";
									$map_string = "<a class='wps-text-muted' href='" . WP_Statistics_Admin_Pages::admin_url( 'overview', array( 'type' => 'last-all-visitor', 'ip' => $items->ip ) ) . "'>" . wp_statistics_icons( 'dashicons-visibility', 'visibility' ) . "</a><a class='show-map wps-text-muted' href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank' title='" . __( 'Map', 'wp-statistics' ) . "'>" . wp_statistics_icons( 'dashicons-location-alt', 'map' ) . "</a>";
								}

								echo "<tr>";

								//Show Browser
								echo "<td style=\"text-align: left\">";
								if ( array_search( strtolower( $items->agent ), wp_statistics_get_browser_list( 'key' ) ) !== false ) {
									$agent = "<img src='" . plugins_url( 'wp-statistics/assets/images/' ) . $items->agent . ".png' class='log-tools' title='{$items->agent}'/>";
								} else {
									$agent = wp_statistics_icons( 'dashicons-editor-help', 'unknown' );
								}
								echo "<a href='" . WP_Statistics_Admin_Pages::admin_url( 'overview', array( 'type' => 'last-all-visitor', 'agent' => $items->agent ) ) . "'>{$agent}</a>";
								echo "</td>";

								//Show Country
								if ( $WP_Statistics->get_option( 'geoip' ) ) {
									echo "<td style=\"text-align: left\">";
									echo "<img src='" . plugins_url( 'wp-statistics/assets/images/flags/' . $items->location . '.png' ) . "' title='{$ISOCountryCode[$items->location]}' class='log-tools'/>";
									echo "</td>";
								}

								//Show City
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

								if ( $WP_Statistics->get_option( 'geoip_city' ) ) {
									echo "<td style=\"text-align: left\">";
									echo $city;
									echo "</td>";
								}

								//Show IP
								echo "<td style=\"text-align: left\">";
								echo $ip_string;
								echo "</td>";

								//Show Online For
								echo "<td style=\"text-align: left\">";
								$timediff = ( $items->timestamp - $items->created );
								if ( $timediff > 3600 ) {
									$onlinefor = date( "H:i:s", ( $items->timestamp - $items->created ) );
								} else if ( $timediff > 60 ) {
									$onlinefor = "00:" . date( "i:s", ( $items->timestamp - $items->created ) );
								} else {
									$onlinefor = "00:00:" . date( "s", ( $items->timestamp - $items->created ) );
								}
								echo "<span>" . $onlinefor . "</span>";
								echo "</td>";

								//Show Page
								$page_info = wp_statistics_get_page_info( $items->page_id, $items->type );
								echo "<td style=\"text-align: left\">";
								echo ( $page_info['link'] != '' ? '<a href="' . $page_info['link'] . '" target="_blank" class="wps-text-danger">' : '' ) . mb_substr( $page_info['title'], 0, 200, "utf-8" ) . ( $page_info['link'] != '' ? '</a>' : '' );
								echo "</td>";

								//Show Referrer
								echo "<td style=\"text-align: left\">";
								echo $WP_Statistics->get_referrer_link( $items->referred );
								echo "</td>";

								//Show Link View IP
								echo "<td style=\"text-align: center\">";
								echo $map_string;
								echo "</td>";

								echo '</tr>';
							}

							echo "</table>";
						} else {
							echo "<div class='wps-center'>" . __( 'Currently there are no online users in the site.', 'wp-statistics' ) . "</div>";
						}
						?>
                    </div>
                </div>
				<?php
				if ( $total > 0 ) {
					wp_statistics_paginate_links( array(
						'item_per_page' => $items_per_page,
						'total'         => $total,
						'current'       => $page,
					) );
				} ?>
            </div>
        </div>
    </div>
</div>