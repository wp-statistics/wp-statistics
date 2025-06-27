<div class="wps-card">
    <div class="wps-card__title">
        <h2><?php esc_html_e('At a Glance', 'wp-statistics') ?></h2>
    </div>
    <div class="inside">
        <div class="wps-at-a-glance">
            <?php if (!empty($metrics)): ?>
                <?php foreach ($metrics as $metric): ?>
                    <div class="wps-at-a-glance-item">
                        <span class="wps-at-a-glance-label">
                            <?php echo esc_html($metric['label']); ?>
                            <?php if (isset($metric['tooltip'])): ?>
                            <span class="wps-tooltip" title="<?php echo esc_html($metric['tooltip']) ?>"><i class="wps-tooltip-icon info"></i></span>
                            <?php endif; ?>
                        </span>
                        <span class="wps-at-a-glance-value" title="<?php echo esc_html(isset($metric['value']) ? $metric['value'] : '-'); ?>">
                            <?php echo esc_html($metric['value'] !== null && $metric['value'] !== '' ? $metric['value'] : '-'); ?>
                        </span>
                        <?php if (isset($metric['change'])): ?>
                            <span class="wps-at-a-glance-change <?php echo esc_attr($metric['change'] >= 0 ? 'wps-positive' : 'wps-negative'); ?>">
                                <?php echo esc_html($metric['change'] >= 0 ? '+' : '') . esc_html($metric['change']) . '%'; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>