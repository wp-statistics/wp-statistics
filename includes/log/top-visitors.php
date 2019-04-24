<script type="text/javascript">
    jQuery(document).ready(function () {
        postboxes.add_postbox_toggles(pagenow);
    });
</script>
<?php
$ISOCountryCode = $WP_Statistics->get_country_codes();
include( WP_Statistics::$reg['plugin-dir'] . 'includes/log/widgets/top.visitors.php' );
?>
<div class="wrap wps-wrap">
	<?php WP_Statistics_Admin_Pages::show_page_title( __( 'Top 100 Visitors Today', 'wp-statistics' ) ); ?>
	<?php
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_register_style( 'jquery-ui-smoothness-css', WP_Statistics::$reg['plugin-url'] . 'assets/css/jquery-ui-smoothness.min.css' );
	wp_enqueue_style( 'jquery-ui-smoothness-css' );

	$current = 0;
	$statsdate  = $WP_Statistics->Current_Date( get_option( "date_format" ), '-' . $current );
	$rang_start = $WP_Statistics->Current_Date( "Y-m-d" );
	if ( isset( $_GET['statsdate'] ) and strtotime( $_GET['statsdate'] ) != false ) {
		$statsdate  = date( get_option( "date_format" ), strtotime( $_GET['statsdate'] ) );
		$rang_start = date( "Y-m-d", strtotime( $_GET['statsdate'] ) );
	}

	echo '<br><form method="get">' . "\r\n";
	echo ' ' . __( 'Date', 'wp-statistics' ) . ': ';

	echo '<input type="hidden" name="page" value="' . WP_Statistics::$page['top-visitors'] . '">' . "\r\n";
	echo '<input type="text" size="18" name="statsdate" id="statsdate" value="' . htmlentities( $statsdate, ENT_QUOTES ) . '" autocomplete="off" placeholder="' . __( wp_statistics_dateformat_php_to_jqueryui( get_option( "date_format" ) ), 'wp-statistics' ) . '"> <input type="submit" value="' . __( 'Go', 'wp-statistics' ) . '" class="button-primary">' . "\r\n";
	echo '<input type="hidden" name="statsdate" id="stats-date" value="' . $rang_start . '">';
	echo '</form>' . "\r\n";

	echo '<script src="' . WP_Statistics::$reg['plugin-url'] . 'assets/js/moment.min.js?ver=2.24.0"></script>';
	echo '<script>
        jQuery(function() { 
        jQuery( "#statsdate" ).datepicker({dateFormat: \'' . wp_statistics_dateformat_php_to_jqueryui( get_option( "date_format" ) ) . '\', 
        onSelect: function(selectedDate) {
            if (selectedDate.length > 0) {
                jQuery("#stats-date").val(moment(selectedDate, \'' . wp_statistics_convert_php_to_moment_js( get_option( "date_format" ) ) . '\').format(\'YYYY-MM-DD\'));
            }
        }
        });
        });
        </script>' . "\r\n";

	?>
    <div class="postbox-container" id="last-log" style="width: 100%;">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
					<?php $paneltitle = __( 'Top Visitors', 'wp-statistics' ); ?>
                    <button class="handlediv" type="button" aria-expanded="true">
						<span class="screen-reader-text"><?php printf(
								__( 'Toggle panel: %s', 'wp-statistics' ),
								$paneltitle
							); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></h2>

                    <div class="inside">

						<?php wp_statistics_generate_top_visitors_postbox_content(
							$ISOCountryCode,
							$statsdate,
							100,
							false
						); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>