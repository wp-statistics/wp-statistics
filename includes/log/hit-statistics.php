<script type="text/javascript">
    jQuery(document).ready(function () {
        postboxes.add_postbox_toggles(pagenow);
    });
</script>
<div class="wrap">
    <h2><?php _e( 'Hit Statistics', 'wp-statistics' ); ?></h2>
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
	wp_statistics_date_range_selector( WP_STATISTICS_HITS_PAGE, $daysToDisplay );

	$visit_total   = 0;
	$visitor_total = 0;
	$daysInThePast = (int) ( ( time() - $rangeend_utime ) / 86400 );
	$visitors      = array();
	$visits        = array();

	for ( $i = $daysToDisplay; $i >= 0; $i -- ) {
		$stat        = wp_statistics_visit( '-' . (int) ( $i + $daysInThePast ), true );
		$visit_total += $stat;
		$visits[]    = $stat;
	}

	for ( $i = $daysToDisplay; $i >= 0; $i -- ) {
		$stat          = wp_statistics_visitor( '-' . (int) ( $i + $daysInThePast ), true );
		$visitor_total += $stat;
		$visitors[]    = $stat;
	}

	for ( $i = $daysToDisplay; $i >= 0; $i -- ) {
		$date[] = "'" . $WP_Statistics->Current_Date( 'M j', '-' . $i ) . "'";
	}
	?>
    <div class="postbox-container" id="last-log">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
					<?php $paneltitle = __( 'Hits Statistics Chart', 'wp-statistics' ); ?>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf( __( 'Toggle panel: %s', 'wp-statistics' ), $paneltitle ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></span></h2>
                    <div class="inside">
                        <canvas id="hit-stats" height="80"></canvas>
                        <script>
                            var ctx = document.getElementById("hit-stats").getContext('2d');
                            var ChartJs = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: [<?php echo implode( ', ', $date ); ?>],
                                    datasets: [
										<?php if ( $WP_Statistics->get_option( 'visitors' ) ) { ?>
                                        {
                                            label: '<?php _e( 'Visitors', 'wp-statistics' ); ?>',
                                            data: [<?php echo implode( ',', $visitors ); ?>],
                                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                            borderColor: 'rgba(255, 99, 132, 1)',
                                            borderWidth: 1,
                                            fill: true,
                                        },
										<?php } ?>
										<?php if ( $WP_Statistics->get_option( 'visits' ) ) { ?>
                                        {
                                            label: '<?php _e( 'Visits', 'wp-statistics' ); ?>',
                                            data: [<?php echo implode( ',', $visits ); ?>],
                                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                            borderColor: 'rgba(54, 162, 235, 1)',
                                            borderWidth: 1,
                                            fill: true,
                                        },
										<?php } ?>
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
                                <th class="th-center"><?php _e( 'Visits', 'wp-statistics' ); ?></th>
                                <th class="th-center"><?php _e( 'Visitors', 'wp-statistics' ); ?></th>
                            </tr>

                            <tr>
                                <th><?php _e( 'Chart Total', 'wp-statistics' ); ?>:</th>
                                <th class="th-center"><span><?php echo number_format_i18n( $visit_total ); ?></span>
                                </th>
                                <th class="th-center"><span><?php echo number_format_i18n( $visitor_total ); ?></span>
                                </th>
                            </tr>

                            <tr>
                                <th><?php _e( 'All Time Total', 'wp-statistics' ); ?>:</th>
                                <th class="th-center">
                                    <span><?php echo number_format_i18n( wp_statistics_visit( 'total' ) ); ?></span>
                                </th>
                                <th class="th-center">
                                    <span><?php echo number_format_i18n( wp_statistics_visitor( 'total', null, true ) ); ?></span>
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
