<?php
function wp_statistics_generate_browsers_postbox_content() {

	global $wpdb, $WP_Statistics;
	?>
    <script type="text/javascript">
        jQuery(function () {
            var browser_chart;
            jQuery(document).ready(function () {
				<?php
				$Browsers = wp_statistics_ua_list();
				$BrowserVisits = array();
				$total = 0;

				foreach ( $Browsers as $Browser ) {
					$BrowserVisits[ $Browser ] = wp_statistics_useragent( $Browser );
					$total                     += $BrowserVisits[ $Browser ];
				}

				arsort( $BrowserVisits );

				echo "var browser_data = [";
				$count = 0;
				$topten = 0;

				foreach ( $BrowserVisits as $key => $value ) {
					echo "['" . substr( $key, 0, 15 ) . " (" . number_format_i18n( $value ) . ")'," . $value . "], ";

					$topten += $value;
					$count ++;
					if ( $count > 9 ) {
						break;
					}
				}

				echo "['" . json_encode( __( 'Other', 'wp_statistics' ) ) . " (" . number_format_i18n( $total - $topten ) . ")'," . ( $total - $topten ) . "], ";

				echo "];\n";
				?>

                browser_chart = jQuery.jqplot('browsers-log', [browser_data], {
                    title: {
                        text: '<b>' + <?php echo json_encode( __( 'Top 10 Browsers', 'wp_statistics' ) ); ?> +'</b>',
                        fontSize: '12px',
                        fontFamily: 'Tahoma',
                        textColor: '#000000',
                    },
                    seriesDefaults: {
                        // Make this a pie chart.
                        renderer: jQuery.jqplot.PieRenderer,
                        rendererOptions: {
                            // Put data labels on the pie slices.
                            // By default, labels show the percentage of the slice.
                            dataLabels: 'percent',
                            showDataLabels: true,
                            shadowOffset: 0,
                        }
                    },
                    legend: {
                        show: true,
                        location: 's',
                        renderer: jQuery.jqplot.EnhancedPieLegendRenderer,
                        rendererOptions: {
                            numberColumns: 2,
                            disableIEFading: false,
                            border: 'none',
                        },
                    },
                    grid: {background: 'transparent', borderWidth: 0, shadow: false},
                    highlighter: {
                        show: true,
                        formatString: '%s',
                        tooltipLocation: 'n',
                        useAxesFormatters: false,
                    },
                });
            });

            jQuery(window).resize(function () {
                browser_chart.replot({resetAxes: true});
            });
        });

    </script>

    <div id="browsers-log" style="height: <?php $height = ( count( $Browsers ) / 2 * 27 ) + 300;
	if ( $height > 462 ) {
		$height = 462;
	}
	echo $height; ?>px;"></div>
	<?php
}

