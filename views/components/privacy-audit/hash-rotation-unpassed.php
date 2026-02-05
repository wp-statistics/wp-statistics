<div class="wps-mb-16"><?php echo esc_html__('Hash rotation is disabled. The same visitor will always produce the same hash, which means visitors can be tracked indefinitely.', 'wp-statistics'); ?></div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Implications', 'wp-statistics'); ?></div>
    <div class="wps-audit-card__suggestion-text">
        <ol>
            <li><?php echo wp_kses(
                    __('<b>Reduced Privacy:</b> Without salt rotation, visitor hashes remain constant, allowing long-term visitor identification and tracking.', 'wp-statistics'),
                    array('b' => array())
                ); ?></li>
            <li><?php echo wp_kses(
                    __('<b>Compliance Risks:</b> Permanent hashes may be considered persistent identifiers under privacy regulations such as GDPR.', 'wp-statistics'),
                    array('b' => array())
                ); ?></li>
        </ol>
    </div>
</div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Recommendations', 'wp-statistics'); ?></div>
    <div class="wps-audit-card__suggestion-text">
        <ol>
            <li><?php echo wp_kses(
                    __('<b>Enable Rotation:</b> Set the hash rotation interval to "Daily" for the best privacy protection.', 'wp-statistics'),
                    array('b' => array())
                ); ?></li>
        </ol>
    </div>
</div>
<div class="wps-audit-card__suggestion">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('How to Enable', 'wp-statistics'); ?></div>
    <div class="wps-audit-card__suggestion-text">
        <p><?php echo esc_html__('Navigate to Settings > Privacy & Data Protection > Hash Rotation Interval and select "Daily".', 'wp-statistics'); ?></p>
    </div>
</div>
