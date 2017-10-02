<script type="text/javascript">
    jQuery(document).ready(function () {
        postboxes.add_postbox_toggles(pagenow);
    });
</script>
<?php
if ( $WP_Statistics->get_option( 'record_exclusions' ) != 1 ) {
	echo "<div class='updated settings-error'><p><strong>" . __( 'Attention: Exclusion are not currently set to be recorded, the results below may not reflect current statistics!', 'wp-statistics' ) . "</strong></p></div>";
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
$excluded_reason_translate = array(
	'Robot'           => json_encode( __( 'Robot', 'wp-statistics' ) ),
	'Browscap'        => json_encode( __( 'Browscap', 'wp-statistics' ) ),
	'IP Match'        => json_encode( __( 'IP Match', 'wp-statistics' ) ),
	'Self Referral'   => json_encode( __( 'Self Referral', 'wp-statistics' ) ),
	'Login Page'      => json_encode( __( 'Login Page', 'wp-statistics' ) ),
	'Admin Page'      => json_encode( __( 'Admin Page', 'wp-statistics' ) ),
	'User Role'       => json_encode( __( 'User Role', 'wp-statistics' ) ),
	'Total'           => json_encode( __( 'Total', 'wp-statistics' ) ),
	'GeoIP'           => json_encode( __( 'GeoIP', 'wp-statistics' ) ),
	'Hostname'        => json_encode( __( 'Hostname', 'wp-statistics' ) ),
	'Robot Threshold' => json_encode( __( 'Robot Threshold', 'wp-statistics' ) ),
	'Honey Pot'       => json_encode( __( 'Honey Pot', 'wp-statistics' ) ),
	'Feeds'           => json_encode( __( 'Feeds', 'wp-statistics' ) ),
	'Excluded URL'    => json_encode( __( 'Excluded URL', 'wp-statistics' ) ),
	'404 Pages'       => json_encode( __( '404 Pages', 'wp-statistics' ) ),
	'Referrer Spam'   => json_encode( __( 'Referrer Spam', 'wp-statistics' ) ),
	'AJAX Request'    => json_encode( __( 'AJAX Request', 'wp-statistics' ) )
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

for ( $i = $daysToDisplay; $i >= 0; $i -- ) {
	$date[] = "'" . $WP_Statistics->Current_Date( 'M j', '-' . $i ) . "'";
}

$stats = array();
foreach ( $excluded_reasons as $reason ) {
	for ( $i = $daysToDisplay; $i >= 0; $i -- ) {
		$stats[ $reason ][] = $excluded_results[ $reason ][ $i ];
	}
}
?>
<div class="wrap">
    <h2><?php _e( 'Exclusions Statistics', 'wp-statistics' ); ?></h2>
	<?php wp_statistics_date_range_selector( WP_STATISTICS_EXCLUSIONS_PAGE, $daysToDisplay ); ?>
    <div class="postbox-container" id="last-log">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
					<?php $paneltitle = __( 'Exclusions Statistical Chart', 'wp-statistics' ); ?>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf( __( 'Toggle panel: %s', 'wp-statistics' ), $paneltitle ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></span></h2>
                    <div class="inside">
                        <div class="inside">
                            <canvas id="hit-stats" height="80"></canvas>
                            <script type='text/javascript' src='<?php echo WP_STATISTICS_PLUGIN_DIR; ?>/assets/js/Chart.bundle.min.js'></script>
                            <script>
                                var ctx = document.getElementById("hit-stats").getContext('2d');
                                var ChartJs = new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: [<?php echo implode( ', ', $date ); ?>],
                                        datasets: [
											<?php foreach ($stats as $key => $value) : $i ++; ?>
                                            {
                                                label: '<?php echo $key; ?>',
                                                data: [<?php echo implode( ',', $value ); ?>],
                                                backgroundColor: <?php echo wp_statistics_generate_rgba_color( $i, '0.2' ); ?>,
                                                borderColor: <?php echo wp_statistics_generate_rgba_color( $i, '1' ); ?>,
                                                borderWidth: 1,
                                                fill: true,
                                            },
											<?php endforeach; ?>
                                        ]
                                    },
                                    options: {
                                        responsive: true,
                                        legend: {
                                            position: 'bottom',
                                        },
                                        title: {
                                            display: true,
                                            text: '<?php echo sprintf( __( 'Hits in the last %s days', 'wp-statistics' ), $daysToDisplay ); ?>'
                                        },
                                        tooltips: {
                                            mode: 'index',
                                            intersect: false,
                                        },
                                        scales: {
                                            yAxes: [{
                                                ticks: {
                                                    beginAtZero: true
                                                }
                                            }]
                                        }
                                    }
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="postbox-container" style="width: 100%; float: left; margin-right:20px">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
					<?php $paneltitle = __( 'Hits Statistics Summary', 'wp-statistics' ); ?>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf( __( 'Toggle panel: %s', 'wp-statistics' ), $paneltitle ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></span></h2>
                    <div class="inside">
                        <table width="auto" class="widefat table-stats" id="summary-stats">
                            <tbody>
                            <tr>
                                <th></th>
                                <th class="th-center"><?php _e( 'Exclusions', 'wp-statistics' ); ?></th>
                            </tr>

                            <tr>
                                <th><?php _e( 'Chart Total', 'wp-statistics' ); ?>:</th>
                                <th class="th-center"><span><?php echo number_format_i18n( $excluded_total ); ?></span>
                                </th>
                            </tr>

                            <tr>
                                <th><?php _e( 'All Time Total', 'wp-statistics' ); ?>:</th>
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