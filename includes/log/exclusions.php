<script type="text/javascript">
    jQuery(document).ready(function () {
        postboxes.add_postbox_toggles(pagenow);
    });
</script>
<?php
if ( $WP_Statistics->get_option( 'record_exclusions' ) != 1 ) {
	echo "<div class='updated settings-error'><p><strong>" . __( 'Attention: Exclusion are not currently set to be recorded, the results below may not reflect current statistics!', 'wp_statistics' ) . "</strong></p></div>";
}

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

$total_stats = $WP_Statistics->get_option( 'chart_totals' );

$excluded_reasons          = array(
	'Robot',
	'Browscap',
	'IP Match',
	'Self Referral',
	'Login Page',
	'Admin Page',
	'User Role',
	'GeoIP',
	'Hostname',
	'Robot Threshold',
	'Honey Pot',
	'Feeds',
	'Excluded URL',
	'404 Pages',
	'Referrer Spam',
	'AJAX Request'
);
$excluded_reason_tags      = array(
	'Robot'           => 'robot',
	'Browscap'        => 'browscap',
	'IP Match'        => 'ipmatch',
	'Self Referral'   => 'selfreferral',
	'Login Page'      => 'loginpage',
	'Admin Page'      => 'adminpage',
	'User Role'       => 'userrole',
	'Total'           => 'total',
	'GeoIP'           => 'geoip',
	'Hostname'        => 'hostname',
	'Robot Threshold' => 'robot_threshold',
	'Honey Pot'       => 'honeypot',
	'Feeds'           => 'feed',
	'Excluded URL'    => 'excluded_url',
	'404 Pages'       => 'excluded_404s',
	'Referrer Spam'   => 'referrer_spam',
	'AJAX Request'    => 'ajax'
);
$excluded_reason_db        = array(
	'Robot'           => 'robot',
	'Browscap'        => 'browscap',
	'IP Match'        => 'ip match',
	'Self Referral'   => 'self referral',
	'Login Page'      => 'login page',
	'Admin Page'      => 'admin page',
	'User Role'       => 'user role',
	'Total'           => 'total',
	'GeoIP'           => 'geoip',
	'Hostname'        => 'hostname',
	'Robot Threshold' => 'robot_threshold',
	'Honey Pot'       => 'honeypot',
	'Feeds'           => 'feed',
	'Excluded URL'    => 'excluded url',
	'404 Pages'       => '404',
	'Referrer Spam'   => 'referrer_spam',
	'AJAX Request'    => 'ajax'
);
$excluded_reason_translate = array( 'Robot'           => json_encode( __( 'Robot', 'wp_statistics' ) ),
                                    'Browscap'        => json_encode( __( 'Browscap', 'wp_statistics' ) ),
                                    'IP Match'        => json_encode( __( 'IP Match', 'wp_statistics' ) ),
                                    'Self Referral'   => json_encode( __( 'Self Referral', 'wp_statistics' ) ),
                                    'Login Page'      => json_encode( __( 'Login Page', 'wp_statistics' ) ),
                                    'Admin Page'      => json_encode( __( 'Admin Page', 'wp_statistics' ) ),
                                    'User Role'       => json_encode( __( 'User Role', 'wp_statistics' ) ),
                                    'Total'           => json_encode( __( 'Total', 'wp_statistics' ) ),
                                    'GeoIP'           => json_encode( __( 'GeoIP', 'wp_statistics' ) ),
                                    'Hostname'        => json_encode( __( 'Hostname', 'wp_statistics' ) ),
                                    'Robot Threshold' => json_encode( __( 'Robot Threshold', 'wp_statistics' ) ),
                                    'Honey Pot'       => json_encode( __( 'Honey Pot', 'wp_statistics' ) ),
                                    'Feeds'           => json_encode( __( 'Feeds', 'wp_statistics' ) ),
                                    'Excluded URL'    => json_encode( __( 'Excluded URL', 'wp_statistics' ) ),
                                    '404 Pages'       => json_encode( __( '404 Pages', 'wp_statistics' ) ),
                                    'Referrer Spam'   => json_encode( __( 'Referrer Spam', 'wp_statistics' ) ),
                                    'AJAX Request'    => json_encode( __( 'AJAX Request', 'wp_statistics' ) )
);
$excluded_results          = array( 'Total' => array() );
$excluded_total            = 0;

foreach ( $excluded_reasons as $reason ) {

	// The reasons array above is used both for display and internal purposes.  Internally the values are all lower case but the array
	// is created with mixed case so it looks nice to the user.  Therefore we have to convert it to lower case here.
	$thisreason = $excluded_reason_db[ $reason ];

	for ( $i = $daysToDisplay; $i >= 0; $i -- ) {

		// We're looping through the days backwards, so let's fine out what date we want to look at.
		$thisdate = $WP_Statistics->real_current_date( 'Y-m-d', '-' . $i, $rangeend_utime );

		// Create the SQL query string to get the data.
		$query = $wpdb->prepare( "SELECT count FROM {$wpdb->prefix}statistics_exclusions WHERE reason = %s AND date = %s", $thisreason, $thisdate );

		// Execute the query.
		$excluded_results[ $reason ][ $i ] = $wpdb->get_var( $query );

		// If we're returned an error or a FALSE value, then let's make sure it's set to a numerical 0.
		if ( $excluded_results[ $reason ][ $i ] < 1 ) {
			$excluded_results[ $reason ][ $i ] = 0;
		}

		// Make sure to initialize the results so we don't get warnings when WP_DEBUG is enabled.
		if ( ! array_key_exists( $i, $excluded_results['Total'] ) ) {
			$excluded_results['Total'][ $i ] = 0;
		}

		// We're totalling things up here for use later.
		$excluded_results['Total'][ $i ] += $excluded_results[ $reason ][ $i ];
		$excluded_total                  += $excluded_results[ $reason ][ $i ];
	}
}

$excuded_all_time = $wpdb->get_var( "SELECT SUM(count) FROM {$wpdb->prefix}statistics_exclusions" );

// If the chart totals is enabled, cheat a little and just add another reason category to the list so it gets generated later.
if ( $total_stats == 1 ) {
	$excluded_reasons[] = 'Total';
}
?>
<div class="wrap">
    <h2><?php _e( 'Exclusions Statistics', 'wp_statistics' ); ?></h2>

	<?php wp_statistics_date_range_selector( WP_STATISTICS_EXCLUSIONS_PAGE, $daysToDisplay ); ?>

    <div class="postbox-container" style="width: 100%; float: left; margin-right:20px">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
                    <div class="handlediv" title="<?php _e( 'Click to toggle', 'wp_statistics' ); ?>"><br/></div>
                    <h3 class="hndle"><span><?php _e( 'Exclusions Statistical Chart', 'wp_statistics' ); ?></span></h3>
                    <div class="inside">
                        <script type="text/javascript">
                            var visit_chart;
                            jQuery(document).ready(function () {
								<?php
								foreach ( $excluded_reasons as $reason ) {

									echo "var excluded_data_line_" . $excluded_reason_tags[ $reason ] . " = [";

									for ( $i = $daysToDisplay; $i >= 0; $i -- ) {
										echo "['" . $WP_Statistics->Real_Current_Date( 'Y-m-d', '-' . $i, $rangeend_utime ) . "'," . $excluded_results[ $reason ][ $i ] . "], ";
									}

									echo "];\n";
								}

								$tickInterval = round( $daysToDisplay / 20, 0 );
								if ( $tickInterval < 1 ) {
									$tickInterval = 1;
								}
								?>
                                visit_chart = jQuery.jqplot('exclusion-stats', [<?php foreach ( $excluded_reasons as $reason ) {
									echo "excluded_data_line_" . $excluded_reason_tags[ $reason ] . ", ";
								} ?>], {
                                    title: {
                                        text: '<b>' + <?php echo json_encode( __( 'Excluded hits in the last', 'wp_statistics' ) . ' ' . $daysToDisplay . ' ' . __( 'days', 'wp_statistics' ) ); ?> +'</b>',
                                        fontSize: '12px',
                                        fontFamily: 'Tahoma',
                                        textColor: '#000000',
                                    },
                                    axes: {
                                        xaxis: {
                                            min: '<?php echo $WP_Statistics->Real_Current_Date( 'Y-m-d', '-' . $daysToDisplay, $rangeend_utime );?>',
                                            max: '<?php echo $WP_Statistics->Real_Current_Date( 'Y-m-d', '-0', $rangeend_utime );?>',
                                            tickInterval: '<?php echo $tickInterval?> day',
                                            renderer: jQuery.jqplot.DateAxisRenderer,
                                            tickRenderer: jQuery.jqplot.CanvasAxisTickRenderer,
                                            tickOptions: {
                                                angle: -45,
                                                formatString: '%b %#d',
                                                showGridline: false,
                                            },
                                        },
                                        yaxis: {
                                            min: 0,
                                            padMin: 1.0,
                                            label: <?php echo json_encode( __( 'Number of excluded hits', 'wp_statistics' ) ); ?>,
                                            labelRenderer: jQuery.jqplot.CanvasAxisLabelRenderer,
                                            labelOptions: {
                                                angle: -90,
                                                fontSize: '12px',
                                                fontFamily: 'Tahoma',
                                                fontWeight: 'bold',
                                            },
                                        }
                                    },
                                    legend: {
                                        show: true,
                                        location: 's',
                                        placement: 'outsideGrid',
                                        labels: [<?php foreach ( $excluded_reasons as $reason ) {
											echo $excluded_reason_translate[ $reason ] . ", ";
										} ?>],
                                        renderer: jQuery.jqplot.EnhancedLegendRenderer,
                                        rendererOptions: {
                                            numberColumns: <?php echo count( $excluded_reasons ) + 1; ?>,
                                            disableIEFading: false,
                                            border: 'none',
                                        },
                                    },
                                    highlighter: {
                                        show: true,
                                        bringSeriesToFront: true,
                                        tooltipAxes: 'xy',
                                        formatString: '%s:&nbsp;<b>%i</b>&nbsp;',
                                        tooltipContentEditor: tooltipContentEditor,
                                    },
                                    grid: {
                                        drawGridlines: true,
                                        borderColor: 'transparent',
                                        shadow: false,
                                        drawBorder: false,
                                        shadowColor: 'transparent'
                                    },
                                });

                                function tooltipContentEditor(str, seriesIndex, pointIndex, plot) {
                                    // display series_label, x-axis_tick, y-axis value
                                    return plot.legend.labels[seriesIndex] + ", " + str;
                                    ;
                                }

                                jQuery(window).resize(function () {
                                    JQPlotExclusionChartLengendClickRedraw()
                                });

                                function JQPlotExclusionChartLengendClickRedraw() {
                                    visit_chart.replot({resetAxes: ['yaxis']});
                                    jQuery('div[id="exclusion-stats"] .jqplot-table-legend').click(function () {
                                        JQPlotExclusionChartLengendClickRedraw();
                                    });
                                }

                                jQuery('div[id="exclusion-stats"] .jqplot-table-legend').click(function () {
                                    JQPlotExclusionChartLengendClickRedraw()
                                });

                            });
                        </script>

                        <div id="exclusion-stats" style="height:500px;"></div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="postbox-container" style="width: 100%; float: left; margin-right:20px">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
                    <div class="handlediv" title="<?php _e( 'Click to toggle', 'wp_statistics' ); ?>"><br/></div>
                    <h3 class="hndle"><span><?php _e( 'Hits Statistics Summary', 'wp_statistics' ); ?></span></h3>
                    <div class="inside">
                        <table width="auto" class="widefat table-stats" id="summary-stats">
                            <tbody>
                            <tr>
                                <th></th>
                                <th class="th-center"><?php _e( 'Exclusions', 'wp_statistics' ); ?></th>
                            </tr>

                            <tr>
                                <th><?php _e( 'Chart Total', 'wp_statistics' ); ?>:</th>
                                <th class="th-center"><span><?php echo number_format_i18n( $excluded_total ); ?></span>
                                </th>
                            </tr>

                            <tr>
                                <th><?php _e( 'All Time Total', 'wp_statistics' ); ?>:</th>
                                <th class="th-center">
                                    <span><?php echo number_format_i18n( $excuded_all_time ); ?></span>
                                </th>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
