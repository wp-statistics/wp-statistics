<div class="wps-mb-16"><?php echo esc_html__('This setting applies a secure, irreversible hashing process to IP addresses, transforming them into unique, non-reversible strings. This method of pseudonymization protects user privacy by preventing the possibility of tracing the hash back to the original IP address.', 'wp-statistics'); ?></div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head">
        <?php echo esc_html__('How It Works?', 'wp-statistics'); ?>
    </div>
    <div class="wps-audit-card__suggestion-text">
        <ol>
            <li><?php echo wp_kses(
                    __('<b>Unique Visitor Counting:</b> The system counts unique visitors by hashing a combination of the IP address, User-Agent string, and a daily-changing salt. This ensures each visitor’s identifier is unique and secure for that day.', 'wp-statistics'),
                    array('b' => array())
                ); ?></li>
            <li><?php echo wp_kses(
                    __('<b>Privacy Enhancement:</b> Through this process, WP Statistics supports privacy compliance by anonymizing visitor data, thus aligning with stringent privacy regulations.', 'wp-statistics'),
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
                    __('<b>Maintain Enabled Status:</b> Keeping this feature enabled is recommended to uphold the highest standards of user privacy and security. This default setting ensures that all IP addresses are hashed from the start, offering a robust privacy-first approach.', 'wp-statistics'),
                    array('b' => array(), 'q' => array())
                ); ?></li>
            <li><?php echo wp_kses(
                    __('<b>Retroactive Hashing:</b> For users seeking to enhance privacy for previously stored data, WP Statistics offers guidance on converting existing IP addresses to hashes, further strengthening privacy measures.', 'wp-statistics'),
                    array('b' => array())
                ); ?></li>
        </ol>
    </div>
</div>

