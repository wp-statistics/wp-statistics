<?php
function wp_statistics_generate_hits_postbox_content( $size = '300px', $days = 20 ) {

	global $wpdb, $WP_Statistics;
	?>
    <script type="text/javascript">
        var visit_chart;
        jQuery(document).ready(function () {
			<?php
			$data_lines = array();

			if ( $WP_Statistics->get_option( 'visits' ) ) {
				echo "var visit_data_line = [";

				for ( $i = $days; $i >= 0; $i -- ) {
					$stat = wp_statistics_visit( '-' . $i, true );

					echo "['" . $WP_Statistics->Current_Date( 'Y-m-d', '-' . $i ) . "'," . $stat . "], ";

				}

				echo "];\n";

				$data_lines[] = 'visit_data_line';
			}

			if ( $WP_Statistics->get_option( 'visitors' ) ) {
				echo "var visitor_data_line = [";

				for ( $i = $days; $i >= 0; $i -- ) {
					$stat = wp_statistics_visitor( '-' . $i, true );

					echo "['" . $WP_Statistics->Current_Date( 'Y-m-d', '-' . $i ) . "'," . $stat . "], ";

				}

				echo "];\n";

				$data_lines[] = 'visitor_data_line';
			}
			?>
            visit_chart = jQuery.jqplot('visits-stats', [<?php echo implode( ',', $data_lines ); ?>], {
                title: {
                    text: '<b>' + <?php echo json_encode( __( 'Hits in the last', 'wp_statistics' ) . ' ' . $days . ' ' . __( 'days', 'wp_statistics' ) ); ?> +'</b>',
                    fontSize: '12px',
                    fontFamily: 'Tahoma',
                    textColor: '#000000',
                },
                axes: {
                    xaxis: {
                        min: '<?php echo $WP_Statistics->Current_Date( 'Y-m-d', '-' . $days ); ?>',
                        max: '<?php echo $WP_Statistics->Current_Date( 'Y-m-d', '' );?>',
                        tickInterval: '1 day',
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
                        label: <?php echo json_encode( __( 'Number of visits and visitors', 'wp_statistics' ) ); ?>,
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
                    labels: [<?php echo implode( ',', array(
						json_encode( __( 'Visit', 'wp_statistics' ) ),
						json_encode( __( 'Visitor', 'wp_statistics' ) )
					) ); ?>],
                    renderer: jQuery.jqplot.EnhancedLegendRenderer,
                    rendererOptions: {
                        numberColumns: 2,
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
                JQPlotVisitChartLengendClickRedraw()
            });

            function JQPlotVisitChartLengendClickRedraw() {
                visit_chart.replot({resetAxes: ['yaxis']});
                jQuery('div[id="visits-stats"] .jqplot-table-legend').click(function () {
                    JQPlotVisitChartLengendClickRedraw();
                });
            }

            jQuery('div[id="visits-stats"] .jqplot-table-legend').click(function () {
                JQPlotVisitChartLengendClickRedraw()
            });
        });
    </script>

    <div id="visits-stats" style="height:<?php echo $size; ?>;"></div>

	<?php
}
