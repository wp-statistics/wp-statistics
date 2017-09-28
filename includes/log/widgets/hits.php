<?php
function wp_statistics_generate_hits_postbox_content( $size = '300', $days = 20 ) {
	global $wpdb, $WP_Statistics;
	$id       = 'visits-stats-' . rand( 111, 999 );
	$visitors = array();
	$visits   = array();

	for ( $i = $days; $i >= 0; $i -- ) {
		$date[] = "'" . $WP_Statistics->Current_Date( 'M j', '-' . $i ) . "'";
	}

	for ( $i = $days; $i >= 0; $i -- ) {
		$visitors[] = wp_statistics_visitor( '-' . $i, true );
	}

	for ( $i = $days; $i >= 0; $i -- ) {
		$visits[] = wp_statistics_visit( '-' . $i, true );
	}
	?>
    <canvas id="<?php echo $id; ?>" height="<?php echo $size; ?>"></canvas>
    <script>
        var ctx = document.getElementById("<?php echo $id; ?>").getContext('2d');
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
                animation: {
                    duration: 0,
                },
                title: {
                    display: true,
                    text: '<?php echo sprintf( __( 'Hits in the last %s days', 'wp-statistics' ), $days ); ?>'
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
	<?php
}
