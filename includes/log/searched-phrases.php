<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('.show-map').click(function () {
            alert('<?php _e( 'To be added soon', 'wp-statistics' ); ?>');
        });

        postboxes.add_postbox_toggles(pagenow);
    });
</script>
<?php
$date_args = '';
//Set Default Time Picker Option
list( $daysToDisplay, $rangestart, $rangeend ) = wp_statistics_prepare_range_time_picker();
if ( isset( $_GET['hitdays'] ) and $_GET['hitdays'] > 0 ) {
	$date_args .= '&hitdays=' . $daysToDisplay;
}
if ( isset( $_GET['rangeend'] ) and isset( $_GET['rangestart'] ) and strtotime( $_GET['rangestart'] ) != false and strtotime( $_GET['rangeend'] ) != false ) {
	$date_args .= '&rangestart=' . $rangestart . '&rangeend=' . $rangeend;
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
	<?php WP_Statistics_Admin_Pages::show_page_title( __( 'Top Search Words', 'wp-statistics' ) ); ?>
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
				} ?>href="?page=<?php echo WP_Statistics::$page['searched-phrases'] . $date_args; ?>"><?php _e( 'All', 'wp-statistics' ); ?></a>
            </li>|
            <li>
                <a class="current" href="?page=<?php echo WP_Statistics::$page['searched-phrases']; ?>&referr=<?php echo esc_html( $phrase ) . $date_args; ?>"> <?php echo htmlentities( $title, ENT_QUOTES ); ?>
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
						$paneltitle = __( 'Top Search Words', 'wp-statistics' );
					}; ?>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf( __( 'Toggle panel: %s', 'wp-statistics' ), $paneltitle ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></h2>

                    <div class="inside">
						<?php

						if ( $total > 0 ) {
						// Initiate pagination object with appropriate arguments
						$items_per_page = 10;
						$page           = isset( $_GET['pagination-page'] ) ? abs( (int) $_GET['pagination-page'] ) : 1;
						$offset         = ( $page * $items_per_page ) - $items_per_page;
						$start          = $offset;
						$end            = $offset + $items_per_page;

						if ( $result ) {
						$result = array_slice( $result, $start, $end );
						$i      = $start; ?>
                        <table width="100%" class="widefat table-stats" id="searched-phrases">
                            <tr>
                                <td width="90%"><?php _e( 'Phrase', 'wp-statistics' ); ?></td>
                                <td width="10%"><?php _e( 'Count', 'wp-statistics' ); ?></td>
                            </tr>
							<?php foreach ( $result as $item ) {
								$i ++;
								echo "<tr>";
								echo "<td>{$item->words}</td>";
								echo "<td>{$item->count}</td>";
								echo "</tr>";
							}
							}
							}
							?>
                        </table>
                    </div>
                </div>
				<?php if ( $total > 0 ) {
					wp_statistics_paginate_links( array(
						'item_per_page' => $items_per_page,
						'total'         => $total,
						'current'       => $page,
					) );
				} ?>
            </div>
        </div>
    </div>
</div>