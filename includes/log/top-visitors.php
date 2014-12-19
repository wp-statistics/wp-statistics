<script type="text/javascript">
	jQuery(document).ready(function(){
	postboxes.add_postbox_toggles(pagenow);
	});
</script>
<?php 
	include_once( dirname( __FILE__ ) . '/../functions/country-codes.php' ); 
	include_once( dirname( __FILE__ ) . '/widgets/top.visitors.php' );
?>
<div class="wrap">
	<?php screen_icon('options-general'); ?>
	<h2><?php _e('Top 100 Visitors Today', 'wp_statistics'); ?></h2>
	<div class="postbox-container" id="last-log" style="width: 100%;">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<div class="postbox">
					<div class="inside">
						<div class="inside">
						
						<?php wp_statistics_generate_top_visitors_postbox_content($ISOCountryCode, 'today', 100, false); ?>
					
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>