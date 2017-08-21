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

$rangestartdate = $WP_Statistics->real_current_date( 'Y-m-d', '-0', $rangestart_utime );
$rangeenddate   = $WP_Statistics->real_current_date( 'Y-m-d', '-0', $rangeend_utime );

?>
<div class="wrap">
    <h2><?php _e( 'Browser Statistics', 'wp-statistics' ); ?></h2>

    <div><?php wp_statistics_date_range_selector( WP_STATISTICS_BROWSERS_PAGE, $daysToDisplay ); ?></div>

    <div class="postbox-container" style="width: 48%; float: left; margin-right:20px">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
                    <?php $paneltitle = __( 'Browsers', 'wp-statistics' ); ?>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf( __( 'Toggle panel: %s', 'wp-statistics' ), $paneltitle ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></span></h2>
                    <div class="inside">
                        <script type="text/javascript">
                            jQuery(function () {
                                var browser_chart;
                                jQuery(document).ready(function () {
									<?php
									$Browsers = wp_statistics_ua_list();
									if ( ! is_array( $Browsers ) ) {
										$Browsers = array();
									}

									natcasesort( $Browsers );

									echo "var browser_data = [";

									foreach ( $Browsers as $Browser ) {
										$count = wp_statistics_useragent( $Browser, $rangestartdate, $rangeenddate );
										echo "['" . substr( $Browser, 0, 15 ) . " (" . number_format_i18n( $count ) . ")'," . $count . "], ";
									}

									echo "];\n";


									?>

                                    browser_chart = jQuery.jqplot('browsers-log', [browser_data], {
                                        title: {
                                            text: '<b>' + <?php echo json_encode( __( 'Browsers by type', 'wp-statistics' ) ); ?> +'</b>',
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
                                                numberColumns: 3,
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

                        <div id="browsers-log" style="height: <?php $height = ( ceil( count( $Browsers ) / 3 ) * 27 ) + 400;
						if ( $height < 400 ) {
							$height = 400;
						}
						echo $height; ?>px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="postbox-container" style="width: 48%; float: left; margin-right:20px">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
                    <?php $paneltitle = __( 'Platform', 'wp-statistics' ); ?>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf( __( 'Toggle panel: %s', 'wp-statistics' ), $paneltitle ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></span></h2>
                    <div class="inside">
                        <script type="text/javascript">
                            jQuery(function () {
                                var platform_chart;
                                jQuery(document).ready(function () {
									<?php
									$Platforms = wp_statistics_platform_list( null, $rangestartdate, $rangeenddate );
									if ( ! is_array( $Platforms ) ) {
										$Platforms = array();
									}

									natcasesort( $Platforms );

									echo "var platform_data = [";

									foreach ( $Platforms as $Platform ) {
										$count = wp_statistics_platform( $Platform );
										echo "['" . substr( $Platform, 0, 15 ) . " (" . number_format_i18n( $count ) . ")'," . $count . "], ";
									}

									echo "];\n";


									?>

                                    platform_chart = jQuery.jqplot('platform-log', [platform_data], {
                                        title: {
                                            text: '<b>' + <?php echo json_encode( __( 'Browsers by platform', 'wp-statistics' ) ); ?> +'</b>',
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
                                                numberColumns: 3,
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
                                    platform_chart.replot({resetAxes: true});
                                });

                            });

                        </script>

                        <div id="platform-log" style="height: <?php $height = ( ceil( count( $Platforms ) / 3 ) * 27 ) + 400;
						if ( $height < 400 ) {
							$height = 400;
						}
						echo $height; ?>px;"></div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="width: 100%; clear: both;">
        <hr/>
    </div>

    <div class="postbox-container" style="width: 30%; float: left; margin-right: 20px;">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
				<?php
				for ( $BrowserCount = 0; $BrowserCount < count( $Browsers ); $BrowserCount ++ ) {
					if ( $BrowserCount % 3 == 0 ) {
						BrowserVersionStats( $Browsers[ $BrowserCount ] );
					}
				}
				?>
            </div>
        </div>
    </div>

    <div class="postbox-container" style="width: 30%; float: left; margin-right: 20px;">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
				<?php
				for ( $BrowserCount = 0; $BrowserCount < count( $Browsers ); $BrowserCount ++ ) {
					if ( $BrowserCount % 3 == 1 ) {
						BrowserVersionStats( $Browsers[ $BrowserCount ] );
					}
				}
				?>
            </div>
        </div>
    </div>

    <div class="postbox-container" style="width: 30%; float: left">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
				<?php
				for ( $BrowserCount = 0; $BrowserCount < count( $Browsers ); $BrowserCount ++ ) {
					if ( $BrowserCount % 3 == 2 ) {
						BrowserVersionStats( $Browsers[ $BrowserCount ] );
					}
				}
				?>
            </div>
        </div>
    </div>
</div>

<?php function BrowserVersionStats( $Browser, $rangestartdate = null, $rangeenddate = null ) {
	$Browser_tag = strtolower( preg_replace( '/[^a-zA-Z]/', '', $Browser ) ); ?>
    <div class="postbox">
        <?php $paneltitle = sprintf( __( '%s Version', 'wp-statistics' ), $Browser ); ?>
        <button class="handlediv" type="button" aria-expanded="true">
            <span class="screen-reader-text"><?php printf( __( 'Toggle panel: %s', 'wp-statistics' ), $paneltitle ); ?></span>
            <span class="toggle-indicator" aria-hidden="true"></span>
        </button>
        <h2 class="hndle"><span><?php echo $paneltitle; ?></span></h2>
        <div class="inside">
            <script type="text/javascript">
                jQuery(function () {
                    var <?php echo $Browser_tag;?>_chart;
                    jQuery(document).ready(function () {
						<?php
						$Versions = wp_statistics_agent_version_list( $Browser, $rangestartdate, $rangeenddate );
						if ( ! is_array( $Versions ) ) {
							$Versions = array();
						}

						natcasesort( $Versions );

						echo "var " . $Browser_tag . "_version_data = [";

						foreach ( $Versions as $Version ) {
							$count = wp_statistics_agent_version( $Browser, $Version, $rangestartdate, $rangeenddate );
							echo "['" . $Version . " (" . number_format_i18n( $count ) . ")'," . $count . "], ";
						}

						echo "];\n";


						?>
						<?php echo $Browser_tag;?>_chart = jQuery.jqplot('version-<?php echo $Browser_tag;?>-log', [<?php echo $Browser_tag;?>_version_data], {
                            title: {
                                text: '<b><?php echo $Browser; ?></b>',
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
                                renderer: jQuery.jqplot.EnhancedLegendPieRenderer,
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
                        <?php echo $Browser_tag;?>_chart.replot({resetAxes: true});
                    });

                });
            </script>
            <div class="ltr" id="version-<?php echo $Browser_tag; ?>-log" style="height: <?php $height = ( ceil( count( $Versions ) / 2 ) * 27 ) + 237;
			if ( $height < 300 ) {
				$height = 300;
			}
			echo $height; ?>px;"></div>
        </div>
    </div>
<?php } ?>