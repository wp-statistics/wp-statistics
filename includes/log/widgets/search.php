<?php
function wp_statistics_generate_search_postbox_content( $search_engines, $size = "300", $days = 20 ) {
	global $wpdb, $WP_Statistics;
	$id          = 'search-stats-' . rand( 111, 999 );
	$total_stats = $WP_Statistics->get_option( 'chart_totals' );
	$date        = array();
	$stats       = array();
	$total_daily = array();

	for ( $i = $days; $i >= 0; $i -- ) {
		$date[] = "'" . $WP_Statistics->Current_Date( 'M j', '-' . $i ) . "'";
	}

	foreach ( $search_engines as $se ) {
		for ( $i = $days; $i >= 0; $i -- ) {
			$stat                   = wp_statistics_searchengine( $se['tag'], '-' . $i );
			$stats[ $se['name'] ][] = $stat;
			$total_daily[ $i ]      += $stat;
		}
	}
	?>
    <canvas id="<?php echo $id; ?>" height="<?php echo $size; ?>"></canvas>
    <script>
        var colors = [];
        colors['baidu'] = ['rgba(35, 25, 220, 0.2)', 'rgba(35, 25, 220, 1)'];
        colors['bing'] = ['rgba(12, 132, 132, 0.2)', 'rgba(12, 132, 132, 1)'];
        colors['duckduckgo'] = ['rgba(222, 88, 51, 0.2)', 'rgba(222, 88, 51, 1)'];
        colors['google'] = ['rgba(23, 107, 239, 0.2)', 'rgba(23, 107, 239, 1)'];
        colors['yahoo'] = ['rgba(64, 0, 144, 0.2)', 'rgba(64, 0, 144, 1)'];
        colors['yandex'] = ['rgba(255, 219, 77, 0.2)', 'rgba(255, 219, 77, 1)'];
        colors['ask'] = ['rgba(205, 0, 0, 0.2)', 'rgba(205, 0, 0, 1)'];
        colors['clearch'] = ['rgba(13, 0, 76, 0.2)', 'rgba(13, 0, 76, 1)'];

        var ctx = document.getElementById("<?php echo $id; ?>").getContext('2d');
        var ChartJs = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [<?php echo implode( ', ', $date ); ?>],
                datasets: [
					<?php foreach ( $search_engines as $se ): ?>
                    {
                        label: '<?php echo $se['name']; ?>',
                        data: [<?php echo implode( ',', $stats[ $se['name'] ] ); ?>],
                        backgroundColor: colors['<?php echo $se['tag']; ?>'][0],
                        borderColor: colors['<?php echo $se['tag']; ?>'][1],
                        borderWidth: 1,
                        fill: true,
                    },
					<?php endforeach; ?>
					<?php if ( $total_stats == 1 ) : ?>
                    {
                        label: '<?php _e( 'Total', 'wp-statistics' ); ?>',
                        data: [<?php echo implode( ',', $total_daily ); ?>],
                        backgroundColor: 'rgba(180, 180, 180, 0.2)',
                        borderColor: 'rgba(180, 180, 180, 1)',
                        borderWidth: 1,
                        fill: false,
                    },
					<?php endif;?>
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
                    text: '<?php echo sprintf( __( 'Search engine referrals in the last %s days', 'wp-statistics' ), $days ); ?>'
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

