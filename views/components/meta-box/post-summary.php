<?php

if (!defined('ABSPATH')) exit;

use WP_Statistics\Components\View;

// No summary (e.g. the post has not been saved yet). Do not `exit` here: this
// template is rendered into an AJAX response, and exiting would cut it off.
if (empty($summary)) {
    printf('<div class="o-wrap o-wrap--no-data">%s</div>', esc_html__('No data found for this post yet.', 'wp-statistics'));
    return;
}

// The 7-day row only makes sense once the post is at least a week old.
$showThisPeriod = (time() - strtotime($summary['publishDateString'])) >= WEEK_IN_SECONDS;

// Chart caption: which metric the bars show, and the busiest day.
$chartData     = !empty($summary['postChartData']) && is_array($summary['postChartData']) ? $summary['postChartData'] : [];
$chartSettings = !empty($summary['postChartSettings']) && is_array($summary['postChartSettings']) ? $summary['postChartSettings'] : [];
$chartMetric   = isset($chartSettings['metric']) && $chartSettings['metric'] === 'visitors' ? 'visitors' : 'views';

$peak = null;
foreach ($chartData as $day) {
    if (intval($day['hits']) > 0 && ($peak === null || intval($day['hits']) > intval($peak['hits']))) {
        $peak = $day;
    }
}
$firstDay = !empty($chartData) ? reset($chartData) : null;
$lastDay  = !empty($chartData) ? end($chartData) : null;
?>
<div class="wps-post-summary">
    <?php
    if ($showThisPeriod) {
        View::load('components/meta-box/post-summary-row', [
            'label'         => sprintf(
                // translators: 1: Start date - 2: End date.
                __('Last 7 days · %1$s - %2$s', 'wp-statistics'),
                $summary['fromString'],
                $summary['toString']
            ),
            'views'         => $summary['thisPeriodViews'],
            'visitors'      => $summary['thisPeriodVisitors'],
            'referrerUrl'   => $summary['thisPeriodTopReferrer'],
            'referrerCount' => $summary['thisPeriodTopReferrerCount'],
        ]);
    }

    View::load('components/meta-box/post-summary-row', [
        'label'         => __('All time', 'wp-statistics'),
        'views'         => $summary['totalViews'],
        'visitors'      => $summary['totalVisitors'],
        'referrerUrl'   => $summary['topReferrer'],
        'referrerCount' => $summary['topReferrerCount'],
    ]);
    ?>

    <?php if (!empty($chartData)) : ?>
        <div class="wps-post-summary__chart">
            <div class="wps-post-summary__caption">
                <span><?php
                    // The chart covers the Mini Chart range (14 days by default, up to a year), not the 7 days above: say so.
                    $chartDays = count($chartData);
                    echo esc_html(sprintf(
                        // translators: 1: "Views per day" or "Visitors per day" - 2: Number of days in the chart.
                        _n('%1$s · last %2$s day', '%1$s · last %2$s days', $chartDays, 'wp-statistics'),
                        $chartMetric === 'visitors' ? __('Visitors per day', 'wp-statistics') : __('Views per day', 'wp-statistics'),
                        number_format_i18n($chartDays)
                    ));
                    ?></span>
                <span>
                    <?php
                    if ($peak) {
                        echo esc_html(sprintf(
                            // translators: 1: Highest daily count - 2: Date of that day.
                            __('Peak %1$s on %2$s', 'wp-statistics'),
                            number_format_i18n(intval($peak['hits'])),
                            $peak['fullDate']
                        ));
                    } else {
                        echo $chartMetric === 'visitors' ? esc_html__('No visitors in this period', 'wp-statistics') : esc_html__('No views in this period', 'wp-statistics');
                    }
                    ?>
                </span>
            </div>
            <div class="c-wps-post-summary-panel-chart">
                <canvas id="postSummaryChart" height="64" role="img" aria-label="<?php
                echo esc_attr(sprintf(
                    // translators: 1: Start date - 2: End date.
                    $chartMetric === 'visitors' ? __('Visitors per day, %1$s to %2$s', 'wp-statistics') : __('Views per day, %1$s to %2$s', 'wp-statistics'),
                    $firstDay['fullDate'],
                    $lastDay['fullDate']
                ));
                ?>"></canvas>
            </div>
        </div>
    <?php endif; ?>

    <a class="wps-post-summary__link" href="<?php echo esc_url($summary['contentAnalyticsUrl']); ?>" target="_blank">
        <?php esc_html_e('View full analytics', 'wp-statistics'); ?>
        <span aria-hidden="true"><?php echo is_rtl() ? '&larr;' : '&rarr;'; ?></span>
    </a>
</div>
