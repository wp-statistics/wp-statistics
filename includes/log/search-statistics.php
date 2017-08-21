<script type="text/javascript">
    jQuery(document).ready(function () {
        postboxes.add_postbox_toggles(pagenow);
    });
</script>
<?php
$search_engines = wp_statistics_searchengine_list();

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
$daysInThePast = round( ( time() - $rangeend_utime ) / 86400, 0 );
?>
<div class="wrap">
    <h2><?php _e( 'Search Engine Referral Statistics', 'wp-statistics' ); ?></h2>

	<?php wp_statistics_date_range_selector( WP_STATISTICS_SEARCHES_PAGE, $daysToDisplay ); ?>

    <div class="postbox-container" style="width: 100%; float: left; margin-right:20px">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
                    <?php $paneltitle = __( 'Search Engine Referral Statistics', 'wp-statistics' ); ?>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf( __( 'Toggle panel: %s', 'wp-statistics' ), $paneltitle ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></span></h2>
                    <div class="inside">
                        <script type="text/javascript">
                            var visit_chart;
                            jQuery(document).ready(function () {
								<?php
								$total_stats = $WP_Statistics->get_option( 'chart_totals' );
								$total_daily = array();

								foreach ( $search_engines as $se ) {

									echo "var searches_data_line_" . $se['tag'] . " = [";

									for ( $i = $daysToDisplay; $i >= 0; $i -- ) {
										if ( ! array_key_exists( $i, $total_daily ) ) {
											$total_daily[ $i ] = 0;
										}

										$stat              = wp_statistics_searchengine( $se['tag'], '-' . ( $i + $daysInThePast ) );
										$total_daily[ $i ] += $stat;

										echo "['" . $WP_Statistics->Real_Current_Date( 'Y-m-d', '-' . $i, $rangeend_utime ) . "'," . $stat . "], ";

									}

									echo "];\n";
								}

								if ( $total_stats == 1 ) {
									echo "var searches_data_line_total = [";

									for ( $i = $daysToDisplay; $i >= 0; $i -- ) {
										echo "['" . $WP_Statistics->Real_Current_Date( 'Y-m-d', '-' . $i, $rangeend_utime ) . "'," . $total_daily[ $i ] . "], ";
									}

									echo "];\n";
								}

								$tickInterval = round( $daysToDisplay / 20, 0 );
								if ( $tickInterval < 1 ) {
									$tickInterval = 1;
								}
								?>
                                visit_chart = jQuery.jqplot('search-stats', [<?php foreach ( $search_engines as $se ) {
									echo "searches_data_line_" . $se['tag'] . ", ";
								} if ( $total_stats == 1 ) {
									echo 'searches_data_line_total';
								}?>], {
                                    title: {
                                        text: '<b>' + <?php echo json_encode( sprintf( __( 'Search engine referrals in the last %s days', 'wp-statistics' ), $daysToDisplay ) ); ?> +'</b>',
                                        fontSize: '12px',
                                        fontFamily: 'Tahoma',
                                        textColor: '#000000',
                                    },
                                    axes: {
                                        xaxis: {
                                            min: '<?php echo $WP_Statistics->Real_Current_Date( 'Y-m-d', '-' . $daysToDisplay, $rangeend_utime ); ?>',
                                            max: '<?php echo $WP_Statistics->Real_Current_Date( 'Y-m-d', '-0', $rangeend_utime ); ?>',
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
                                            label: '<?php echo addslashes( __( 'Number of referrals', 'wp-statistics' ) ); ?>',
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
                                        labels: [<?php foreach ( $search_engines as $se ) {
											echo "'" . addslashes( $se['translated'] ) . "', ";
										} if ( $total_stats == 1 ) {
											echo "'" . addslashes( __( 'Total', 'wp-statistics' ) ) . "'";
										} ?>],
                                        renderer: jQuery.jqplot.EnhancedLegendRenderer,
                                        rendererOptions: {
                                            numberColumns: <?php echo count( $search_engines ) + 1; ?>,
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
                                    JQPlotSearchChartLengendClickRedraw()
                                });

                                function JQPlotSearchChartLengendClickRedraw() {
                                    visit_chart.replot({resetAxes: ['yaxis']});
                                    jQuery('div[id="search-stats"] .jqplot-table-legend').click(function () {
                                        JQPlotSearchChartLengendClickRedraw();
                                    });
                                }

                                jQuery('div[id="search-stats"] .jqplot-table-legend').click(function () {
                                    JQPlotSearchChartLengendClickRedraw()
                                });

                            });

                        </script>

                        <div id="search-stats" style="height:500px;"></div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
