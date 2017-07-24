<script type="text/javascript">
    jQuery(document).ready(function () {
        postboxes.add_postbox_toggles(pagenow);
    });
</script>
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
$daysInThePast = round( ( time() - $rangeend_utime ) / 86400, 0 );

list( $total, $uris ) = wp_statistics_get_top_pages( $WP_Statistics->Real_Current_Date( 'Y-m-d', '-0', $rangestart_utime ), $WP_Statistics->Real_Current_Date( 'Y-m-d', '-0', $rangeend_utime ) );

?>
<div class="wrap">
    <h2><?php _e( 'Top Pages', 'wp_statistics' ); ?></h2>

	<?php wp_statistics_date_range_selector( WP_STATISTICS_PAGES_PAGE, $daysToDisplay ); ?>

    <div class="postbox-container" id="last-log">
        <div class="metabox-holder">
            <div class="meta-box-sortables">

                <div class="postbox">
                    <div class="handlediv" title="<?php _e( 'Click to toggle', 'wp_statistics' ); ?>"><br/></div>
                    <h3 class="hndle"><span><?php _e( 'Top 5 Pages Trends', 'wp_statistics' ); ?></span></h3>
                    <div class="inside">
                        <script type="text/javascript">
                            var pages_jqchart;
                            jQuery(document).ready(function () {
								<?php
								$count = 0;

								foreach ( $uris as $uri ) {

									$count ++;

									echo "var pages_data_line" . $count . " = [";

									for ( $i = $daysToDisplay; $i >= 0; $i -- ) {
										$stat = wp_statistics_pages( '-' . ( $i + $daysInThePast ), $uri[0] );

										echo "['" . $WP_Statistics->Real_Current_Date( 'Y-m-d', '-' . $i, $rangeend_utime ) . "'," . $stat . "], ";

									}

									echo "];\n";
									if ( $count > 4 ) {
										break;
									}
								}

								if ( $count < 6 ) {
									for ( $i = $count + 1; $i < 6; $i ++ ) {
										echo "var pages_data_line" . $i . " = [];\n";
									}
								}

								$tickInterval = round( $daysToDisplay / 20, 0 );
								if ( $tickInterval < 1 ) {
									$tickInterval = 1;
								}
								?>

                                pages_jqchart = jQuery.jqplot('jqpage-stats', [pages_data_line1, pages_data_line2, pages_data_line3, pages_data_line4, pages_data_line5], {
                                    title: {
                                        text: '<b><?php echo htmlentities( __( 'Top 5 Page Trending Stats', 'wp_statistics' ), ENT_QUOTES ); ?></b>',
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
                                        labels: [ <?php echo json_encode( __( 'Rank #1', 'wp_statistics' ) ); ?>, <?php echo json_encode( __( 'Rank #2', 'wp_statistics' ) ); ?>, <?php echo json_encode( __( 'Rank #3', 'wp_statistics' ) ); ?>, <?php echo json_encode( __( 'Rank #4', 'wp_statistics' ) ); ?>, <?php echo json_encode( __( 'Rank #5', 'wp_statistics' ) ); ?> ],
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
                                    jQuery('div[id="jqpage-stats"] .jqplot-table-legend').click(function () {
                                        JQPlotPagesChartLengendClickRedraw();
                                    });
                                }

                                jQuery('div[id="jqpage-stats"] .jqplot-table-legend').click(function () {
                                    JQPlotPagesChartLengendClickRedraw()
                                });
                            });
                        </script>

                        <div id="jqpage-stats" style="height:500px;"></div>

                    </div>
                </div>

                <div class="postbox">
                    <div class="handlediv" title="<?php _e( 'Click to toggle', 'wp_statistics' ); ?>"><br/></div>
                    <h3 class="hndle"><span><?php _e( 'Top Pages', 'wp_statistics' ); ?></span></h3>
                    <div class="inside">
						<?php
						if ( $total > 0 ) {
							// Instantiate pagination object with appropriate arguments
							$pagesPerSection = 10;
							$options         = 10;
							$stylePageOff    = "pageOff";
							$stylePageOn     = "pageOn";
							$styleErrors     = "paginationErrors";
							$styleSelect     = "paginationSelect";

							$Pagination = new WP_Statistics_Pagination( $total, $pagesPerSection, $options, false, $stylePageOff, $stylePageOn, $styleErrors, $styleSelect );

							$start = $Pagination->getEntryStart();
							$end   = $Pagination->getEntryEnd();

							$site_url = site_url();

							echo "<div class='log-latest'>";
							$count = 0;

							foreach ( $uris as $uri ) {
								$count ++;

								if ( $count >= $start ) {
									echo "<div class='log-item'>";

									if ( $uri[3] == '' ) {
										$uri[3] = '[' . htmlentities( __( 'No page title found', 'wp_statistics' ), ENT_QUOTES ) . ']';
									}

									echo "<div class='log-page-title'>{$count} - {$uri[3]}</div>";
									echo "<div class='right-div'>" . __( 'Visits', 'wp_statistics' ) . ": <a href='?page=" . WP_STATISTICS_PAGES_PAGE . '&page-uri=' . htmlentities( $uri[0], ENT_QUOTES ) . "'>" . number_format_i18n( $uri[1] ) . "</a></div>";
									echo "<div class='left-div'><a dir='ltr' href='" . htmlentities( $site_url . $uri[0], ENT_QUOTES ) . "'>" . htmlentities( urldecode( $uri[0] ), ENT_QUOTES ) . "</a></div>";
									echo "</div>";
								}

								if ( $count == $start + 10 ) {
									break;
								}

							}

							echo "</div>";
						}
						?>
                    </div>
                </div>

				<?php if ( $total > 0 ) { ?>
                    <div class="pagination-log">
						<?php echo $Pagination->display(); ?>
                        <p id="result-log"><?php echo ' ' . __( 'Page', 'wp_statistics' ) . ' ' . $Pagination->getCurrentPage() . ' ' . __( 'From', 'wp_statistics' ) . ' ' . $Pagination->getTotalPages(); ?></p>
                    </div>
				<?php } ?>
            </div>
        </div>
    </div>
</div>