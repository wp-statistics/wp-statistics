<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('.show-map').click(function () {
            alert('<?php _e( 'To be added soon', 'wp-statistics' ); ?>');
        });

        postboxes.add_postbox_toggles(pagenow);
    });
</script>
<?php
$date_args     = '';
$daysToDisplay = 20;
if ( array_key_exists( 'hitdays', $_GET ) ) {
	$daysToDisplay = intval( esc_attr($_GET['hitdays']) );
	$date_args     .= '&hitdays=' . $daysToDisplay;
}

if ( array_key_exists( 'rangestart', $_GET ) ) {
	$rangestart = esc_attr($_GET['rangestart']);
	$date_args  .= '&rangestart=' . $rangestart;
} else {
	$rangestart = '';
}

if ( array_key_exists( 'rangeend', $_GET ) ) {
	$rangeend  = esc_attr($_GET['rangeend']);
	$date_args .= '&rangeend=' . $rangeend;
} else {
	$rangeend = '';
}

list( $daysToDisplay, $rangestart_utime, $rangeend_utime ) = wp_statistics_date_range_calculator( $daysToDisplay, $rangestart, $rangeend );

$rangestartdate = $WP_Statistics->real_current_date( 'Y-m-d', '-0', $rangestart_utime );
$rangeenddate   = $WP_Statistics->real_current_date( 'Y-m-d', '-0', $rangeend_utime );

if ( array_key_exists( 'referr', $_GET ) ) {
	$referr       = $_GET['referr'];
	$title        = $_GET['referr'];
	$referr_field = '&referr=' . $referr;
} else {
	$referr       = '';
	$referr_field = null;
}

$get_urls = array();
$total    = 0;

if ( $referr ) {
	$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}statistics_visitor` WHERE `referred` LIKE %s AND referred <> '' AND `last_counter` BETWEEN %s AND %s ORDER BY `{$wpdb->prefix}statistics_visitor`.`ID` DESC", '%' . $referr . '%', $rangestartdate, $rangeenddate ) );

	$total = count( $result );
} else {
	$result = $wpdb->get_results( $wpdb->prepare( "SELECT referred FROM {$wpdb->prefix}statistics_visitor WHERE referred <> '' AND `last_counter` BETWEEN %s AND %s", $rangestartdate, $rangeenddate ) );

	$urls = array();
	foreach ( $result as $item ) {

		$url = parse_url( $item->referred );

		if ( empty( $url['host'] ) || stristr( get_bloginfo( 'url' ), $url['host'] ) ) {
			continue;
		}

		$urls[] = $url['scheme'] . '://' . $url['host'];
	}

	$get_urls = array_count_values( $urls );

	$total = count( $get_urls );
}

?>
<div class="wrap">
    <h2><?php _e( 'Top Referring Sites', 'wp-statistics' ); ?></h2>

    <div><?php wp_statistics_date_range_selector( WP_STATISTICS_REFERRERS_PAGE, $daysToDisplay, null, null, $referr_field ); ?></div>

    <div class="clear"/>

    <ul class="subsubsub">
		<?php if ( $referr ) { ?>
            <li class="all"><a <?php if ( ! $referr ) {
					echo 'class="current"';
				} ?>href="?page=<?php echo WP_STATISTICS_REFERRERS_PAGE . $date_args; ?>"><?php _e( 'All', 'wp-statistics' ); ?></a>
            </li>
            |
            <li>
                <a class="current" href="?page=<?php echo WP_STATISTICS_REFERRERS_PAGE; ?>&referr=<?php echo $WP_Statistics->html_sanitize_referrer( $referr ) . $date_args; ?>"> <?php echo htmlentities( $title, ENT_QUOTES ); ?>
                    <span class="count">(<?php echo $total; ?>)</span></a></li>
		<?php } else { ?>
            <li class="all"><a <?php if ( ! $referr ) {
					echo 'class="current"';
				} ?>href="?page=<?php echo WP_STATISTICS_REFERRERS_PAGE . $date_args; ?>"><?php _e( 'All', 'wp-statistics' ); ?>
                    <span class="count">(<?php echo $total; ?>)</span></a></li>
		<?php } ?>
    </ul>
    <div class="postbox-container" id="last-log">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
                    <?php if ( $referr ) {
                        $paneltitle = sprintf( __( 'Referring site: %s', 'wp-statistics' ), $WP_Statistics->html_sanitize_referrer( $referr ) );
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
							$pagesPerSection = 10;
							$options         = array( 25, "All" );
							$stylePageOff    = "pageOff";
							$stylePageOn     = "pageOn";
							$styleErrors     = "paginationErrors";
							$styleSelect     = "paginationSelect";

							$Pagination = new WP_Statistics_Pagination( $total, $pagesPerSection, $options, false, $stylePageOff, $stylePageOn, $styleErrors, $styleSelect );

							$start = $Pagination->getEntryStart();
							$end   = $Pagination->getEntryEnd();

							if ( $referr ) {
								if ( $WP_Statistics->get_option( 'search_converted' ) ) {
									$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}statistics_search` INNER JOIN `{$wpdb->prefix}statistics_visitor` on {$wpdb->prefix}statistics_search.`visitor` = {$wpdb->prefix}statistics_visitor.`ID` WHERE `host` = %s AND {$wpdb->prefix}statistics_visitor.`last_counter` BETWEEN %s AND %s ORDER BY `{$wpdb->prefix}statistics_search`.`ID` DESC LIMIT %d, %d", $referr, $rangestartdate, $rangeenddate, $start, $end ) );
								}

								foreach ( $result as $item ) {
									echo "<div class='log-item'>";
									echo "<div class='log-referred'><a href='?page=" . WP_STATISTICS_OVERVIEW_PAGE . "&type=last-all-visitor&ip={$item->ip}'>" . wp_statistics_icons( 'dashicons-visibility', 'visibility' ) . "{$item->ip}</a></div>";
									echo "<div class='log-ip'>" . date( get_option( 'date_format' ), strtotime( $item->last_counter ) ) . " - <a href='http://www.geoiptool.com/en/?IP={$item->ip}' target='_blank'>{$item->ip}</a></div>";
									echo "<div class='clear'></div>";
									echo "<a class='show-map' title='" . __( 'Map', 'wp-statistics' ) . "'><div class='dashicons dashicons-location-alt'></div></a>";

									if ( array_search( strtolower( $item->agent ), array(
											'chrome',
											'firefox',
											'msie',
											'opera',
											'safari'
										) ) !== false
									) {
										$agent = "<img src='" . plugins_url( 'wp-statistics/assets/images/' ) . $item->agent . ".png' class='log-tools' title='{$item->agent}'/>";
									} else {
										$agent = "<div class='dashicons dashicons-editor-help'></div>";
									}

									echo "<div class='log-agent'><a href='?page=" . WP_STATISTICS_OVERVIEW_PAGE . "&type=last-all-visitor&agent={$item->agent}'>{$agent}</a>";
									echo $WP_Statistics->get_referrer_link( $item->referred, 100 ) . '</div>';
									echo "</div>";
								}
							} else {
								arsort( $get_urls );
								$get_urls = array_slice( $get_urls, $start, $end );

								$i = $start;
								foreach ( $get_urls as $items => $value ) {
									$i ++;
									$referrer_html = $WP_Statistics->html_sanitize_referrer( $items );
									$referrer_html = parse_url($referrer_html)['host'];
									echo "<div class='log-item'>";
									echo "<div class='log-referred'>{$i} - <a href='?page=" . WP_STATISTICS_REFERRERS_PAGE . "&referr=" . $referrer_html . $date_args . "'>" . $referrer_html . "</a></div>";
									echo "<div class='log-ip'>" . __( 'References', 'wp-statistics' ) . ': ' . number_format_i18n( $value ) . '</div>';
									echo "<div class='clear'></div>";
									echo "<div class='log-url'>" . $WP_Statistics->get_referrer_link( $items, 100 ) . '</div>';
									echo "</div>";
								}
							}
						}

						echo '</div>';
						?>
                    </div>
                </div>

                <div class="pagination-log">
					<?php if ( $total > 0 ) {
						echo $Pagination->display(); ?>
                        <p id="result-log"><?php printf( __( 'Page %1$s of %2$s', 'wp-statistics' ), $Pagination->getCurrentPage(), $Pagination->getTotalPages() ); ?></p>
					<?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
