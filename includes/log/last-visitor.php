<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('.show-map').click(function(){
			alert('<?php _e('To be added soon', 'wp_statistics'); ?>');
		});
	});
</script>

<div class="wrap">
	<?php screen_icon('options-general'); ?>
	<h2><?php _e('Recent Visitors', 'wp_statistics'); ?></h2>
	<ul class="subsubsub">
		<li class="all"><a <?php if(!$agent) { echo 'class="current"'; } ?>href="?page=wp-statistics/wp-statistics.php&type=last-all-visitor"><?php _e('All', 'wp_statistics'); ?> <span class="count">(<?php echo $result[2]; ?>)</span></a>|</li>
		<li><a <?php if($agent == 'Firefox') { echo 'class="current"'; } ?>href="?page=wp-statistics/wp-statistics.php&type=last-all-visitor&agent=Firefox"> <?php echo _e('Firefox', 'wp_statistics'); ?> <span class="count">(<?php echo wp_statistics_useragent('Firefox'); ?>)</span></a>|</li>
		<li><a <?php if($agent == 'IE') { echo 'class="current"'; } ?>href="?page=wp-statistics/wp-statistics.php&type=last-all-visitor&agent=IE"> <?php echo _e('IE', 'wp_statistics'); ?> <span class="count">(<?php echo wp_statistics_useragent('IE'); ?>)</span></a>|</li>
		<li><a <?php if($agent == 'Ipad') { echo 'class="current"'; } ?>href="?page=wp-statistics/wp-statistics.php&type=last-all-visitor&agent=Ipad"> <?php echo _e('Ipad', 'wp_statistics'); ?> <span class="count">(<?php echo wp_statistics_useragent('Ipad'); ?>)</span></a>|</li>
		<li><a <?php if($agent == 'Android') { echo 'class="current"'; } ?>href="?page=wp-statistics/wp-statistics.php&type=last-all-visitor&agent=Android"> <?php echo _e('Android', 'wp_statistics'); ?> <span class="count">(<?php echo wp_statistics_useragent('Android'); ?>)</span></a>|</li>
		<li><a <?php if($agent == 'Chrome') { echo 'class="current"'; } ?>href="?page=wp-statistics/wp-statistics.php&type=last-all-visitor&agent=Chrome"> <?php echo _e('Chrome', 'wp_statistics'); ?> <span class="count">(<?php echo wp_statistics_useragent('Chrome'); ?>)</span></a>|</li>
		<li><a <?php if($agent == 'Safari') { echo 'class="current"'; } ?>href="?page=wp-statistics/wp-statistics.php&type=last-all-visitor&agent=Safari"> <?php echo _e('Safari', 'wp_statistics'); ?> <span class="count">(<?php echo wp_statistics_useragent('Safari'); ?>)</span></a>|</li>
		<li><a <?php if($agent == 'Opera') { echo 'class="current"'; } ?>href="?page=wp-statistics/wp-statistics.php&type=last-all-visitor&agent=Opera"> <?php echo _e('Opera', 'wp_statistics'); ?> <span class="count">(<?php echo wp_statistics_useragent('Opera'); ?>)</span></a>|</li>
		<li><a <?php if($agent == 'unknown') { echo 'class="current"'; } ?>href="?page=wp-statistics/wp-statistics.php&type=last-all-visitor&agent=unknown"> <?php echo _e('Other', 'wp_statistics'); ?> <span class="count">(<?php echo wp_statistics_useragent('unknown'); ?>)</span></a></li>
	</ul>
	<div class="postbox-container" id="last-log">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Recent Visitors', 'wp_statistics'); ?></span></h3>
					<div class="inside">
							<?php
								global $s;
								
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
								
								echo "<div class='log-latest'>";
								
								foreach($result as $items) {
								
									echo "<div class='log-item'>";
										echo "<div class='log-referred'><a href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank'>{$items->ip}</a></div>";
										echo "<div class='log-ip'>{$items->last_counter} - <a href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank'>{$items->ip}</a></div>";
										echo "<div class='clear'></div>";
										echo "<a class='show-map'><img src='".plugins_url('wp-statistics/images/map.png')."' class='log-tools' title='".__('Map', 'wp_statistics')."'/></a>";
										echo "<a href='?page=wp-statistics/wp-statistics.php&type=last-all-visitor&agent={$items->agent}'><img src='".plugins_url('wp-statistics/images/').$items->agent.".png' class='log-tools' title='{$items->agent}'/></a>";
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