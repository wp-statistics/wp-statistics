<div class="wps-mb-16"><?php echo esc_html__('The hash rotation interval is set to weekly or monthly. This allows returning-visitor detection within the period, but daily rotation would provide stronger privacy.', 'wp-statistics'); ?></div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head">
        <?php echo esc_html__('Why Daily Is Recommended', 'wp-statistics'); ?>
    </div>
    <div class="wps-audit-card__suggestion-text">
        <?php echo esc_html__('Shorter rotation intervals reduce the window during which a visitor can be correlated across visits, improving their privacy. Daily rotation is the best balance of analytics accuracy and privacy.', 'wp-statistics'); ?>
    </div>
</div>
<div class="wps-audit-card__suggestion">
    <div class="wps-audit-card__suggestion-head">
        <?php echo esc_html__('How to Change', 'wp-statistics'); ?>
    </div>
    <div class="wps-audit-card__suggestion-text">
        <p><?php echo esc_html__('To change the rotation interval, navigate to Settings > Privacy & Data Protection > Hash Rotation Interval and select "Daily".', 'wp-statistics'); ?></p>
    </div>
</div>
