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
<div class="wrap">
    <h2><?php _e( 'Recent Visitors', 'wp-statistics' ); ?></h2>
	<?php do_action( 'wp_statistics_after_title' ); ?>

    <ul class="subsubsub">
        <li class="all"><a <?php if ( $_get == '%' ) {
				echo 'class="current"';
			} ?>href="?page=<?php echo WP_Statistics::$page['visitors']; ?>"><?php _e( 'All', 'wp-statistics' ); ?>
                <span class="count">(<?php echo $total_visitor; ?>)</span></a></li>
		<?php
		if ( isset( $_var ) ) {
			$spacer = " | ";

			if ( $_var == 'agent' ) {
				$Browsers = wp_statistics_ua_list();
				$i        = 0;
				$Total    = count( $Browsers );

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
					echo $spacer .
					     "<li><a " .
					     $current .
					     "href='?page=" .
					     WP_Statistics::$page['visitors'] .
					     "&agent=" .
					     $Browser .
					     "'> " .
					     __( $Browser, 'wp-statistics' ) .
					     " <span class='count'>(" .
					     number_format_i18n( wp_statistics_useragent( $Browser ) ) .
					     ")</span></a></li>";
				}
			} else {
				if ( $_get != '%' ) {
					$current = 'class="current" ';
				} else {
					$current = "";
				}
				echo $spacer .
				     "<li><a {$current} href='?page=" .
				     WP_Statistics::$page['visitors'] .
				     "&{$_var}={$_get}'>{$title} <span class='count'>({$total})</span></a></li>";
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
						<span class="screen-reader-text"><?php printf(
								__( 'Toggle panel: %s', 'wp-statistics' ),
								$paneltitle
							); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></span></h2>

                    <div class="inside">
						<?php
						// Instantiate pagination object with appropriate arguments
						$pagesPerSection = 10;
						$options         = array( 25, "All" );
						$stylePageOff    = "pageOff";
						$stylePageOn     = "pageOn";
						$styleErrors     = "paginationErrors";
						$styleSelect     = "paginationSelect";

						$Pagination = new WP_Statistics_Pagination(
							$total,
							$pagesPerSection,
							$options,
							false,
							$stylePageOff,
							$stylePageOn,
							$styleErrors,
							$styleSelect
						);

						$start = $Pagination->getEntryStart();
						$end   = $Pagination->getEntryEnd();

						// Retrieve MySQL data
						if ( $_get != '%' ) {
							$result = $wpdb->get_results(
								$wpdb->prepare(
									"SELECT * FROM `{$wpdb->prefix}statistics_visitor` WHERE `{$_var}` LIKE %s ORDER BY `{$wpdb->prefix}statistics_visitor`.`ID` DESC  LIMIT {$start}, {$end}",
									$_get
								)
							);
						} else {
							$result = $wpdb->get_results(
								"SELECT * FROM `{$wpdb->prefix}statistics_visitor` ORDER BY `{$wpdb->prefix}statistics_visitor`.`ID` DESC  LIMIT {$start}, {$end}"
							);
						}

						echo "<table width=\"100%\" class=\"widefat table-stats\" id=\"last-referrer\">
		                      <tr>";
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
							if ( array_search(
								     strtolower( $items->agent ),
								     array(
									     "chrome",
									     "firefox",
									     "msie",
									     "opera",
									     "safari",
								     )
							     ) !== false
							) {
								$agent = "<img src='" .
								         plugins_url( 'wp-statistics/assets/images/' ) .
								         $items->agent .
								         ".png' class='log-tools' title='{$items->agent}'/>";
							} else {
								$agent = wp_statistics_icons( 'dashicons-editor-help', 'unknown' );
							}
							echo "<a href='?page=" .
							     WP_Statistics::$page['overview'] .
							     "&type=last-all-visitor&agent={$items->agent}'>{$agent}</a>";
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
								echo "<img src='" .
								     plugins_url( 'wp-statistics/assets/images/flags/' . $items->location . '.png' ) .
								     "' title='{$ISOCountryCode[$items->location]}' class='log-tools'/>";
								echo "</td>";
							}

							if ( $WP_Statistics->get_option( 'geoip_city' ) ) {
								echo "<td style=\"text-align: left\">";
								echo $city;
								echo "</td>";
							}

							echo "<td style=\"text-align: left\">";
							echo date( get_option( 'date_format' ), strtotime( $items->last_counter ) );
							echo "</td>";

							echo "<td style=\"text-align: left\">";
							if ( substr( $items->ip, 0, 6 ) == '#hash#' ) {
								$ip_string = __( '#hash#', 'wp-statistics' );
							} else {
								$ip_string = "<a href='admin.php?page=" .
								             WP_Statistics::$page['visitors'] .
								             "&type=last-all-visitor&ip={$items->ip}'>{$items->ip}</a>";
							}
							echo $ip_string;
							echo "</td>";

							echo "<td style=\"text-align: left\">";
							echo "<td style=\"text-align: left\">" . $WP_Statistics->get_referrer_link( $items->referred ) . "</td>";
							echo "</td>";

							echo "</tr>";
						}

						echo "</table>";
						?>

                        <div class="pagination-log">
							<?php echo $Pagination->display(); ?>
                            <p id="result-log"><?php printf(
									__( 'Page %1$s of %2$s', 'wp-statistics' ),
									$Pagination->getCurrentPage(),
									$Pagination->getTotalPages()
								); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>