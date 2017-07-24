<script type="text/javascript">
    jQuery(document).ready(function () {
        postboxes.add_postbox_toggles(pagenow);
    });
</script>
<?php
$daysToDisplay = 20;
if ( array_key_exists( 'hitdays', $_GET ) ) {
	$daysToDisplay = intval( $_GET['hitdays'] );
}

if ( array_key_exists( 'rangestart', $_GET ) ) {
	$rangestart = $_GET['rangestart'];
} else {
	$rangestart = '';
}
if ( array_key_exists( 'rangeend', $_GET ) ) {
	$rangeend = $_GET['rangeend'];
} else {
	$rangeend = '';
}

list( $daysToDisplay, $rangestart_utime, $rangeend_utime ) = wp_statistics_date_range_calculator( $daysToDisplay, $rangestart, $rangeend );

?>
<div class="wrap">
    <h2><?php _e( 'Top Countries', 'wp_statistics' ); ?></h2>

	<?php wp_statistics_date_range_selector( WP_STATISTICS_COUNTRIES_PAGE, $daysToDisplay ); ?>

    <div class="postbox-container" id="last-log" style="width: 100%;">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
                    <div class="handlediv" title="<?php _e( 'Click to toggle', 'wp_statistics' ); ?>"><br/></div>
                    <h3 class="hndle"><span><?php _e( 'Top Countries', 'wp_statistics' ); ?></span></h3>
                    <div class="inside">
                        <table class="widefat table-stats" id="last-referrer" style="width: 100%;">
                            <tr>
                                <td><?php _e( 'Rank', 'wp_statistics' ); ?></td>
                                <td><?php _e( 'Flag', 'wp_statistics' ); ?></td>
                                <td><?php _e( 'Country', 'wp_statistics' ); ?></td>
                                <td><?php _e( 'Visitor Count', 'wp_statistics' ); ?></td>
                            </tr>

							<?php
							$ISOCountryCode = $WP_Statistics->get_country_codes();

							$result = $wpdb->get_results( "SELECT DISTINCT `location` FROM `{$wpdb->prefix}statistics_visitor`" );

							$rangestartdate = $WP_Statistics->real_current_date( 'Y-m-d', '-0', $rangestart_utime );
							$rangeenddate   = $WP_Statistics->real_current_date( 'Y-m-d', '-0', $rangeend_utime );

							foreach ( $result as $item ) {
								$Countries[ $item->location ] = $wpdb->get_var( $wpdb->prepare( "SELECT count(location) FROM `{$wpdb->prefix}statistics_visitor` WHERE location=%s AND `last_counter` BETWEEN %s AND %s", $item->location, $rangestartdate, $rangeenddate ) );
							}

							arsort( $Countries );
							$i = 0;

							foreach ( $Countries as $item => $value ) {
								$i ++;

								$item = strtoupper( $item );

								echo "<tr>";
								echo "<td style='text-align: center;'>$i</td>";
								echo "<td style='text-align: center;'><img src='" . plugins_url( 'wp-statistics/assets/images/flags/' . $item . '.png' ) . "' title='{$ISOCountryCode[$item]}'/></td>";
								echo "<td style='text-align: left; direction: ltr;'>{$ISOCountryCode[$item]}</td>";
								echo "<td style='text-align: center;'>" . number_format_i18n( $value ) . "</td>";
								echo "</tr>";
							}
							?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>