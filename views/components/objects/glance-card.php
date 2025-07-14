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
                            <?php echo esc_html($metric['label'] ?? ''); ?>
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
                            <?php elseif (isset($metric['value'])): ?>
                                <span title="<?php echo esc_html($metric['value'] ?? 'No data'); ?>">
                                    <?php if ($metric['value']): ?>
                                        <?php echo esc_html($metric['value']); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </span>
                            <?php else: ?>
                                <span>-</span>
                            <?php endif; ?>
                            <?php if (isset($metric['change']) && ( !empty($metric['link-title'])  || !empty($metric['value']) ) ): ?>
                                <span class="wps-at-a-glance-change <?php echo esc_attr($metric['change'] > 0 ? 'wps-glance-positive' : ($metric['change'] < 0 ? 'wps-glance-negative' : '')); ?>">
                                   <?php echo esc_html($metric['change'] > 0 ? '+' : '') . esc_html($metric['change']) . '%'; ?>
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