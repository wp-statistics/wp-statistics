<div class="wps-mb-16"><?php echo esc_html__('IP addresses are not being stored in the database. Visitors are identified using anonymous, non-reversible hashes generated from a daily rotating salt combined with the IP address and user agent.', 'wp-statistics'); ?></div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head">
        <?php echo esc_html__('How It Works?', 'wp-statistics'); ?>
    </div>
    <div class="wps-audit-card__suggestion-text">
        <ol>
            <li><?php echo wp_kses(
                    __('<b>Unique Visitor Counting:</b> The system counts unique visitors by hashing a combination of the IP address, User-Agent string, and a daily-changing salt. This ensures each visitor\'s identifier is unique and secure for that day.', 'wp-statistics'),
                    array('b' => array())
                ); ?></li>
            <li><?php echo wp_kses(
                    __('<b>Privacy Enhancement:</b> No raw IP addresses are stored, making re-identification of visitors impossible. This aligns with stringent privacy regulations.', 'wp-statistics'),
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
                    __('<b>Maintain Current Setting:</b> Keeping IP storage disabled is recommended to uphold the highest standards of user privacy and security.', 'wp-statistics'),
                    array('b' => array())
                ); ?></li>
        </ol>
    </div>
</div>
