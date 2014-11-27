<script type="text/javascript">
	jQuery(document).ready(function(){
		postboxes.add_postbox_toggles(pagenow);
	});
</script>
<?php
	if( array_key_exists( 'page-uri', $_GET ) ) { $pageuri = $_GET['page-uri']; } else { $pageuri = null; }
	if( array_key_exists( 'page-id', $_GET ) ) { $pageid = $_GET['page-id']; } else { $pageid = null; }

	if( $pageuri && !$pageid ) { $pageid = wp_statistics_uri_to_id( $pageuri ); }
	
	$post = get_post($pageid);
	if( is_object($post) ) { $title = $post->post_title; } else { $title = ""; }
	
	$urlfields = "&page-id={$pageid}";
	if( $pageuri ) { $urlfields .= "&page-uri={$pageuri}"; }
	
	$daysToDisplay = 20; 
	
	if( array_key_exists('hitdays',$_GET) ) { 
		if( intval($_GET['hitdays']) > 0 ) { 
			$daysToDisplay = intval($_GET['hitdays']); 
		} 
	}
	
?>
<div class="wrap">
	<?php screen_icon('options-general'); ?>
	<h2><?php echo __('Page Trend for Post ID', 'wp_statistics') . ' ' .  $pageid . ' - ' . $title; ?></h2>

	<ul class="subsubsub">
		<?php $daysToDisplay = 20; if( array_key_exists('hitdays',$_GET) ) { if( intval($_GET['hitdays']) > 0 ) { $daysToDisplay = intval($_GET['hitdays']); } }?>
		<li class="all"><a <?php if($daysToDisplay == 10) { echo 'class="current"'; } ?>href="?page=wps_pages_menu&hitdays=10<?php echo $urlfields;?>"><?php _e('10 Days', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 20) { echo 'class="current"'; } ?>href="?page=wps_pages_menu&hitdays=20<?php echo $urlfields;?>"><?php _e('20 Days', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 30) { echo 'class="current"'; } ?>href="?page=wps_pages_menu&hitdays=30<?php echo $urlfields;?>"><?php _e('30 Days', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 60) { echo 'class="current"'; } ?>href="?page=wps_pages_menu&hitdays=60<?php echo $urlfields;?>"><?php _e('2 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 90) { echo 'class="current"'; } ?>href="?page=wps_pages_menu&hitdays=90<?php echo $urlfields;?>"><?php _e('3 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 180) { echo 'class="current"'; } ?>href="?page=wps_pages_menu&hitdays=180<?php echo $urlfields;?>"><?php _e('6 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 270) { echo 'class="current"'; } ?>href="?page=wps_pages_menu&hitdays=270<?php echo $urlfields;?>"><?php _e('9 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 365) { echo 'class="current"'; } ?>href="?page=wps_pages_menu&hitdays=365<?php echo $urlfields;?>"><?php _e('1 Year', 'wp_statistics'); ?></a></li>
	</ul>

	<div class="postbox-container" id="last-log">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Page Trend', 'wp_statistics'); ?></span></h3>
					<div class="inside">
						<?php include_once( dirname( __FILE__ ) . '/widgets/page.php'); wp_statistics_generate_page_postbox_content( $pageuri, $pageid, $daysToDisplay ); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>