<script type="text/javascript">
    jQuery(document).ready(function () {
        postboxes.add_postbox_toggles(pagenow);
    });
</script>
<?php
$search_engines = wp_statistics_searchengine_list();

$search_result['All'] = wp_statistics_searchword( 'all', 'total' );

foreach ( $search_engines as $key => $se ) {
	$search_result[ $key ] = wp_statistics_searchword( $key, 'total' );
}

if ( array_key_exists( 'referred', $_GET ) ) {
	if ( $_GET['referred'] != '' ) {
		$referred = $_GET['referred'];
	} else {
		$referred = 'All';
	}
} else {
	$referred = 'All';
}

$total = $search_result[ $referred ];
?>
<div class="wrap">
    <h2><?php _e( 'Latest Search Words', 'wp-statistics' ); ?></h2>
	<?php do_action( 'wp_statistics_after_title' ); ?>

    <ul class="subsubsub">
		<?php
		$search_result_count = count( $search_result );
		$i                   = 0;
		$separator           = ' | ';

		foreach ( $search_result as $key => $value ) {
			$i ++;

			if ( $i == $search_result_count ) {
				$separator = '';
			}

			if ( $key == 'All' ) {
				$tag       = '';
				$name      = 'All';
				$translate = __( 'All', 'wp-statistics' );
			} else {
				$tag       = $search_engines[ $key ]['tag'];
				$name      = $search_engines[ $key ]['name'];
				$translate = $search_engines[ $key ]['translated'];
			}

			echo "<li><a href='?page=" .
			     WP_Statistics::$page['words'] .
			     "&referred={$tag}'>" .
			     $translate .
			     " <span class='count'>({$value})</span></a></li>{$separator}";
		}
		?>
    </ul>
    <div class="postbox-container" id="last-log">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
					<?php $paneltitle = __( 'Latest Search Word Statistics', 'wp-statistics' ); ?>
                    <button class="handlediv" type="button" aria-expanded="true">
						<span class="screen-reader-text"><?php printf(
								__( 'Toggle panel: %s', 'wp-statistics' ),
								$paneltitle
							); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></span></h2>

                    <div class="inside">
                        <div class='log-latest'>
							<?php
							if ( $total > 0 ) {
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
								if ( $referred && $referred != '' ) {
									$search_query = wp_statistics_searchword_query( $referred );
								} else {
									$search_query = wp_statistics_searchword_query( 'all' );
								}

								// Determine if we're using the old or new method of storing search engine info and build the appropriate table name.
								$tablename = $wpdb->prefix . 'statistics_';

								if ( $WP_Statistics->get_option( 'search_converted' ) ) {
									$tabletwo  = $tablename . 'visitor';
									$tablename .= 'search';
									$result    = $wpdb->get_results(
										"SELECT * FROM `{$tablename}` INNER JOIN `{$tabletwo}` on {$tablename}.`visitor` = {$tabletwo}.`ID` WHERE {$search_query} ORDER BY `{$tablename}`.`ID` DESC  LIMIT {$start}, {$end}"
									);
								} else {
									$tablename .= 'visitor';
									$result    = $wpdb->get_results(
										"SELECT * FROM `{$tablename}` WHERE {$search_query} ORDER BY `{$tablename}`.`ID` DESC  LIMIT {$start}, {$end}"
									);
								}

								$ISOCountryCode = $WP_Statistics->get_country_codes();

								$dash_icon = wp_statistics_icons( 'dashicons-location-alt', 'map' );

								echo "<table width=\"100%\" class=\"widefat table-stats\" id=\"last-referrer\">
		                              <tr>";
								echo "<td>" . __( 'Word', 'wp-statistics' ) . "</td>";
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

									if ( ! $WP_Statistics->Search_Engine_QueryString( $items->referred ) ) {
										continue;
									}

									if ( $WP_Statistics->get_option( 'search_converted' ) ) {
										$this_search_engine = $WP_Statistics->Search_Engine_Info_By_Engine( $items->engine );
										$words              = $items->words;
									} else {
										$this_search_engine = $WP_Statistics->Search_Engine_Info( $items->referred );
										$words              = $WP_Statistics->Search_Engine_QueryString( $items->referred );
									}

									echo "<tr>";
									echo "<td style=\"text-align: left\">";
									echo $words;
									echo "</td>";
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
							}
							?>
                        </div>
                    </div>

                    <div class="pagination-log">
						<?php if ( $total > 0 ) {
							echo $Pagination->display(); ?>
                            <p id="result-log"><?php printf(
									__( 'Page %1$s of %2$s', 'wp-statistics' ),
									$Pagination->getCurrentPage(),
									$Pagination->getTotalPages()
								); ?></p>
						<?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>