<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('.show-map').click(function () {
            alert('<?php _e( 'To be added soon', 'wp-statistics' ); ?>');
        });

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
	$result = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM `{$wpdb->prefix}statistics_visitor` WHERE `referred` LIKE %s AND referred <> '' AND `last_counter` BETWEEN %s AND %s ORDER BY `{$wpdb->prefix}statistics_visitor`.`ID` DESC",
			'%' . $referr . '%',
			$rangestartdate,
			$rangeenddate
		)
	);

	$total = count( $result );
} else {

	//Get Wordpress Domain
	$site_url = wp_parse_url( get_site_url() );
	$site_url = $site_url['scheme'] . "://" . $site_url['host'];

	//Get List referred
	$result = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT SUBSTRING_INDEX(REPLACE( REPLACE( referred, 'http://', '') , 'https://' , '') , '/', 1 ) as `domain`, count(referred) as `number` FROM {$wpdb->prefix}statistics_visitor WHERE `referred` REGEXP \"^(https?://|www\\.)[\.A-Za-z0-9\-]+\\.[a-zA-Z]{2,4}\" AND referred <> '' AND LENGTH(referred) >=12 AND `referred` NOT LIKE '{$site_url}%' AND `last_counter` BETWEEN %s AND %s GROUP BY domain ORDER BY `number` DESC",
			$rangestartdate,
			$rangeenddate
		)
	);

	//Number Total Row
	$total = count( $result );
}

?>
<div class="wrap">
	<?php WP_Statistics_Admin_Pages::show_page_title( __( 'Top Referring Sites', 'wp-statistics' ) ); ?>
    <div><?php wp_statistics_date_range_selector( WP_Statistics::$page['referrers'], $daysToDisplay, null, null, $referr_field ); ?></div>
    <div class="clear"/>

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

								foreach ( $result as $item ) {
									echo "<div class='log-item'>";
									echo "<div class='log-referred'><a href='?page=" . WP_Statistics::$page['overview'] . "&type=last-all-visitor&ip={$item->ip}'>" . wp_statistics_icons( 'dashicons-visibility', 'visibility' ) . "{$item->ip}</a></div>";
									echo "<div class='log-ip'>" . date( get_option( 'date_format' ), strtotime( $item->last_counter ) ) . " - <a href='http://www.geoiptool.com/en/?IP={$item->ip}' target='_blank'>{$item->ip}</a></div>";
									echo "<div class='clear'></div>";
									echo "<a class='show-map' title='" . __( 'Map', 'wp-statistics' ) . "'><div class='dashicons dashicons-location-alt'></div></a>";

									if ( array_search( strtolower( $item->agent ), wp_statistics_get_browser_list( 'key' ) ) !== false ) {
										$agent = "<img src='" . plugins_url( 'wp-statistics/assets/images/' ) . $item->agent . ".png' class='log-tools' title='{$item->agent}'/>";
									} else {
										$agent = "<div class='dashicons dashicons-editor-help'></div>";
									}

									echo "<div class='log-agent'><a href='" . WP_Statistics_Admin_Pages::admin_url( 'overview', array( 'type' => 'last-all-visitor', 'agent' => $item->agent ) ) . "'>{$agent}</a>";
									echo $WP_Statistics->get_referrer_link( $item->referred, 100 ) . '</div>';
									echo "</div>";
								}
							} else {
								$i = 1;
								foreach ( $result as $items ) {
									if ( $i > $start and $i <= $end ) {
										$referrer_html = $WP_Statistics->html_sanitize_referrer( $items->domain );
										echo "<div class='log-item'>";
										echo "<div class='log-referred'>{$i} - <a href='?page=" . WP_Statistics::$page['referrers'] . "&referr=" . $referrer_html . $date_args . "'>" . $referrer_html . "</a></div>";
										echo "<div class='log-ip'>" . __( 'References', 'wp-statistics' ) . ': ' . number_format_i18n( $items->number ) . '</div>';
										echo "<div class='clear'></div>";
										echo "<div class='log-url'>" . $WP_Statistics->get_referrer_link( $items->domain ) . '</div>';
										echo "</div>";
									}
									$i ++;
								}
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