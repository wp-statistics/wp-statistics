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

// Browsers
$Browsers = wp_statistics_ua_list();
if ( ! is_array( $Browsers ) ) {
	$Browsers = array();
}

natcasesort( $Browsers );
$BrowserVisits = array();
foreach ( $Browsers as $Browser ) {
	$BrowserVisits[ $Browser ] = wp_statistics_useragent( $Browser, $rangestartdate, $rangeenddate );
}

$i = 0;
foreach ( $BrowserVisits as $key => $value ) {
	$i ++;
	$browser_name[]  = "'" . $key . "'";
	$browser_value[] = $value;
	$browser_color[] = wp_statistics_generate_rgba_color( $i, '0.4' );
}

// Platforms
$Platforms = wp_statistics_platform_list( null, $rangestartdate, $rangeenddate );
if ( ! is_array( $Platforms ) ) {
	$Platforms = array();
}

natcasesort( $Platforms );
$PlatformVisits = array();
foreach ( $Platforms as $Platform ) {
	$PlatformVisits[ $Platform ] = wp_statistics_platform( $Platform );
}

$i = 0;
foreach ( $PlatformVisits as $key => $value ) {
	$i ++;
	$platform_name[]  = "'" . $key . "'";
	$platform_value[] = $value;
	$platform_color[] = wp_statistics_generate_rgba_color( $i, '0.4' );
}
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
                        <canvas id="browsers-log" height="400"></canvas>
                        <script>
                            var ctx = document.getElementById("browsers-log").getContext('2d');
                            var ChartJs = new Chart(ctx, {
                                type: 'pie',
                                data: {
                                    labels: [<?php echo implode( ', ', $browser_name ); ?>],
                                    datasets: [{
                                        label: '<?php _e( 'Browsers', 'wp-statistics' ); ?>',
                                        data: [<?php echo implode( ', ', $browser_value ); ?>],
                                        backgroundColor: [<?php echo implode( ', ', $browser_color ); ?>],
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
                        <canvas id="platforms-log" height="400"></canvas>
                        <script>
                            var ctx = document.getElementById("platforms-log").getContext('2d');
                            var ChartJs = new Chart(ctx, {
                                type: 'pie',
                                data: {
                                    labels: [<?php echo implode( ', ', $platform_name ); ?>],
                                    datasets: [{
                                        label: '<?php _e( 'Platforms', 'wp-statistics' ); ?>',
                                        data: [<?php echo implode( ', ', $platform_value ); ?>],
                                        backgroundColor: [<?php echo implode( ', ', $platform_color ); ?>],
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
						wp_statistics_browser_version_stats( $Browsers[ $BrowserCount ] );
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
						wp_statistics_browser_version_stats( $Browsers[ $BrowserCount ] );
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
						wp_statistics_browser_version_stats( $Browsers[ $BrowserCount ] );
					}
				}
				?>
            </div>
        </div>
    </div>
</div>

