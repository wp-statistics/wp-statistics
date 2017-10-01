<?php
function wp_statistics_generate_browsers_postbox_content() {
	global $wpdb, $WP_Statistics;
	$id                   = 'browser-stats-' . rand( 111, 999 );
	$Browsers             = wp_statistics_ua_list();
	$BrowserVisits        = array();
	$total                = 0;
	$count                = 0;
	$topten               = 0;
	$topten_browser_name  = array();
	$topten_browser_value = array();

	foreach ( $Browsers as $Browser ) {
		$BrowserVisits[ $Browser ] = wp_statistics_useragent( $Browser );
		$total                     += $BrowserVisits[ $Browser ];
	}

	arsort( $BrowserVisits );

	foreach ( $BrowserVisits as $key => $value ) {
		$topten += $value;
		$count ++;
		if ( $count > 9 ) {
			break;
		}

		$topten_browser_name[]  = "'" . $key . "'";
		$topten_browser_value[] = $value;
		$topten_browser_color[] = wp_statistics_generate_rgba_color( $count, '0.4' );
	}

	if ( $topten_browser_name and $topten_browser_value ) {
		$topten_browser_name[]  = "'" . __( 'Other', 'wp-statistics' ) . "'";
		$topten_browser_value[] = ( $total - $topten );
		$topten_browser_color[] = wp_statistics_generate_rgba_color( 10, '0.4' );
	}
	?>
    <canvas id="<?php echo $id; ?>" height="220"></canvas>
    <script>
        var ctx = document.getElementById("<?php echo $id; ?>").getContext('2d');
        var ChartJs = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: [<?php echo implode( ', ', $topten_browser_name ); ?>],
                datasets: [{
                    label: '<?php _e( 'Browsers', 'wp-statistics' ); ?>',
                    data: [<?php echo implode( ', ', $topten_browser_value ); ?>],
                    backgroundColor: [<?php echo implode( ', ', $topten_browser_color ); ?>],
                }]
            },
            options: {
                responsive: true,
                legend: {
                    position: 'bottom',
                },
                animation: {
                    duration: 0,
                },
                tooltips: {
                    callbacks: {
                        label: function (tooltipItem, data) {
                            var dataset = data.datasets[tooltipItem.datasetIndex];
                            var total = dataset.data.reduce(function (previousValue, currentValue, currentIndex, array) {
                                return previousValue + currentValue;
                            });
                            var currentValue = dataset.data[tooltipItem.index];
                            var precentage = Math.floor(((currentValue / total) * 100) + 0.5);
                            return precentage + "% - " + data.labels[tooltipItem.index];
                        }
                    }
                }
            }
        });
    </script>
	<?php
}
