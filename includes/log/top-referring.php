<script type="text/javascript">
    jQuery(document).ready(function () {
        postboxes.add_postbox_toggles(pagenow);
    });
</script>
<?php
$date_args = '';
//Set Default Time Picker Option
list( $daysToDisplay, $rangestart, $rangeend ) = wp_statistics_prepare_range_time_picker();
if ( isset( $_GET['hitdays'] ) and $_GET['hitdays'] > 0 ) {
	$date_args .= '&hitdays=' . $daysToDisplay;
}
if ( isset( $_GET['rangeend'] ) and isset( $_GET['rangestart'] ) and strtotime( $_GET['rangestart'] ) != false and strtotime( $_GET['rangeend'] ) != false ) {
	$date_args .= '&rangestart=' . $rangestart . '&rangeend=' . $rangeend;
}

list( $daysToDisplay, $rangestart_utime, $rangeend_utime ) = wp_statistics_date_range_calculator(
	$daysToDisplay,
	$rangestart,
	$rangeend
);

$rangestartdate = $WP_Statistics->real_current_date( 'Y-m-d', '-0', $rangestart_utime );
$rangeenddate   = $WP_Statistics->real_current_date( 'Y-m-d', '-0', $rangeend_utime );

if ( array_key_exists( 'referr', $_GET ) ) {
	$referr       = $title = $_GET['referr'];
	$referr_field = '&referr=' . $referr;
} else {
	$referr       = '';
	$referr_field = null;
}

$get_urls = array();
$total    = 0;

if ( $referr ) {

	//Get domain Name
	$search_url = wp_statistics_get_domain_name( trim( $_GET['referr'] ) );
	$result     = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM `{$wpdb->prefix}statistics_visitor` WHERE `referred` REGEXP \"^(https?://|www\\.)[\.A-Za-z0-9\-]+\\.[a-zA-Z]{2,4}\" AND referred <> '' AND LENGTH(referred) >=12 AND (`referred` LIKE  %s OR `referred` LIKE %s OR `referred` LIKE %s OR `referred` LIKE %s) AND `last_counter` BETWEEN %s AND %s ORDER BY `{$wpdb->prefix}statistics_visitor`.`ID` DESC",
			'https://www.' . $wpdb->esc_like( $search_url ) . '%', 'https://' . $wpdb->esc_like( $search_url ) . '%', 'http://www.' . $wpdb->esc_like( $search_url ) . '%', 'http://' . $wpdb->esc_like( $search_url ) . '%',
			$rangestartdate,
			$rangeenddate
		)
	);

	$total = count( $result );
} else {

	//Get Wordpress Domain
	$where       = '';
	$domain_name = rtrim( preg_replace( '/^https?:\/\//', '', get_site_url() ), " / " );
	foreach ( array( "http", "https", "ftp" ) as $protocol ) {
		foreach ( array( '', 'www.' ) as $w3 ) {
			$where = " AND `referred` NOT LIKE '{$protocol}://{$w3}{$domain_name}%' ";
		}
	}

	//Get List referred
	$result = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT SUBSTRING_INDEX(REPLACE( REPLACE( referred, 'http://', '') , 'https://' , '') , '/', 1 ) as `domain`, count(referred) as `number` FROM {$wpdb->prefix}statistics_visitor WHERE `referred` REGEXP \"^(https?://|www\\.)[\.A-Za-z0-9\-]+\\.[a-zA-Z]{2,4}\" AND referred <> '' AND LENGTH(referred) >=12 AND `last_counter` BETWEEN %s AND %s {$where} GROUP BY domain ORDER BY `number` DESC",
			$rangestartdate,
			$rangeenddate
		)
	);

	//Number Total Row
	$total = count( $result );
}

//Load country Code
$ISOCountryCode = $WP_Statistics->get_country_codes();

?>
<div class="wrap wps-wrap">
	<?php WP_Statistics_Admin_Pages::show_page_title( __( 'Top Referring Sites', 'wp-statistics' ) ); ?>
    <div><?php wp_statistics_date_range_selector( WP_Statistics::$page['referrers'], $daysToDisplay, null, null, $referr_field ); ?></div>
    <br class="clear"/>

    <ul class="subsubsub">
		<?php if ( $referr ) { ?>
            <li class="all"><a <?php if ( ! $referr ) {
					echo 'class="current"';
				} ?>href="?page=<?php echo WP_Statistics::$page['referrers'] . $date_args; ?>"><?php _e(
						'All',
						'wp-statistics'
					); ?></a>
            </li>|
            <li>
                <a class="current" href="?page=<?php echo WP_Statistics::$page['referrers']; ?>&referr=<?php echo $WP_Statistics->html_sanitize_referrer( $referr ) . $date_args; ?>"> <?php echo htmlentities( $title, ENT_QUOTES ); ?>
                    <span class="count">(<?php echo number_format_i18n( $total ); ?>)</span></a></li>
		<?php } else { ?>
            <li class="all"><a <?php if ( ! $referr ) {
					echo 'class="current"';
				} ?>href="?page=<?php echo WP_Statistics::$page['referrers'] . $date_args; ?>"><?php _e(
						'All',
						'wp-statistics'
					); ?>
                    <span class="count">(<?php echo number_format_i18n( $total ); ?>)</span></a></li>
		<?php } ?>
    </ul>
    <div class="postbox-container" id="last-log">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
					<?php if ( $referr ) {
						$paneltitle = sprintf(
							__( 'Referring site: %s', 'wp-statistics' ),
							$WP_Statistics->html_sanitize_referrer( $referr )
						);
					} else {
						$paneltitle = __( 'Top Referring Sites', 'wp-statistics' );
					}; ?>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf( __( 'Toggle panel: %s', 'wp-statistics' ), $paneltitle ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></h2>

                    <div class="inside">
						<?php
						echo "<div class='log-latest'>";

						if ( $total > 0 ) {
							// Initiate pagination object with appropriate arguments
							$items_per_page = 10;
							$page           = isset( $_GET['pagination-page'] ) ? abs( (int) $_GET['pagination-page'] ) : 1;
							$offset         = ( $page * $items_per_page ) - $items_per_page;
							$start          = $offset;
							$end            = $offset + $items_per_page;

							if ( $referr ) {

								//Show Table
								echo "<table width=\"100%\" class=\"widefat table-stats\" id=\"top-referring\"><tr>";
								echo "<td>" . __( 'Link', 'wp-statistics' ) . "</td>";
								echo "<td>" . __( 'IP', 'wp-statistics' ) . "</td>";
								echo "<td>" . __( 'Browser', 'wp-statistics' ) . "</td>";
								if ( $WP_Statistics->get_option( 'geoip' ) ) {
									echo "<td>" . __( 'Country', 'wp-statistics' ) . "</td>";
								}
								echo "<td>" . __( 'Date', 'wp-statistics' ) . "</td>";
								echo "<td></td>";
								echo "</tr>";

								$i = 1;
								foreach ( $result as $items ) {
									if ( $i > $start and $i <= $end ) {

										//Sanitize IP
										if ( substr( $items->ip, 0, 6 ) == '#hash#' ) {
											$ip_string  = __( '#hash#', 'wp-statistics' );
											$map_string = "";
										} else {
											$ip_string  = "{$items->ip}";
											$map_string = "<a class='wps-text-muted' href='" . WP_Statistics_Admin_Pages::admin_url( 'overview', array( 'type' => 'last-all-visitor', 'ip' => $items->ip ) ) . "'>" . wp_statistics_icons( 'dashicons-visibility', 'visibility' ) . "</a><a class='show-map wps-text-muted' href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank' title='" . __( 'Map', 'wp-statistics' ) . "'>" . wp_statistics_icons( 'dashicons-location-alt', 'map' ) . "</a>";
										}

										echo "<tr>";

										//show Referrer Link
										echo "<td style=\"text-align: left\">";
										echo '<a href="' . $items->referred . '" target="_blank" title="' . $items->referred . '">' . preg_replace( "(^https?://)", "", trim( $items->referred ) ) . '</a>';
										echo "</td>";

										//Show IP
										echo "<td style=\"text-align: left\">";
										echo $ip_string;
										echo "</td>";

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

										//Show Date
										echo "<td style=\"text-align: left\">";
										echo date_i18n( get_option( 'date_format' ), strtotime( $items->last_counter ) );
										echo "</td>";

										//Show Link View IP
										echo "<td style=\"text-align: center\">";
										echo $map_string;
										echo "</td>";

										echo '</tr>';

									}
									$i ++;
								}

								echo '</table>';
							} else {

								//Show Table
								echo "<table width=\"100%\" class=\"widefat table-stats\" id=\"top-referring\"><tr>";
								echo "<td>" . __( 'Rating', 'wp-statistics' ) . "</td>";
								echo "<td>" . __( 'Site Url', 'wp-statistics' ) . "</td>";
								echo "<td>" . __( 'Site Title', 'wp-statistics' ) . "</td>";
								echo "<td>" . __( 'Server IP', 'wp-statistics' ) . "</td>";
								if ( $WP_Statistics->get_option( 'geoip' ) ) {
									echo "<td>" . __( 'Country', 'wp-statistics' ) . "</td>";
								}
								echo "<td>" . __( 'References', 'wp-statistics' ) . "</td>";
								echo "<td></td>";
								echo "</tr>";


								//Get Refer Site Detail
								$refer_opt     = get_option( 'wp_statistics_referrals_detail' );
								$referrer_list = ( empty( $refer_opt ) ? array() : $refer_opt );

								//Default unknown Column Value
								$unknown = '<span aria-hidden="true">—</span><span class="screen-reader-text">' . __( "Unknown", 'wp-statistics' ) . '</span>';

								$i = 1;
								foreach ( $result as $items ) {
									if ( $i > $start and $i <= $end ) {

										//Prepare Data
										$domain = $items->domain;
										$number = wp_statistics_get_number_referer_from_domain( $items->domain, array( $rangestartdate, $rangeenddate ) );

										//Get Site Link
										$referrer_html = $WP_Statistics->html_sanitize_referrer( $domain );

										//Get Site information if Not Exist
										if ( ! array_key_exists( $domain, $referrer_list ) ) {
											$get_site_inf             = wp_statistics_get_domain_server( $domain );
											$get_site_title           = wp_statistics_get_site_title( $domain );
											$referrer_list[ $domain ] = array(
												'ip'      => $get_site_inf['ip'],
												'country' => $get_site_inf['country'],
												'title'   => ( $get_site_title === false ? '' : $get_site_title ),
											);
										}

										echo "<tr>";
										echo "<td>" . number_format_i18n( $i ) . "</td>";
										echo "<td>" . wp_statistics_show_site_icon( $domain ) . " " . $WP_Statistics->get_referrer_link( $domain, $referrer_list[ $domain ]['title'] ) . "</td>";
										echo "<td>" . ( trim( $referrer_list[ $domain ]['title'] ) == "" ? $unknown : $referrer_list[ $domain ]['title'] ) . "</td>";
										echo "<td>" . ( trim( $referrer_list[ $domain ]['ip'] ) == "" ? $unknown : $referrer_list[ $domain ]['ip'] ) . "</td>";
										if ( $WP_Statistics->get_option( 'geoip' ) ) {
											echo "<td>" . ( trim( $referrer_list[ $domain ]['country'] ) == "" ? $unknown : "<img src='" . plugins_url( 'wp-statistics/assets/images/flags/' . $referrer_list[ $domain ]['country'] . '.png' ) . "' title='{$ISOCountryCode[$referrer_list[ $domain ]['country']]}' class='log-tools'/>" ) . "</td>";
										}
										echo "<td><a class='wps-text-success' href='?page=" . WP_Statistics::$page['referrers'] . "&referr=" . $referrer_html . $date_args . "'>" . number_format_i18n( $number ) . "</a></td>";
										echo "</tr>";
									}
									$i ++;
								}

								echo "</table>";

								//Save Referrer List Update
								update_option( 'wp_statistics_referrals_detail', $referrer_list, 'no' );
							}
						}

						echo '</div>';
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
				}
				?>
            </div>
        </div>
    </div>
</div>