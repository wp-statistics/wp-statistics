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
	$title = $post->post_title;
} else {
	$title = "";
}

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

if ( array_key_exists( 'page-id', $_GET ) ) {
	$page = intval( $_GET['page-id'] );
} else {
	if ( array_key_exists( 'prepage', $_GET ) ) {
		$page = intval( $_GET['prepage'] );
	} else {
		$page = 0;
	}
}

$urlfields = '&prepage=' . $pageid;
$html      = __( 'Select Page', 'wp-statistics' ) . ': ';
$html      .= wp_dropdown_pages( array( 'selected' => $pageid, 'echo' => 0, 'name' => 'page-id' ) );
$html      .= '<input type="submit" value="' . __( 'Select', 'wp-statistics' ) . '" class="button-primary">';
$html      .= '<br>';
?>
<div class="wrap">
    <h2><?php echo sprintf( __( 'Page Trend for Post ID %s', 'wp-statistics' ), $pageid ) . ' - ' . $title; ?></h2>
	<?php wp_statistics_date_range_selector( WP_Statistics::$page['pages'], $daysToDisplay, null, null, $urlfields, $html ); ?>
    <div class="postbox-container" id="last-log">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
					<?php $paneltitle = __( 'Page Trend', 'wp-statistics' ); ?>
                    <button class="handlediv" type="button" aria-expanded="true">
						<span class="screen-reader-text"><?php printf(
								__( 'Toggle panel: %s', 'wp-statistics' ),
								$paneltitle
							); ?></span>
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