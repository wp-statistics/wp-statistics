<script type="text/javascript">
    jQuery(document).ready(function () {
        postboxes.add_postbox_toggles(pagenow);
    });
</script>
<div class="wrap">
    <h2><?php _e( 'Category Statistics', 'wp-statistics' ); ?></h2>

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
	if ( array_key_exists( 'cat', $_GET ) ) {
		$category = intval( $_GET['cat'] );
	} else {
		if ( array_key_exists( 'precat', $_GET ) ) {
			$category = intval( $_GET['precat'] );
		} else {
			$category = 0;
		}
	}

	$html = __( 'Select Category', 'wp-statistics' ) . ': ';

	$args = array(
		'selected' => $category,
		'echo'     => 0,
	);

	$html .= wp_dropdown_categories( $args );
	$html .= '<input type="submit" value="' . __( 'Select', 'wp-statistics' ) . '" class="button-primary">';
	$html .= '<br>';

	list( $daysToDisplay, $rangestart_utime, $rangeend_utime ) = wp_statistics_date_range_calculator( $daysToDisplay, $rangestart, $rangeend );

	wp_statistics_date_range_selector( WP_STATISTICS_CATEGORIES_PAGE, $daysToDisplay, null, null, '&precat=' . $category, $html );

	$args = array(
		'category' => $category,
	);

	$posts = get_posts( $args );

	$visit_total   = 0;
	$daysInThePast = (int) ( ( time() - $rangeend_utime ) / 86400 );
	$posts_stats   = array();
	$visits        = array();

	// Setup the array, otherwise PHP may throw an error.
	foreach ( $posts as $post ) {
		$posts_stats[ $post->ID ] = 0;
	}

	for ( $i = $daysToDisplay; $i >= 0; $i -- ) {
		$date[] = "'" . $WP_Statistics->Real_Current_Date( 'M j', '-' . ( $i + $daysInThePast ), $rangeend_utime ) . "'";

		$stat = 0;
		foreach ( $posts as $post ) {
			$temp_stat                = wp_statistics_pages( '-' . (int) ( $i + $daysInThePast ), null, $post->ID );
			$posts_stats[ $post->ID ] += $temp_stat;
			$stat                     = $temp_stat;
		}

		$visits[]    = $stat;
		$visit_total += $stat;
	}
	?>
    <div class="postbox-container" id="last-log">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
					<?php $paneltitle = __( 'Category Statistics Chart', 'wp-statistics' ); ?>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf( __( 'Toggle panel: %s', 'wp-statistics' ), $paneltitle ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></span></h2>
                    <div class="inside">
                        <canvas id="hit-stats" height="80"></canvas>
                        <script>
                            var ctx = document.getElementById("hit-stats").getContext('2d');
                            var ChartJs = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: [<?php echo implode( ', ', $date ); ?>],
                                    datasets: [
                                        {
                                            label: '<?php _e( 'Visits', 'wp-statistics' ); ?>',
                                            data: [<?php echo implode( ',', $visits ); ?>],
                                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                            borderColor: 'rgba(54, 162, 235, 1)',
                                            borderWidth: 1,
                                            fill: true,
                                        },
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    legend: {
                                        position: 'bottom',
                                    },
                                    title: {
                                        display: true,
                                        text: '<?php echo sprintf( __( 'Hits in the last %s days', 'wp-statistics' ), $daysToDisplay ); ?>'
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
            </div>
        </div>
    </div>

    <div class="postbox-container" style="width: 100%; float: left; margin-right:20px">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
					<?php $paneltitle = __( 'Category Statistics Summary', 'wp-statistics' ); ?>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf( __( 'Toggle panel: %s', 'wp-statistics' ), $paneltitle ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></span></h2>
                    <div class="inside">
                        <table width="auto" class="widefat table-stats" id="summary-stats">
                            <tbody>
                            <tr>
                                <th></th>
                                <th class="th-center"><?php _e( 'Count', 'wp-statistics' ); ?></th>
                            </tr>

                            <tr>
                                <th><?php _e( 'Number of posts in category', 'wp-statistics' ); ?>:</th>
                                <th class="th-center"><span><?php echo number_format_i18n( count( $posts ) ); ?></span>
                                </th>
                            </tr>

                            <tr>
                                <th><?php _e( 'Chart Visits Total', 'wp-statistics' ); ?>:</th>
                                <th class="th-center"><span><?php echo number_format_i18n( $visit_total ); ?></span>
                                </th>
                            </tr>

                            <tr>
                                <th><?php _e( 'All Time Visits Total', 'wp-statistics' ); ?>:</th>
                                <th class="th-center"><span><?php

										$stat = 0;
										foreach ( $posts as $post ) {
											$stat += wp_statistics_pages( 'total', null, $post->ID );
										}

										echo number_format_i18n( $stat ); ?></span></th>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="postbox-container" style="width: 100%; float: left; margin-right:20px">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
					<?php $paneltitle = __( 'Category Posts Sorted by Hits', 'wp-statistics' ); ?>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf( __( 'Toggle panel: %s', 'wp-statistics' ), $paneltitle ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></span></h2>
                    <div class="inside">
                        <table width="auto" class="widefat table-stats" id="post-stats">
                            <tbody>
                            <tr>
                                <th><?php _e( 'Post Title', 'wp-statistics' ); ?></th>
                                <th class="th-center"><?php _e( 'Hits', 'wp-statistics' ); ?></th>
                            </tr>

							<?php
							arsort( $posts_stats );

							$posts_by_id = array();

							foreach ( $posts as $post ) {
								$posts_by_id[ $post->ID ] = $post;
							}

							foreach ( $posts_stats as $post_id => $post_stat ) {
								$post_obj = $posts_by_id[ $post_id ];

								?>
                                <tr>
                                    <th>
                                        <a href="<?php echo get_permalink( $post_obj ); ?>"><?php echo $post_obj->post_title; ?></a>
                                    </th>
                                    <th class="th-center"><span><?php echo number_format_i18n( $post_stat ); ?></span>
                                    </th>
                                </tr>
								<?php
							}
							?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
