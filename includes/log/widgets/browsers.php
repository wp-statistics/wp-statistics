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
	}

	if ( $topten_browser_name and $topten_browser_value ) {
		$topten_browser_name[]  = "'" . __( 'Other', 'wp-statistics' ) . "'";
		$topten_browser_value[] = ( $total - $topten );
	}
	?>
    <canvas id="<?php echo $id; ?>" height="240"></canvas>
    <script>
        var ctx = document.getElementById("<?php echo $id; ?>").getContext('2d');
        var ChartJs = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: [<?php echo implode( ', ', $topten_browser_name ); ?>],
                datasets: [{
                    label: '<?php _e( 'Browsers', 'wp-statistics' ); ?>',
                    data: [<?php echo implode( ', ', $topten_browser_value ); ?>],
                    backgroundColor: [
                        "rgba(230, 126, 34, 0.4)",
                        "rgba(142, 68, 173, 0.4)",
                        "rgba(72, 201, 176, 0.4)",
                        "rgba(244, 208, 63, 0.4)",
                        "rgba(84, 153, 199, 0.4)",
                        "rgba(231, 76, 60, 0.4)",
                        "rgba(93, 109, 126, 0.4)",
                        "rgba(23, 216, 35, 0.4)",
                        "rgba(23, 205, 216, 0.4)",
                        "rgba(140, 140, 140, 0.4)",
                    ],
                }]
            },
            options: {
                responsive: true,
                legend: {
                    position: 'bottom',
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
