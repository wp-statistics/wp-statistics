<?php
	if( array_key_exists( 'populate', $_GET ) ) {
		if( $_GET['populate'] == 1 ) {
			require_once( plugin_dir_path( __FILE__ ) . '../functions/geoip-populate.php' );
			echo wp_statistics_populate_geoip_info();
		}
	}
?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#tabs").tabs();
	} );
</script>
<div class="wrap">
	<div id="tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active ui-state-focus"><a href="#resources" class="ui-tabs-anchor"><span><?php _e('Resources/Information', 'wp_statistics'); ?></span></a></li>
			<li class="ui-state-default ui-corner-top"><a href="#export" class="ui-tabs-anchor"><span><?php _e('Export', 'wp_statistics'); ?></span></a></li>
			<li class="ui-state-default ui-corner-top"><a href="#purging" class="ui-tabs-anchor"><span><?php _e('Purging', 'wp_statistics'); ?></span></a></li>
			<?php if( version_compare(phpversion(), WP_STATISTICS_REQUIRED_GEOIP_PHP_VERSION, '>') ) { ?>
			<li class="ui-state-default ui-corner-top"><a href="#updates" class="ui-tabs-anchor"><span><?php _e('Updates', 'wp_statistics'); ?></span></a></li>
			<?php } ?>
		</ul>

		<div id="resources">
		<?php include( dirname( __FILE__ ) . '/tabs/wps-optimization-resources.php' ); ?>
		</div>

		<div id="export">
		<?php include( dirname( __FILE__ ) . '/tabs/wps-optimization-export.php' ); ?>
		</div>

		<div id="purging">
		<?php include( dirname( __FILE__ ) . '/tabs/wps-optimization-purging.php' ); ?>
		</div>

		<div id="updates">
		<?php include( dirname( __FILE__ ) . '/tabs/wps-optimization-updates.php' ); ?>
		</div>

	</div>
</div>