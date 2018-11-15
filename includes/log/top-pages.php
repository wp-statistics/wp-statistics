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

list( $daysToDisplay, $rangestart_utime, $rangeend_utime ) = wp_statistics_date_range_calculator(
	$daysToDisplay,
	$rangestart,
	$rangeend
);
$daysInThePast = round( ( time() - $rangeend_utime ) / 86400, 0 );

list( $total, $uris ) = wp_statistics_get_top_pages(
	$WP_Statistics->Real_Current_Date( 'Y-m-d', '-0', $rangestart_utime ),
	$WP_Statistics->Real_Current_Date( 'Y-m-d', '-0', $rangeend_utime )
);
$count = 0;

foreach ( $uris as $uri ) {
	$count ++;

	for ( $i = $daysToDisplay; $i >= 0; $i -- ) {
		$stats[ $uri[0] ][] = wp_statistics_pages( '-' . ( $i + $daysInThePast ), $uri[0] );
	}

	if ( $count > 4 ) {
		break;
	}
}

for ( $i = $daysToDisplay; $i >= 0; $i -- ) {
	$date[] = "'" . $WP_Statistics->Real_Current_Date( 'M j', '-' . $i, $rangeend_utime ) . "'";
}
?>
<div class="wrap">
    <h2><?php _e( 'Top Pages', 'wp-statistics' ); ?></h2>
	<?php do_action( 'wp_statistics_after_title' ); ?>

	<?php wp_statistics_date_range_selector( WP_Statistics::$page['pages'], $daysToDisplay ); ?>
    <div class="postbox-container" id="last-log">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
					<?php $paneltitle = __( 'Top 5 Pages Trends', 'wp-statistics' ); ?>
                    <button class="handlediv" type="button" aria-expanded="true">
						<span class="screen-reader-text"><?php printf(
								__( 'Toggle panel: %s', 'wp-statistics' ),
								$paneltitle
							); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></h2>

                    <div class="inside">
                        <canvas id="hit-stats" height="80"></canvas>
                        <script>
                            var colors = [];
                            colors[0] = ['rgba(12, 132, 132, 0.2)', 'rgba(12, 132, 132, 1)'];
                            colors[1] = ['rgba(23, 107, 239, 0.2)', 'rgba(23, 107, 239, 1)'];
                            colors[2] = ['rgba(222, 88, 51, 0.2)', 'rgba(222, 88, 51, 1)'];
                            colors[3] = ['rgba(255, 99, 132, 0.2)', 'rgba(255, 99, 132, 1)'];
                            colors[4] = ['rgba(54, 162, 235, 0.2)', 'rgba(54, 162, 235, 1)'];

                            var ctx = document.getElementById("hit-stats").getContext('2d');
                            var ChartJs = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: [<?php echo implode( ', ', $date ); ?>],
                                    datasets: [
										<?php foreach ($stats as $key => $value) : $i ++; ?>
                                        {
                                            label: '<?php echo $key; ?>',
                                            data: [<?php echo implode( ',', $value ); ?>],
                                            backgroundColor: colors[<?php echo $i; ?>][0],
                                            borderColor: colors[<?php echo $i; ?>][1],
                                            fill: true,
                                            borderWidth: 1,
                                        },
										<?php endforeach; ?>
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    legend: {
                                        position: 'bottom',
                                    },
                                    title: {
                                        display: true,
                                        text: '<?php echo htmlentities( __( 'Top 5 Page Trending Stats', 'wp-statistics' ), ENT_QUOTES ); ?>'
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
                    </div>
                </div>

                <div class="postbox">
					<?php $paneltitle = __( 'Top Pages', 'wp-statistics' ); ?>
                    <button class="handlediv" type="button" aria-expanded="true">
						<span class="screen-reader-text"><?php printf(
								__( 'Toggle panel: %s', 'wp-statistics' ),
								$paneltitle
							); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></h2>

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

							$Pagination = new WP_Statistics_Pagination(
								$total,
								$pagesPerSection,
								$options,
								false,
								$stylePageOff,
								$stylePageOn,
								$styleErrors,
								$styleSelect
							);

							$start = $Pagination->getEntryStart();
							$end   = $Pagination->getEntryEnd();

							$site_url = site_url();

							$count = 0;

							echo "<table width=\"100%\" class=\"widefat table-stats\" id=\"last-referrer\">
		                          <tr>";
							echo "<td width='10%'>" . __( 'ID', 'wp-statistics' ) . "</td>";
							echo "<td width='40%'>" . __( 'Title', 'wp-statistics' ) . "</td>";
							echo "<td width='40%'>" . __( 'Link', 'wp-statistics' ) . "</td>";
							echo "<td width='10%'>" . __( 'Visits', 'wp-statistics' ) . "</td>";
							echo "</tr>";

							foreach ( $uris as $uri ) {
								$count ++;

								if ( $count >= $start ) {

									if ( $uri[3] == '' ) {
										$uri[3] = '[' .
										          htmlentities( __( 'No page title found', 'wp-statistics' ), ENT_QUOTES ) .
										          ']';
									}
									echo "<tr>";
									echo "<td style=\"text-align: left\">" . $count . "</td>";
									echo "<td style=\"text-align: left\">" . $uri[3] . "</td>";

									echo "<td style=\"text-align: left\"><a dir='ltr' href='" .
									     htmlentities( $site_url . $uri[0], ENT_QUOTES ) .
									     "'>" .
									     htmlentities( urldecode( $uri[0] ), ENT_QUOTES ) .
									     "</a></td>";
									echo "<td style=\"text-align: left\"><a href='?page=" .
									     WP_Statistics::$page['pages'] .
									     '&page-uri=' .
									     htmlentities( $uri[0], ENT_QUOTES ) .
									     "'>" .
									     number_format_i18n( $uri[1] ) .
									     "</a></td>";
								}

								if ( $count == $start + 10 ) {
									break;
								}

							}

							echo "</table>";
						}
						?>
                    </div>
                </div>

				<?php if ( $total > 0 ) { ?>
                    <div class="pagination-log">
						<?php echo $Pagination->display(); ?>
                        <p id="result-log"><?php printf(
								__( 'Page %1$s of %2$s', 'wp-statistics' ),
								$Pagination->getCurrentPage(),
								$Pagination->getTotalPages()
							); ?></p>
                    </div>
				<?php } ?>
            </div>
        </div>
    </div>
</div>