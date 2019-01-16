<script type="text/javascript">
    jQuery(document).ready(function () {
        postboxes.add_postbox_toggles(pagenow);
    });
</script>
<?php

?>
<div class="wrap">
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
						$ISOCountryCode = $WP_Statistics->get_country_codes();

						$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}statistics_useronline;" );
						$total  = count( $result );

						if ( $total > 0 ) {
							// Instantiate pagination object with appropriate arguments
							$items_per_page = 10;
							$page           = isset( $_GET['pagination-page'] ) ? abs( (int) $_GET['pagination-page'] ) : 1;
							$offset         = ( $page * $items_per_page ) - $items_per_page;
							$start          = $offset;
							$end            = $offset + $items_per_page;

							echo "<div class='log-latest'>";
							$count     = 0;
							$dash_icon = wp_statistics_icons( 'dashicons-visibility', 'visibility' );

							foreach ( $result as $items ) {
								$count ++;

								if ( $count >= $start ) {
									if ( substr( $items->ip, 0, 6 ) == '#hash#' ) {
										$ip_string  = __( '#hash#', 'wp-statistics' );
										$map_string = "";
									} else {
										$ip_string  = "<a href='" . WP_Statistics_Admin_Pages::admin_url( 'overview', array( 'type' => 'last-all-visitor', 'ip' => $items->ip ) ) . "'>{$dash_icon}{$items->ip}</a>";
										$map_string = "<a class='show-map' href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank' title='" . __( 'Map', 'wp-statistics' ) . "'>" . wp_statistics_icons( 'dashicons-location-alt', 'map' ) . "</a>";
									}

									echo "<div class='log-item'>";
									echo "<div class='log-url'>";
									echo $map_string;

									if ( $WP_Statistics->get_option( 'geoip' ) ) {
										echo "<img src='" . plugins_url( 'wp-statistics/assets/images/flags/' . $items->location . '.png' ) . "' title='{$ISOCountryCode[$items->location]}' class='log-tools'/>";
									}

									if ( array_search( strtolower( $items->agent ), wp_statistics_get_browser_list( 'key' ) ) !== false ) {
										$agent = "<img src='" . plugins_url( 'wp-statistics/assets/images/' ) . $items->agent . ".png' class='log-tools' title='{$items->agent}'/>";
									} else {
										$agent = wp_statistics_icons( 'dashicons-editor-help', 'unknown' );
									}

									echo "<a href='" . WP_Statistics_Admin_Pages::admin_url( 'overview', array( 'type' => 'last-all-visitor', 'agent' => $items->agent ) ) . "'>{$agent}</a> {$items->ip}";
									echo "<br>";

									$timediff = ( $items->timestamp - $items->created );

									if ( $timediff > 3600 ) {
										$onlinefor = date( "H:i:s", ( $items->timestamp - $items->created ) );
									} else if ( $timediff > 60 ) {
										$onlinefor = "00:" . date( "i:s", ( $items->timestamp - $items->created ) );
									} else {
										$onlinefor = "00:00:" . date( "s", ( $items->timestamp - $items->created ) );
									}

									echo sprintf( __( 'Online for %s (HH:MM:SS)', 'wp-statistics' ), $onlinefor );

									echo "</div>";
									echo "</div>";
								}

								if ( $count == $start + 10 ) {
									break;
								}

							}

							echo "</div>";
						} else {
							echo "<div class='wps-center'>" . __( 'Currently there are no users online in the site.', 'wp-statistics' ) . "</div>";
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