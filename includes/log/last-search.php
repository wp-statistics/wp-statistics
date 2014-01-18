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
	<h2><?php _e('Latest search words', 'wp_statistics'); ?></h2>
	<ul class="subsubsub">
<?php
		$search_result_count = count( $search_result );
		$i = 0;
		$separator = '|';
		
		foreach( $search_result as $key => $value ) {
			$i++;
			
			if( $i == $search_result_count ) { $separator = ''; }
			
			if( $key == 'All' )
				{
				$tag = '';
				$name = 'All';
				}
			else
				{
				$tag = $search_engines[$key]['tag'];
				$name = $search_engines[$key]['name'];
				}
			
			echo "<li><a href='?page=wp-statistics/wp-statistics.php&type=last-all-search&referred={$tag}'>" . __($name, 'wp_statistics') . " <span class='count'>({$value})</span></a>{$separator}</li>";
		}
?>
	</ul>
	<div class="postbox-container" id="last-log">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Latest search words', 'wp_statistics'); ?></span></h3>
					<div class="inside">
						<div class='log-latest'>
							<?php
								if( $total > 0 ) {
									$wpstats = new WP_Statistics();
									
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
									if( $referred && $referred != "") {
										$search_query = wp_statistics_Searchengine_query($referred);
									} else {
										$search_query = wp_statistics_Searchengine_query('all');
									}

									$result = $wpdb->get_results("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE {$search_query} ORDER BY `{$table_prefix}statistics_visitor`.`ID` DESC  LIMIT {$start}, {$end}");
									
									foreach($result as $items) {
									
										if( !$wpstats->Search_Engine_QueryString($items->referred) ) continue;
										
										echo "<div class='log-item'>";
											echo "<div class='log-referred'>".substr($wpstats->Search_Engine_QueryString($items->referred), 0, 100)."</div>";
											echo "<div class='log-ip'>{$items->last_counter} - <a href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank'>{$items->ip}</a></div>";
											echo "<div class='clear'></div>";
											echo "<a class='show-map'><img src='".plugins_url('wp-statistics/images/map.png')."' class='log-tools' title='".__('Map', 'wp_statistics')."'/></a>";
											
											$this_search_engine = $wpstats->Search_Engine_Info($items->referred);
											echo "<a href='?page=wp-statistics/wp-statistics.php&type=last-all-search&referred={$this_search_engine['tag']}'><img src='".plugins_url('wp-statistics/images/' . $this_search_engine['image'])."' class='log-tools' title='".__($this_search_engine['name'], 'wp_statistics')."'/></a>";
											
											echo "<a href='?page=wp-statistics/wp-statistics.php&type=last-all-visitor&agent={$items->agent}'><img src='".plugins_url('wp-statistics/images/').$items->agent.".png' class='log-tools' title='{$items->agent}'/></a>";
											echo "<div class='log-url'><a href='{$items->referred}'><img src='".plugins_url('wp-statistics/images/link.png')."' title='{$items->referred}'/> ".substr($items->referred, 0, 100)."[...]</a></div>";
										echo "</div>";
									}
								}

								echo "</div>";
							?>
					</div>
				</div>
				
				<div class="pagination-log">
					<?php if( $total > 0 ) { echo $Pagination->display(); ?>
					<p id="result-log"><?php echo ' ' . __('Page', 'wp_statistics') . ' ' . $Pagination->getCurrentPage() . ' ' . __('From', 'wp_statistics') . ' ' . $Pagination->getTotalPages(); ?></p>
					<?php }?>
				</div>
			</div>
		</div>
	</div>
</div>