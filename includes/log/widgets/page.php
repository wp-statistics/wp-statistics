<?php
/**
 * @param $pageuri
 * @param $pageid
 * @param int $days
 * @param null $chart_title
 * @param string $rangestart
 * @param string $rangeend
 */
function wp_statistics_generate_page_postbox_content( $pageuri, $pageid, $days = 20, $chart_title = null, $rangestart = '', $rangeend = '' ) {
	GLOBAL $WP_Statistics;

	if ( ! $WP_Statistics->get_option( 'pages' ) ) {
		return;
	}

	if ( $chart_title == null ) {
		$chart_title = __( 'Page Trending Stats', 'wp-statistics' );
	}

	if ( $pageuri && ! $pageid ) {
		$pageid = wp_statistics_uri_to_id( $pageuri );
	}

	$post = get_post( $pageid );
	if ( is_object( $post ) ) {
		$title = esc_html( $post->post_title );
	} else {
		$title = "";
	}

	$urlfields = "&page-id={$pageid}";
	if ( $pageuri ) {
		$urlfields .= "&page-uri={$pageuri}";
	}

	list( $daysToDisplay, $rangestart_utime, $rangeend_utime ) = wp_statistics_date_range_calculator( $days, $rangestart, $rangeend );
	$daysInThePast = round( ( time() - $rangeend_utime ) / 86400, 0 );

	for ( $i = $days; $i >= 0; $i -- ) {
		$date[] = "'" . $WP_Statistics->Current_Date( 'M j', '-' . $i ) . "'";
	}

	for ( $i = $daysToDisplay; $i >= 0; $i -- ) {
		$stats[] = wp_statistics_pages( '-' . ( $i + $daysInThePast ), $pageuri, $pageid );
	}
	?>
    <canvas id="visits-stats" height="80"></canvas>
    <script>
        var ctx = document.getElementById("visits-stats").getContext('2d');
        var ChartJs = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [<?php echo implode( ', ', $date ); ?>],
                datasets: [
					<?php if ( $WP_Statistics->get_option( 'visitors' ) ) { ?>
                    {
                        label: '<?php echo $title; ?>',
                        data: [<?php echo implode( ',', $stats ); ?>],
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
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
                    text: '<?php _e( 'Number of Hits', 'wp-statistics' ); ?>'
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