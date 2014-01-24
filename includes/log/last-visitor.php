<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('.show-map').click(function(){
			alert('<?php _e('To be added soon', 'wp_statistics'); ?>');
		});

	postboxes.add_postbox_toggles(pagenow);
	});
</script>

<div class="wrap">
	<?php screen_icon('options-general'); ?>
	<h2><?php _e('Recent Visitors', 'wp_statistics'); ?></h2>
	<ul class="subsubsub">
		<li class="all"><a <?php if(!$agent) { echo 'class="current"'; } ?>href="?page=wps_visitors_menu"><?php _e('All', 'wp_statistics'); ?> <span class="count">(<?php echo $total; ?>)</span></a>|</li>
<?php
		$Browsers = wp_statistics_ua_list();
		$i = 0;
		$Total = count( $Browsers );
		$spacer = "|";
		
		foreach( $Browsers as $Browser )
			{
			$i++;
			if($agent == $Browser) { $current = 'class="current" '; } else { $current = ""; }
			if( $i == $Total ) { $spacer = ""; }
			echo "		<li><a " . $current . "href='?page=wps_visitors_menu&agent=" . $Browser . "'> " . __($Browser, 'wp_statistics') ." <span class='count'>(" . wp_statistics_useragent($Browser) .")</span></a>" . $spacer . "</li>";
			}
?>
	</ul>
	<div class="postbox-container" id="last-log">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Recent Visitors', 'wp_statistics'); ?></span></h3>
					<div class="inside">
							<?php
								// Instantiate pagination object with appropriate arguments
								$pagesPerSection = 10;
								$options = array(25, "All");
								$stylePageOff = "pageOff";
								$stylePageOn = "pageOn";
								$styleErrors = "paginationErrors";
								$styleSelect = "paginationSelect";

								$Pagination = new Pagination($total, $pagesPerSection, $options, false, $stylePageOff, $stylePageOn, $styleErrors, $styleSelect);
								
								$start = $Pagination->getEntryStart();
								$end = $Pagination->getEntryEnd();

								// Retrieve MySQL data
								if( $agent ) {
									$result = $wpdb->get_results("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE `agent` LIKE '%{$agent}%' ORDER BY `{$table_prefix}statistics_visitor`.`ID` DESC  LIMIT {$start}, {$end}");
								} else {
									$result = $wpdb->get_results("SELECT * FROM `{$table_prefix}statistics_visitor` ORDER BY `{$table_prefix}statistics_visitor`.`ID` DESC  LIMIT {$start}, {$end}");
								}
								
								// Check to see if User Agent logging is enabled.
								$DisplayUA = get_option( "wps_store_ua" );
								
								echo "<div class='log-latest'>";
								
								foreach($result as $items) {
								
									echo "<div class='log-item'>";
										echo "<div class='log-referred'><a href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank'>{$items->ip}</a>";
										if( $DisplayUA == TRUE) { echo "&nbsp-&nbsp" . $items->UAString; }
										echo "</div>";
										echo "<div class='log-ip'>{$items->last_counter} - <a href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank'>{$items->ip}</a>";
										echo "</div>";
										echo "<div class='clear'></div>";
										echo "<a class='show-map'><img src='".plugins_url('wp-statistics/images/map.png')."' class='log-tools' title='".__('Map', 'wp_statistics')."'/></a>";
										echo "<img src='".plugins_url('wp-statistics/images/flags/' . $items->location . '.png')."' title='".__('Country', 'wp_statistics')."' class='log-tools'/>";

										if( array_search( strtolower( $items->agent ), array( "chrome", "firefox", "msie", "opera", "safari" ) ) !== FALSE ) 
											{
											$AgentImage = $items->agent . ".png";
											}
										else
											{
											$AgentImage = "unknown.png";
											}
										
										echo "<a href='?page=wps_visitors_menu&agent={$items->agent}'><img src='".plugins_url('wp-statistics/images/').$AgentImage."' class='log-tools' title='{$items->agent}'/></a>";
										echo "<div class='log-url'><a href='{$items->referred}'><img src='".plugins_url('wp-statistics/images/link.png')."' title='{$items->referred}'/> ".substr($items->referred, 0, 100)."[...]</a></div>";
									echo "</div>";
									
								}
								
								echo "</div>";
							?>
					</div>
				</div>
				
				<div class="pagination-log">
					<?php echo $Pagination->display(); ?>
					<p id="result-log"><?php echo ' ' . __('Page', 'wp_statistics') . ' ' . $Pagination->getCurrentPage() . ' ' . __('From', 'wp_statistics') . ' ' . $Pagination->getTotalPages(); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>