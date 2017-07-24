<script type="text/javascript">
    jQuery(document).ready(function () {
        postboxes.add_postbox_toggles(pagenow);
    });
</script>
<?php

?>
<div class="wrap">
    <h2><?php _e( 'Online Users', 'wp_statistics' ); ?></h2>
    <div class="postbox-container" id="last-log">
        <div class="metabox-holder">
            <div class="meta-box-sortables">

                <div class="postbox">
                    <div class="handlediv" title="<?php _e( 'Click to toggle', 'wp_statistics' ); ?>"><br/></div>
                    <h3 class="hndle"><span><?php _e( 'Online Users', 'wp_statistics' ); ?></span></h3>
                    <div class="inside">
						<?php
						$ISOCountryCode = $WP_Statistics->get_country_codes();

						$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}statistics_useronline;" );
						$total  = count( $result );

						if ( $total > 0 ) {
							// Instantiate pagination object with appropriate arguments
							$pagesPerSection = 10;
							$options         = 10;
							$stylePageOff    = "pageOff";
							$stylePageOn     = "pageOn";
							$styleErrors     = "paginationErrors";
							$styleSelect     = "paginationSelect";

							$Pagination = new WP_Statistics_Pagination( $total, $pagesPerSection, $options, false, $stylePageOff, $stylePageOn, $styleErrors, $styleSelect );

							$start = $Pagination->getEntryStart();
							$end   = $Pagination->getEntryEnd();

							echo "<div class='log-latest'>";
							$count = 0;

							$dash_icon = wp_statistics_icons( 'dashicons-visibility', 'visibility' );

							foreach ( $result as $items ) {
								$count ++;

								if ( $count >= $start ) {
									if ( substr( $items->ip, 0, 6 ) == '#hash#' ) {
										$ip_string  = __( '#hash#', 'wp_statistics' );
										$map_string = "";
									} else {
										$ip_string  = "<a href='?page=" . WP_STATISTICS_OVERVIEW_PAGE . "&type=last-all-visitor&ip={$items->ip}'>{$dash_icon}{$items->ip}</a>";
										$map_string = "<a class='show-map' href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank' title='" . __( 'Map', 'wp_statistics' ) . "'>" . wp_statistics_icons( 'dashicons-location-alt', 'map' ) . "</a>";
									}

									echo "<div class='log-item'>";
									echo "<div class='log-url'>";
									echo $map_string;

									if ( $WP_Statistics->get_option( 'geoip' ) ) {
										echo "<img src='" . plugins_url( 'wp-statistics/assets/images/flags/' . $items->location . '.png' ) . "' title='{$ISOCountryCode[$items->location]}' class='log-tools'/>";
									}

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

									echo "<a href='?page=" . WP_STATISTICS_OVERVIEW_PAGE . "&type=last-all-visitor&agent={$items->agent}'>{$agent}</a> {$items->ip}";
									echo "<br>";

									echo __( 'Online for ', 'wp_statistics' );
									$timediff = ( $items->timestamp - $items->created );

									if ( $timediff > 3600 ) {
										echo date( "H:i:s", ( $items->timestamp - $items->created ) );
									} else if ( $timediff > 60 ) {
										echo "00:" . date( "i:s", ( $items->timestamp - $items->created ) );
									} else {
										echo "00:00:" . date( "s", ( $items->timestamp - $items->created ) );
									}

									echo " (HH:MM:SS)";

									echo "</div>";
									echo "</div>";
								}

								if ( $count == $start + 10 ) {
									break;
								}

							}

							echo "</div>";
						} else {
							echo "<div class='wps-center'>" . __( 'Currently there are no users online in the site.', 'wp_statistics' ) . "</div>";
						}
						?>
                    </div>
                </div>

				<?php if ( $total > 0 ) { ?>
                    <div class="pagination-log">
						<?php echo $Pagination->display(); ?>
                        <p id="result-log"><?php echo ' ' . __( 'Page', 'wp_statistics' ) . ' ' . $Pagination->getCurrentPage() . ' ' . __( 'From', 'wp_statistics' ) . ' ' . $Pagination->getTotalPages(); ?></p>
                    </div>
				<?php } ?>
            </div>
        </div>
    </div>
</div>