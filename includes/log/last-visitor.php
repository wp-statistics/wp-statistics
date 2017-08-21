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

$_get = esc_attr($_get);
$total_visitor = $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->prefix}statistics_visitor`" );

if ( $_get != '%' ) {
	$total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}statistics_visitor` WHERE `{$_var}` LIKE %s", $_get ) );
} else {
	$total = $total_visitor;
}

?>
<div class="wrap">
    <h2><?php _e( 'Recent Visitors', 'wp-statistics' ); ?></h2>
    <ul class="subsubsub">
        <li class="all"><a <?php if ( $_get == '%' ) {
				echo 'class="current"';
			} ?>href="?page=<?php echo WP_STATISTICS_VISITORS_PAGE; ?>"><?php _e( 'All', 'wp-statistics' ); ?>
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
					echo $spacer . "<li><a " . $current . "href='?page=" . WP_STATISTICS_VISITORS_PAGE . "&agent=" . $Browser . "'> " . __( $Browser, 'wp-statistics' ) . " <span class='count'>(" . number_format_i18n( wp_statistics_useragent( $Browser ) ) . ")</span></a></li>";
				}
			} else {
				if ( $_get != '%' ) {
					$current = 'class="current" ';
				} else {
					$current = "";
				}
				echo $spacer . "<li><a {$current} href='?page=" . WP_STATISTICS_VISITORS_PAGE . "&{$_var}={$_get}'>{$title} <span class='count'>({$total})</span></a></li>";
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
						if ( $_get != '%' ) {
							$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}statistics_visitor` WHERE `{$_var}` LIKE %s ORDER BY `{$wpdb->prefix}statistics_visitor`.`ID` DESC  LIMIT {$start}, {$end}", $_get ) );
						} else {
							$result = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}statistics_visitor` ORDER BY `{$wpdb->prefix}statistics_visitor`.`ID` DESC  LIMIT {$start}, {$end}" );
						}

						// Check to see if User Agent logging is enabled.
						$DisplayUA = $WP_Statistics->get_option( "store_ua" );

						echo "<div class='log-latest'>";

						$dash_icon = wp_statistics_icons( 'dashicons-visibility', 'visibility' );

						foreach ( $result as $items ) {
							if ( substr( $items->ip, 0, 6 ) == '#hash#' ) {
								$ip_string  = __( '#hash#', 'wp-statistics' );
								$map_string = "";
							} else {
								$ip_string  = "<a href='?page=" . WP_STATISTICS_VISITORS_PAGE . "&ip={$items->ip}'>{$dash_icon}{$items->ip}</a>";
								$map_string = "<a class='show-map' href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank' title='" . __( 'Map', 'wp-statistics' ) . "'>" . wp_statistics_icons( 'dashicons-location-alt', 'map' ) . "</a>";
							}

							echo "<div class='log-item'>";
							echo "<div class='log-referred'>{$ip_string}</div>";
							echo "<div class='log-ip'>" . date( get_option( 'date_format' ), strtotime( $items->last_counter ) ) . "</div>";
							echo "<div class='clear'></div>";
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

							echo "<a href='?page=" . WP_STATISTICS_VISITORS_PAGE . "&agent={$items->agent}'>{$agent}</a>";

							echo $WP_Statistics->get_referrer_link( $items->referred );

							echo "</div>";
							echo "</div>";
						}

						echo "</div>";
						?>
                    </div>
                </div>

                <div class="pagination-log">
					<?php echo $Pagination->display(); ?>
                    <p id="result-log"><?php printf( __( 'Page %1$s of %2$s', 'wp-statistics' ), $Pagination->getCurrentPage(), $Pagination->getTotalPages() ); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>