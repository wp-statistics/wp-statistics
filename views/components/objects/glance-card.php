<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly ?>
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

                            <?php elseif (isset($metric['value']) || isset($metric['not_applicable'])): ?>
                                <?php $metricValue = $metric['value'] ?? null; ?>
                                <span title="<?php echo esc_html($metricValue !== '' && $metricValue !== null ? $metricValue : __('No data', 'wp-statistics')); ?>">
                                    <?php if (isset($metric['not_applicable']) && $metric['not_applicable']): ?>
                                        –
                                    <?php elseif ($metricValue === '' || $metricValue === null): ?>
                                        –
                                    <?php elseif ($metricValue === 0 || $metricValue === '0'): ?>
                                        0
                                    <?php else: ?>
                                        <?php echo esc_html($metricValue); ?>
                                    <?php endif; ?>
                                </span>
                            <?php else: ?>
                                <span>–</span>
                            <?php endif; ?>
                            <?php if (isset($metric['change']) && $metric['change'] != 0 && (!empty($metric['link-title']) || isset($metric['value']))): ?>
                                <?php
                                $arrow_class = $metric['change'] > 0 ? 'wps-glance-up' : 'wps-glance-down';
                                $is_negative_polarity = isset($metric['polarity']) && $metric['polarity'] === 'negative';
                                $is_good_change = ($is_negative_polarity && $metric['change'] < 0) || (!$is_negative_polarity && $metric['change'] > 0);
                                $color_class = $is_good_change ? 'wps-glance-positive' : 'wps-glance-negative';
                                $changeValue = rtrim(rtrim(number_format(abs((float) $metric['change']), 1), '0'), '.');

                                // Build tooltip for comparison
                                $tooltip = '';
                                if (isset($metric['prev_value']) && isset($metric['current_value'])) {
                                    $tooltip = sprintf(
                                        '%s → %s (%s)',
                                        esc_html($metric['prev_value']),
                                        esc_html($metric['current_value']),
                                        esc_html($metric['period'] ?? __('vs previous period', 'wp-statistics'))
                                    );
                                }
                                ?>
                                <span class="wps-at-a-glance-change <?php echo esc_attr($arrow_class . ' ' . $color_class); ?><?php echo !empty($tooltip) ? ' wps-tooltip' : ''; ?>"
                                      <?php if (!empty($tooltip)): ?>title="<?php echo esc_attr($tooltip); ?>"<?php endif; ?>>
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