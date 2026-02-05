<div class="wps-mb-16"><?php echo esc_html__('The hash rotation interval is set to daily. Visitor hashes change every day, making it impossible to track individuals across multiple days.', 'wp-statistics'); ?></div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head">
        <?php echo esc_html__('How It Works', 'wp-statistics'); ?>
    </div>
    <div class="wps-audit-card__suggestion-text">
        <ol>
            <li><?php echo wp_kses(
                    __('<b>Daily Salt Rotation:</b> A new random salt is generated each day. This salt is combined with visitor data to produce unique hashes.', 'wp-statistics'),
                    array('b' => array())
                ); ?></li>
            <li><?php echo wp_kses(
                    __('<b>Privacy Protection:</b> Because the salt changes daily, the same visitor produces a different hash each day, preventing long-term tracking.', 'wp-statistics'),
                    array('b' => array())
                ); ?></li>
        </ol>
    </div>
</div>
<div class="wps-audit-card__suggestion">
    <div class="wps-audit-card__suggestion-head">
        <?php echo esc_html__('Recommendations', 'wp-statistics'); ?>
    </div>
    <div class="wps-audit-card__suggestion-text">
        <ol>
            <li><?php echo wp_kses(
                    __('<b>Maintain Current Setting:</b> Daily rotation provides the strongest privacy protection and is recommended for most sites.', 'wp-statistics'),
                    array('b' => array())
                ); ?></li>
        </ol>
    </div>
</div>
