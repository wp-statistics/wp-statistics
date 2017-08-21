<?php


?>

<script type="text/javascript">
    jQuery(document).ready(function () {
        postboxes.add_postbox_toggles(pagenow);
    });
</script>
<div class="wrap">
    <h2><?php _e( 'Tag Statistics', 'wp-statistics' ); ?></h2>

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
	if ( array_key_exists( 'tag', $_GET ) ) {
		$tag = $_GET['tag'];
	} else {
		if ( array_key_exists( 'pretag', $_GET ) ) {
			$tag = $_GET['pretag'];
		} else {
			$tag = '';
		}
	}

	$html = __( 'Select Tag', 'wp-statistics' ) . ': ';

	$tags = get_tags();

	$html .= '<select name="tag" id="tag">';

	foreach ( $tags as $tag_obj ) {
		// If a tag hasn't been selected yet, use the first one in the tag list.
		if ( '' === $tag ) {
			$tag = $tag_obj->slug;
		}

		// Check to see if this tag is the one that is currently selected.
		if ( $tag_obj->slug === $tag ) {
			$selected = ' SELECTED';
		} else {
			$selected = '';
		}

		$html .= "<option value=\"{$tag_obj->slug}\"{$selected}>{$tag_obj->name}</option>";
	}

	$html .= '</select>';
	$html .= ' <input type="submit" value="' . __( 'Select', 'wp-statistics' ) . '" class="button-primary">';
	$html .= '<br>';

	list( $daysToDisplay, $rangestart_utime, $rangeend_utime ) = wp_statistics_date_range_calculator( $daysToDisplay, $rangestart, $rangeend );

	wp_statistics_date_range_selector( WP_STATISTICS_TAGS_PAGE, $daysToDisplay, null, null, '&pretag=' . $tag, $html );

	$args = array(
		'tax_query' => array(
			array(
				'taxonomy' => 'post_tag',
				'field'    => 'slug',
				'terms'    => sanitize_title( $tag ),
			)
		),
	);

	$posts = get_posts( $args );

	?>

    <div class="postbox-container" style="width: 100%; float: left; margin-right:20px">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
                    <?php $paneltitle =  __( 'Tag Statistics Chart', 'wp-statistics' ); ?>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf( __( 'Toggle panel: %s', 'wp-statistics' ), $paneltitle ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></h2>
                    <div class="inside">
                        <script type="text/javascript">
                            var visit_chart;
                            jQuery(document).ready(function () {
								<?php
								$visit_total = 0;
								$daysInThePast = (int) ( ( time() - $rangeend_utime ) / 86400 );
								$posts_stats = array();

								// Setup the array, otherwise PHP may throw an error.
								foreach ( $posts as $post ) {
									$posts_stats[ $post->ID ] = 0;
								}

								echo "var visit_data_line = [";

								for ( $i = $daysToDisplay; $i >= 0; $i -- ) {
									$working_date = $WP_Statistics->Real_Current_Date( 'Y-m-d', '-' . ( $i + $daysInThePast ), $rangeend_utime );

									$stat = 0;
									foreach ( $posts as $post ) {
										$temp_stat                = wp_statistics_pages( '-' . (int) ( $i + $daysInThePast ), null, $post->ID );
										$posts_stats[ $post->ID ] += $temp_stat;
										$stat                     = $temp_stat;
									}

									$visit_total += $stat;

									echo "['" . $working_date . "'," . $stat . "], ";
								}

								echo "];\n";

								$tickInterval = round( $daysToDisplay / 20, 0 );
								if ( $tickInterval < 1 ) {
									$tickInterval = 1;
								}
								?>
                                visit_chart = jQuery.jqplot('visits-stats', [visit_data_line], {
                                    title: {
                                        text: '<b>' + <?php echo json_encode( sprintf( __( 'Hits in the last %s days', 'wp-statistics' ), $daysToDisplay ) ); ?> +'</b>',
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
                                            label: <?php echo json_encode( __( 'Number of visits', 'wp-statistics' ) ); ?>,
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
                                        labels: [<?php echo json_encode( __( 'Visits', 'wp-statistics' ) ); ?>],
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

                        <div id="visits-stats" style="height:500px;"></div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="postbox-container" style="width: 100%; float: left; margin-right:20px">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
                    <?php $paneltitle =  __( 'Tag Statistics Summary', 'wp-statistics' ); ?>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf( __( 'Toggle panel: %s', 'wp-statistics' ), $paneltitle ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></h2>
                    <div class="inside">
                        <table width="auto" class="widefat table-stats" id="summary-stats">
                            <tbody>
                            <tr>
                                <th></th>
                                <th class="th-center"><?php _e( 'Count', 'wp-statistics' ); ?></th>
                            </tr>

                            <tr>
                                <th><?php _e( 'Number of posts in tag', 'wp-statistics' ); ?>:</th>
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
                    <?php $paneltitle =  __( 'Tag Posts Sorted by Hits', 'wp-statistics' ); ?>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf( __( 'Toggle panel: %s', 'wp-statistics' ), $paneltitle ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></h2>
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
