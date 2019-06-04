<script type="text/javascript">
    jQuery(document).ready(function () {
        postboxes.add_postbox_toggles(pagenow);
    });
</script>
<?php
if ( array_key_exists( 'page-uri', $_GET ) ) {
	$pageuri = $_GET['page-uri'];
} else {
	$pageuri = null;
}
if ( array_key_exists( 'page-id', $_GET ) ) {
	$pageid = (int) $_GET['page-id'];
} else if ( array_key_exists( 'prepage', $_GET ) ) {
	$pageid = (int) $_GET['prepage'];
} else {
	$pageid = null;
}

if ( $pageuri && ! $pageid ) {
	$pageid = wp_statistics_uri_to_id( $pageuri );
}

$post = get_post( $pageid );
if ( is_object( $post ) ) {
	$title = esc_html( $post->post_title );
} else {
	$title = "";
}

//Set Default Time Picker Option
list( $daysToDisplay, $rangestart, $rangeend ) = wp_statistics_prepare_range_time_picker();

//Check Page
if ( array_key_exists( 'page-id', $_GET ) ) {
	$page = intval( $_GET['page-id'] );
} else {
	if ( array_key_exists( 'prepage', $_GET ) ) {
		$page = intval( $_GET['prepage'] );
	} else {
		$page = 0;
	}
}

//Check Page Type
$arg       = array();
$post_type = get_post_type( $page );
if ( $page > 0 and $post_type != "page" ) {
	$arg = array( "post_type" => get_post_type( $page ), "posts_per_page" => 50, "order" => "DESC" );
}

//Add arg to This Url
$url_fields = '&prepage=' . $pageid;

//Show Select Box Ui
$html = __( 'Select Page', 'wp-statistics' ) . ': ';
$html .= '<select name="page-id">';
foreach ( wp_statistics_get_post_list( $arg ) as $post_id => $post_title ) {
	$html .= '<option value="' . $post_id . '"' . selected( $post_id, $page, false ) . '>' . $post_title . '</option>';
}
$html .= '</select>';
$html .= ' <input type="submit" value="' . __( 'Select', 'wp-statistics' ) . '" class="button-primary">';
$html .= '<br>';
?>
<div class="wrap wps-wrap">
	<?php WP_Statistics_Admin_Pages::show_page_title( sprintf( __( 'Page Trend for Post ID %s', 'wp-statistics' ), $pageid ) . ' - ' . $title ); ?>
	<?php wp_statistics_date_range_selector( WP_Statistics::$page['pages'], $daysToDisplay, null, null, $url_fields, $html ); ?>
    <div class="postbox-container" id="last-log">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
					<?php $paneltitle = __( 'Page Trend', 'wp-statistics' ); ?>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf( __( 'Toggle panel: %s', 'wp-statistics' ), $paneltitle ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></span></h2>

                    <div class="inside">
						<?php
						include( WP_Statistics::$reg['plugin-dir'] . 'includes/log/widgets/page.php' );
						wp_statistics_generate_page_postbox_content(
							$pageuri,
							$pageid,
							$daysToDisplay,
							null,
							$rangestart,
							$rangeend
						); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>