<?php

function wp_statistics_generate_page_postbox_content( $pageuri, $pageid, $days = 20, $chart_title = null, $rangestart = '', $rangeend = '' ) {
	GLOBAL $WP_Statistics;

	if ( ! $WP_Statistics->get_option( 'pages' ) ) {
		return;
	}

	if ( $chart_title == null ) {
		$chart_title = __( 'Page Trending Stats', 'wp_statistics' );
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

	?>
    <script type="text/javascript">
        var pages_chart;
        jQuery(document).ready(function () {
			<?php
			echo 'var page_data_line = [';

			for ( $i = $daysToDisplay; $i >= 0; $i -- ) {
				$stat = wp_statistics_pages( '-' . ( $i + $daysInThePast ), $pageuri, $pageid );

				echo "['" . $WP_Statistics->Real_Current_Date( 'Y-m-d', '-' . $i, $rangeend_utime ) . "'," . $stat . "], ";
			}

			echo "];\n";

			$tickInterval = round( $daysToDisplay / 20, 0 );
			if ( $tickInterval < 1 ) {
				$tickInterval = 1;
			}

			?>
            pages_jqchart = jQuery.jqplot('page-stats', [page_data_line], {
                title: {
                    text: '<b>' + <?php echo json_encode( __( $chart_title, 'wp_statistics' ) ); ?> +'</b>',
                    fontSize: '12px',
                    fontFamily: 'Tahoma',
                    textColor: '#000000',
                },
                axes: {
                    xaxis: {
                        min: '<?php echo $WP_Statistics->Real_Current_Date( 'Y-m-d', '-' . $daysToDisplay, $rangeend_utime );?>',
                        max: '<?php echo $WP_Statistics->Real_Current_Date( 'Y-m-d', '-0', $rangeend_utime );?>',
                        tickInterval: '<?php echo $tickInterval; ?> day',
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
                        label: <?php echo json_encode( __( 'Number of Hits', 'wp_statistics' ) ); ?>,
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
                    labels: ['<?php echo (int) $pageid . ' - ' . $title; ?>'],
                    renderer: jQuery.jqplot.EnhancedLegendRenderer,
                    rendererOptions: {
                        numberColumns: 5,
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
                JQPlotPagesChartLengendClickRedraw()
            });

            function JQPlotPagesChartLengendClickRedraw() {
                pages_jqchart.replot({resetAxes: ['yaxis']});
                jQuery('div[id="page-stats"] .jqplot-table-legend').click(function () {
                    JQPlotPagesChartLengendClickRedraw();
                });
            }

            jQuery('div[id="page-stats"] .jqplot-table-legend').click(function () {
                JQPlotPagesChartLengendClickRedraw()
            });
        });
    </script>

    <div id="page-stats" style="height:500px;"></div>

	<?php
}