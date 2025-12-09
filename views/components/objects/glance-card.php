<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\DateRange;

// Get current and previous period for tooltip
$currentPeriod = DateRange::get();
$prevPeriod    = DateRange::getPrevPeriod();

/**
 * Format date for display in tooltip
 */
if (!function_exists('wps_format_period_date')) {
    function wps_format_period_date($date) {
        return date_i18n(get_option('date_format'), strtotime($date));
    }
}

$currentPeriodText = sprintf(
    '%s – %s',
    wps_format_period_date($currentPeriod['from']),
    wps_format_period_date($currentPeriod['to'])
);

$prevPeriodText = sprintf(
    '%s – %s',
    wps_format_period_date($prevPeriod['from']),
    wps_format_period_date($prevPeriod['to'])
);
?>
<div class="wps-card">
    <div class="wps-card__title">
        <h2><?php esc_html_e('At a Glance', 'wp-statistics'); ?></h2>
    </div>
    <div class="inside">
        <div class="wps-at-a-glance <?php echo isset($two_column) && $two_column ? 'wps-at-a-glance__two-col' : ''; ?>">
            <?php if (!empty($metrics) && is_array($metrics)): ?>
                <?php foreach ($metrics as $metric): ?>
                    <div class="wps-at-a-glance-item">
                        <!-- Metric Label -->
                        <span class="wps-at-a-glance-label" title="<?php echo esc_html($metric['label'] ?? ''); ?>">
                               <?php if (!empty($metric['icon'])): ?>
                                   <i class="wps-at-a-glance-icon <?php echo esc_attr($metric['icon']); ?>" aria-hidden="true" aria-label="<?php echo esc_attr($metric['label']); ?>"></i>
                               <?php endif; ?>
                            <?php if (!empty($metric['link-href'])): ?>
                                <a href="<?php echo esc_url($metric['link-href']); ?>"
                                   title="<?php echo esc_attr($metric['label']); ?>">
                                    <?php echo esc_html($metric['label'] ?? ''); ?>
                                </a>
                            <?php else: ?>
                                <?php echo esc_html($metric['label'] ?? ''); ?>
                            <?php endif; ?>
                            <?php if (!empty($metric['tooltip'])): ?>
                                <span class="wps-tooltip" title="<?php echo esc_html($metric['tooltip']); ?>">
                                    <i class="wps-tooltip-icon info"></i>
                                </span>
                            <?php endif; ?>
                        </span>
                        <span class="wps-at-a-glance-value<?php echo !empty($metric['link-href']) && !empty($metric['link-title']) ? ' wps-at-a-glance-link' : ''; ?>">
                            <?php if (!empty($metric['link-href']) && !empty($metric['link-title'])): ?>
                                <a href="<?php echo esc_url($metric['link-href']); ?>" title="<?php echo esc_html($metric['link-title'] ?? 'View Details'); ?>" target="_blank" class="wps-external-link">
                                    <?php echo esc_html($metric['link-title'] ?? 'View Details'); ?>
                                </a>


                            <?php elseif (isset($metric['score'])): ?>
                                <span title="<?php echo esc_html($metric['score']); ?>" class="wps-at-a-glance-score">
                                    <?php echo esc_html($metric['score']); ?><span> /100</span>
                                </span>

                            <?php elseif (isset($metric['value'])): ?>
                                <?php
                                // Show 0 instead of "-" when value exists but is zero
                                // Keep "-" only when metric is not applicable (indicated by 'not_applicable' key)
                                $displayValue = $metric['value'];
                                $showDash = isset($metric['not_applicable']) && $metric['not_applicable'];
                                ?>
                                <span title="<?php echo esc_html($metric['value'] ?? 'No data'); ?>">
                                    <?php if ($showDash): ?>
                                        –
                                    <?php else: ?>
                                        <?php echo esc_html($displayValue); ?>
                                    <?php endif; ?>
                                </span>
                            <?php else: ?>
                                <span>–</span>
                            <?php endif; ?>
                            <?php
                            // Only show change indicator if:
                            // 1. change is set
                            // 2. change is not zero (hide for cleaner UI)
                            // 3. there's a link-title or value present
                            $hasChange = isset($metric['change']) && $metric['change'] != 0;
                            $hasValue  = !empty($metric['link-title']) || isset($metric['value']);
                            if ($hasChange && $hasValue):
                                $arrow_class = $metric['change'] > 0 ? 'wps-glance-up' : ($metric['change'] < 0 ? 'wps-glance-down' : '');
                                $is_negative_polarity = isset($metric['polarity']) && $metric['polarity'] === 'negative';
                                $is_good_change = ($is_negative_polarity && $metric['change'] < 0) || (!$is_negative_polarity && $metric['change'] > 0);
                                $color_class = $is_good_change ? 'wps-glance-positive' : 'wps-glance-negative';

                                // Format change value - remove unnecessary decimals
                                $changeValue = abs((float) $metric['change']);
                                $changeValue = (floor($changeValue) == $changeValue) ? (int) $changeValue : number_format($changeValue, 1);

                                // Build tooltip with exact numbers and periods
                                $currentValue = isset($metric['current_value']) ? $metric['current_value'] : (isset($metric['value']) ? $metric['value'] : '');
                                $prevValue    = isset($metric['prev_value']) ? $metric['prev_value'] : '';

                                $tooltipParts = [];
                                if ($currentValue !== '' && $prevValue !== '') {
                                    $tooltipParts[] = sprintf(
                                        /* translators: %1$s: current value, %2$s: current period dates */
                                        esc_html__('Current: %1$s (%2$s)', 'wp-statistics'),
                                        $currentValue,
                                        $currentPeriodText
                                    );
                                    $tooltipParts[] = sprintf(
                                        /* translators: %1$s: previous value, %2$s: previous period dates */
                                        esc_html__('Previous: %1$s (%2$s)', 'wp-statistics'),
                                        $prevValue,
                                        $prevPeriodText
                                    );
                                } else {
                                    $tooltipParts[] = sprintf(
                                        /* translators: %s: previous period dates */
                                        esc_html__('Compared to %s', 'wp-statistics'),
                                        $prevPeriodText
                                    );
                                }
                                $changeTooltip = implode(' | ', $tooltipParts);
                                ?>
                                <span class="wps-at-a-glance-change <?php echo esc_attr($arrow_class . ' ' . $color_class); ?>" title="<?php echo esc_attr($changeTooltip); ?>">
                                    <?php echo esc_html($changeValue) . '%'; ?>
                                </span>
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p><?php esc_html_e('No metrics available.', 'wp-statistics'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>