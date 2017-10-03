<script type="text/javascript">
    jQuery(document).ready(function () {
        postboxes.add_postbox_toggles(pagenow);
    });
</script>
<?php
$search_engines = wp_statistics_searchengine_list();

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

list( $daysToDisplay, $rangestart_utime, $rangeend_utime ) = wp_statistics_date_range_calculator( $daysToDisplay, $rangestart, $rangeend );
$daysInThePast = round( ( time() - $rangeend_utime ) / 86400, 0 );

$total_stats = $WP_Statistics->get_option( 'chart_totals' );
$date        = array();
$stats       = array();
$total_daily = array();

foreach ( $search_engines as $se ) {
	for ( $i = $daysToDisplay; $i >= 0; $i -- ) {
		if ( ! array_key_exists( $i, $total_daily ) ) {
			$total_daily[ $i ] = 0;
		}

		$stat                   = wp_statistics_searchengine( $se['tag'], '-' . ( $i + $daysInThePast ) );
		$stats[ $se['name'] ][] = $stat;
		$total_daily[ $i ]      += $stat;
	}
}

for ( $i = $daysToDisplay; $i >= 0; $i -- ) {
	$date[] = "'" . $WP_Statistics->Real_Current_Date( 'M j', '-' . ( $i + $daysInThePast ), $rangeend_utime ) . "'";
}
?>
<div class="wrap">
    <h2><?php _e( 'Search Engine Referral Statistics', 'wp-statistics' ); ?></h2>
	<?php wp_statistics_date_range_selector( WP_STATISTICS_SEARCHES_PAGE, $daysToDisplay ); ?>
    <div class="postbox-container" id="last-log">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
					<?php $paneltitle = __( 'Search Engine Referral Statistics', 'wp-statistics' ); ?>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf( __( 'Toggle panel: %s', 'wp-statistics' ), $paneltitle ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></span></h2>
                    <div class="inside">
                        <canvas id="search-stats" height="80"></canvas>
                        <script>
                            var colors = [];
                            colors['baidu'] = ['rgba(35, 25, 220, 0.2)', 'rgba(35, 25, 220, 1)'];
                            colors['bing'] = ['rgba(12, 132, 132, 0.2)', 'rgba(12, 132, 132, 1)'];
                            colors['duckduckgo'] = ['rgba(222, 88, 51, 0.2)', 'rgba(222, 88, 51, 1)'];
                            colors['google'] = ['rgba(23, 107, 239, 0.2)', 'rgba(23, 107, 239, 1)'];
                            colors['yahoo'] = ['rgba(64, 0, 144, 0.2)', 'rgba(64, 0, 144, 1)'];
                            colors['yandex'] = ['rgba(255, 219, 77, 0.2)', 'rgba(255, 219, 77, 1)'];
                            colors['ask'] = ['rgba(205, 0, 0, 0.2)', 'rgba(205, 0, 0, 1)'];
                            colors['clearch'] = ['rgba(13, 0, 76, 0.2)', 'rgba(13, 0, 76, 1)'];

                            var ctx = document.getElementById("search-stats").getContext('2d');
                            var ChartJs = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: [<?php echo implode( ', ', $date ); ?>],
                                    datasets: [
										<?php foreach ( $search_engines as $se ): ?>
                                        {
                                            label: '<?php echo $se['name']; ?>',
                                            data: [<?php echo implode( ',', $stats[ $se['name'] ] ); ?>],
                                            backgroundColor: colors['<?php echo $se['tag']; ?>'][0],
                                            borderColor: colors['<?php echo $se['tag']; ?>'][1],
                                            borderWidth: 1,
                                            fill: true,
                                        },
										<?php endforeach; ?>
										<?php if ( $total_stats == 1 ) : ?>
                                        {
                                            label: '<?php _e( 'Total', 'wp-statistics' ); ?>',
                                            data: [<?php echo implode( ',', $total_daily ); ?>],
                                            backgroundColor: 'rgba(180, 180, 180, 0.2)',
                                            borderColor: 'rgba(180, 180, 180, 1)',
                                            borderWidth: 1,
                                            fill: false,
                                        },
										<?php endif;?>
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    legend: {
                                        position: 'bottom',
                                    },
                                    title: {
                                        display: true,
                                        text: '<?php echo sprintf( __( 'Search engine referrals in the last %s days', 'wp-statistics' ), $daysToDisplay ); ?>'
                                    },
                                    tooltips: {
                                        mode: 'index',
                                        intersect: false,
                                    },
                                    scales: {
                                        yAxes: [{
                                            ticks: {
                                                beginAtZero: true
                                            }
                                        }]
                                    }
                                }
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
