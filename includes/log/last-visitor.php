<script type="text/javascript">
    jQuery(document).ready(function () {
        postboxes.add_postbox_toggles(pagenow);
    });
</script>
<?php
$ISOCountryCode = $WP_Statistics->get_country_codes();

$_var  = 'agent';
$_get  = '%';
$title = 'All';

if ( array_key_exists( 'agent', $_GET ) ) {
	$_var  = 'agent';
	$_get  = '%' . $_GET['agent'] . '%';
	$title = htmlentities( $_GET['agent'], ENT_QUOTES );
}

if ( array_key_exists( 'ip', $_GET ) ) {
	$_var  = 'ip';
	$_get  = '%' . $_GET['ip'] . '%';
	$title = htmlentities( $_GET['ip'], ENT_QUOTES );
}

$_get          = esc_attr( $_get );
$total_visitor = $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->prefix}statistics_visitor`" );

if ( $_get != '%' ) {
	$total = $wpdb->get_var(
		$wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}statistics_visitor` WHERE `{$_var}` LIKE %s", $_get )
	);
} else {
	$total = $total_visitor;
}

?>
<div class="wrap wps-wrap">
	<?php WP_Statistics_Admin_Pages::show_page_title( __( 'Recent Visitors', 'wp-statistics' ) ); ?>
    <br/>
    <ul class="subsubsub">
        <li class="all"><a <?php if ( $_get == '%' ) {
				echo 'class="current"';
			} ?>href="<?php echo WP_Statistics_Admin_Pages::admin_url( 'visitors' ); ?>"><?php _e( 'All', 'wp-statistics' ); ?>
                <span class="count">(<?php echo number_format_i18n( $total_visitor ); ?>)</span></a></li>
		<?php
		if ( isset( $_var ) ) {
			$spacer = " | ";

			if ( $_var == 'agent' ) {
				$Browsers      = wp_statistics_ua_list();
				$browser_names = wp_statistics_get_browser_list();
				$i             = 0;
				$Total         = count( $Browsers );
				echo $spacer;
				foreach ( $Browsers as $Browser ) {
					if ( $Browser == null ) {
						continue;
					}

					$i ++;
					if ( $title == $Browser ) {
						$current = 'class="current" ';
					} else {
						$current = "";
					}
					if ( $i == $Total ) {
						$spacer = "";
					}

					//Get Browser name
					$browser_name = wp_statistics_get_browser_list( strtolower( $Browser ) );
					echo "<li><a " . $current . "href='" . WP_Statistics_Admin_Pages::admin_url( 'visitors', array( 'agent' => $Browser ) ) . "'> " . $browser_name . " <span class='count'>(" . number_format_i18n( wp_statistics_useragent( $Browser ) ) . ")</span></a></li>";
					echo $spacer;
				}
			} else {
				if ( $_get != '%' ) {
					$current = 'class="current" ';
				} else {
					$current = "";
				}
				echo $spacer . "<li><a {$current} href='?page=" . WP_Statistics::$page['visitors'] . "&{$_var}={$_get}'>{$title} <span class='count'>({$total})</span></a></li>";
			}
		}
		?>
    </ul>
    <div class="postbox-container" id="last-log">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
					<?php $paneltitle = __( 'Recent Visitor Statistics', 'wp-statistics' );
					if ( $_get != '%' ) {
						$paneltitle = $paneltitle . ' [' . __( 'Filtered by', 'wp-statistics' ) . ': ' . $title . ']';
					} ?>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf( __( 'Toggle panel: %s', 'wp-statistics' ), $paneltitle ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></span></h2>

                    <div class="inside">
						<?php
						// Retrieve MySQL data
						if ( $_get != '%' ) {
							$sql = $wpdb->prepare( "SELECT count(*) FROM `{$wpdb->prefix}statistics_visitor` WHERE `{$_var}` LIKE %s", $_get );
						} else {
							$sql = "SELECT count(*) FROM `{$wpdb->prefix}statistics_visitor`";
						}

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
						?>
                    </div>
                </div>
				<?php
				//Show Pagination
				wp_statistics_paginate_links( array(
					'item_per_page' => $items_per_page,
					'total'         => $total,
					'current'       => $page,
				) );
				?>
            </div>
        </div>