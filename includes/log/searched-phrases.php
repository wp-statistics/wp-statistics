<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('.show-map').click(function () {
            alert('<?php _e( 'To be added soon', 'wp-statistics' ); ?>');
        });

        postboxes.add_postbox_toggles(pagenow);
    });
</script>
<?php
$date_args     = '';
$daysToDisplay = 20;
if ( array_key_exists( 'hitdays', $_GET ) ) {
	$daysToDisplay = intval( esc_attr( $_GET['hitdays'] ) );
	$date_args     .= '&hitdays=' . $daysToDisplay;
}

if ( array_key_exists( 'rangestart', $_GET ) ) {
	$rangestart = esc_attr( $_GET['rangestart'] );
	$date_args  .= '&rangestart=' . $rangestart;
} else {
	$rangestart = '';
}

if ( array_key_exists( 'rangeend', $_GET ) ) {
	$rangeend  = esc_attr( $_GET['rangeend'] );
	$date_args .= '&rangeend=' . $rangeend;
} else {
	$rangeend = '';
}

list( $daysToDisplay, $rangestart_utime, $rangeend_utime ) = wp_statistics_date_range_calculator(
	$daysToDisplay,
	$rangestart,
	$rangeend
);

$rangestartdate = $WP_Statistics->real_current_date( 'Y-m-d', '-0', $rangestart_utime );
$rangeenddate   = $WP_Statistics->real_current_date( 'Y-m-d', '-0', $rangeend_utime );

if ( array_key_exists( 'phrase', $_GET ) ) {
	$phrase       = $_GET['phrase'];
	$title        = $_GET['phrase'];
	$referr_field = '&phrase=' . $phrase;
} else {
	$phrase       = '';
	$phrase_field = null;
}

$get_urls = array();
$total    = 0;

if ( $phrase ) {
	$q_string = $wpdb->prepare(
		"SELECT `words` , count(`words`) as `count` FROM `{$wpdb->prefix}statistics_search` WHERE `words` LIKE %s AND `words` <> '' AND `last_counter` BETWEEN %s AND %s GROUP BY `words` order by `count` DESC",
		'%' . $phrase . '%',
		$rangestartdate,
		$rangeenddate
	);
	$result   = $wpdb->get_results( $q_string );
	$total    = count( $result );
} else {
	$q_string = $wpdb->prepare(
		"SELECT `words` , count(`words`) as `count` FROM `{$wpdb->prefix}statistics_search` WHERE `words` <> '' AND `last_counter` BETWEEN %s AND %s GROUP BY `words` order by `count` DESC",
		$rangestartdate,
		$rangeenddate
	);
	$result   = $wpdb->get_results( $q_string );
	$total    = count( $result );
}

?>
<div class="wrap">
    <h2><?php _e( 'Top Searched Phrases', 'wp-statistics' ); ?></h2>

    <div><?php wp_statistics_date_range_selector(
			WP_Statistics::$page['searched-phrases'],
			$daysToDisplay,
			null,
			null,
			$phrase_field
		); ?></div>

    <div class="clear"/>

    <ul class="subsubsub">
		<?php if ( $phrase ) { ?>
            <li class="all"><a <?php if ( ! $phrase ) {
					echo 'class="current"';
				} ?>href="?page=<?php echo WP_Statistics::$page['searched-phrases'] . $date_args; ?>"><?php _e(
						'All',
						'wp-statistics'
					); ?></a>
            </li>
            |
            <li>
                <a class="current"
                   href="?page=<?php echo WP_Statistics::$page['searched-phrases']; ?>&referr=<?php echo esc_html( $phrase ) .
				                                                                                         $date_args; ?>"> <?php echo htmlentities(
						$title,
						ENT_QUOTES
					); ?>
                    <span class="count">(<?php echo $total; ?>)</span></a></li>
		<?php } else { ?>
            <li class="all"><a <?php if ( ! $phrase ) {
					echo 'class="current"';
				} ?>href="?page=<?php echo WP_Statistics::$page['searched-phrases'] . $date_args; ?>"><?php _e(
						'All',
						'wp-statistics'
					); ?>
                    <span class="count">(<?php echo $total; ?>)</span></a></li>
		<?php } ?>
    </ul>
    <div class="postbox-container" id="last-log">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
					<?php if ( $phrase ) {
						$paneltitle = sprintf( __( 'Searched Phrase: %s', 'wp-statistics' ), esc_html( $phrase ) );
					} else {
						$paneltitle = __( 'Top Searched Phrases', 'wp-statistics' );
					}; ?>
                    <button class="handlediv" type="button" aria-expanded="true">
						<span class="screen-reader-text"><?php printf(
								__( 'Toggle panel: %s', 'wp-statistics' ),
								$paneltitle
							); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></h2>

                    <div class="inside">
                        <div class="log-latest">
							<?php

							if ( $total > 0 ) {
								// Initiate pagination object with appropriate arguments
								$pagesPerSection = 10;
								$options         = array( 25, "All" );
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

								if ( $result ) {
									$result = array_slice( $result, $start, $end );
									$i      = $start;
									foreach ( $result as $item ) {
										$i ++;
										echo "<div class='log-item'>";
										echo "<div class='log-referred'>{$i} - {$item->words}</div>";
										echo "<div class='clear'></div>";
										echo "<div class='log-url'>{$item->count}</div>";
										echo "</div>";
									}
								}
							}

							?>
                        </div>
                    </div>
                </div>

                <div class="pagination-log">
					<?php if ( $total > 0 ) {
						echo $Pagination->display(); ?>
                        <p id="result-log"><?php printf(
								__( 'Page %1$s of %2$s', 'wp-statistics' ),
								$Pagination->getCurrentPage(),
								$Pagination->getTotalPages()
							); ?></p>
					<?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>