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
    <h2><?php _e( 'Latest Search Words', 'wp_statistics' ); ?></h2>
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
				$translate = __( 'All', 'wp_statistics' );
			} else {
				$tag       = $search_engines[ $key ]['tag'];
				$name      = $search_engines[ $key ]['name'];
				$translate = $search_engines[ $key ]['translated'];
			}

			echo "<li><a href='?page=" . WP_STATISTICS_WORDS_PAGE . "&referred={$tag}'>" . $translate . " <span class='count'>({$value})</span></a></li>{$separator}";
		}
		?>
    </ul>
    <div class="postbox-container" id="last-log">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
                    <div class="handlediv" title="<?php _e( 'Click to toggle', 'wp_statistics' ); ?>"><br/></div>
                    <h3 class="hndle"><span><?php _e( 'Latest Search Word Statistics', 'wp_statistics' ); ?></span></h3>
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

								$Pagination = new WP_Statistics_Pagination( $total, $pagesPerSection, $options, false, $stylePageOff, $stylePageOn, $styleErrors, $styleSelect );

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
									$result    = $wpdb->get_results( "SELECT * FROM `{$tablename}` INNER JOIN `{$tabletwo}` on {$tablename}.`visitor` = {$tabletwo}.`ID` WHERE {$search_query} ORDER BY `{$tablename}`.`ID` DESC  LIMIT {$start}, {$end}" );
								} else {
									$tablename .= 'visitor';
									$result    = $wpdb->get_results( "SELECT * FROM `{$tablename}` WHERE {$search_query} ORDER BY `{$tablename}`.`ID` DESC  LIMIT {$start}, {$end}" );
								}

								$ISOCountryCode = $WP_Statistics->get_country_codes();

								$dash_icon = wp_statistics_icons( 'dashicons-location-alt', 'map' );

								foreach ( $result as $items ) {
									if ( ! $WP_Statistics->Search_Engine_QueryString( $items->referred ) ) {
										continue;
									}

									if ( substr( $items->ip, 0, 6 ) == '#hash#' ) {
										$ip_string  = __( '#hash#', 'wp_statistics' );
										$map_string = "";
									} else {
										$ip_string  = "<a href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank'>{$items->ip}</a>";
										$map_string = "<a class='show-map' href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank' title='" . __( 'Map', 'wp_statistics' ) . "'>{$dash_icon}</a>";
									}

									if ( $WP_Statistics->get_option( 'search_converted' ) ) {
										$this_search_engine = $WP_Statistics->Search_Engine_Info_By_Engine( $items->engine );
										$words              = $items->words;
									} else {
										$this_search_engine = $WP_Statistics->Search_Engine_Info( $items->referred );
										$words              = $WP_Statistics->Search_Engine_QueryString( $items->referred );
									}

									echo "<div class='log-item'>";
									echo "<div class='log-referred'>" . $words . "</div>";
									echo "<div class='log-ip'>" . date( get_option( 'date_format' ), strtotime( $items->last_counter ) ) . " - {$ip_string}</div>";
									echo "<div class='clear'></div>";
									echo "<div class='log-url'>";
									echo $map_string;

									if ( $WP_Statistics->get_option( 'geoip' ) ) {
										echo "<img src='" . plugins_url( 'wp-statistics/assets/images/flags/' . $items->location . '.png' ) . "' title='{$ISOCountryCode[$items->location]}' class='log-tools'/>";
									}

									echo "<a href='?page=" . WP_STATISTICS_OVERVIEW_PAGE . "&type=last-all-search&referred={$this_search_engine['tag']}'><img src='" . plugins_url( 'wp-statistics/assets/images/' . $this_search_engine['image'] ) . "' class='log-tools' title='" . __( $this_search_engine['name'], 'wp_statistics' ) . "'/></a>";

									if ( array_search( strtolower( $items->agent ), array(
											"chrome",
											"firefox",
											"msie",
											"opera",
											"safari"
										) ) !== false
									) {
										$agent = "<img src='" . plugins_url( 'wp-statistics/assets/images/' ) . $items->agent . ".png' class='log-tools' title='{$items->agent}'/>";
									} else {
										$agent = wp_statistics_icons( 'dashicons-editor-help', 'unknown' );
									}

									echo "<a href='?page=" . WP_STATISTICS_OVERVIEW_PAGE . "&type=last-all-visitor&agent={$items->agent}'>{$agent}</a>";

									echo $WP_Statistics->get_referrer_link( $items->referred );

									echo "</div>";
									echo "</div>";
								}
							}

							echo "</div>";
							?>
                        </div>
                    </div>

                    <div class="pagination-log">
						<?php if ( $total > 0 ) {
							echo $Pagination->display(); ?>
                            <p id="result-log"><?php echo ' ' . __( 'Page', 'wp_statistics' ) . ' ' . $Pagination->getCurrentPage() . ' ' . __( 'From', 'wp_statistics' ) . ' ' . $Pagination->getTotalPages(); ?></p>
						<?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>