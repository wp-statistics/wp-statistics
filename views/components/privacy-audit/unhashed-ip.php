<div class="wps-mb-16"><?php echo esc_html__('Our system has identified that raw IP addresses are stored in your database, likely due to the “Hash IP Addresses” feature being disabled in the past. To enhance data protection and align with privacy best practices, converting these IP addresses to a hashed format is strongly recommended.') ?></div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('How to Convert IP Addresses to Hash?', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <?php echo __('<ol>
                <li>Go to the <b>Optimization</b> section.</li>
                <li>Select <b>Plugin Maintenance</b>.</li>
                <li>Choose <b>Convert IP Addresses to Hash</b> to start the conversion process.</li>
            </ol>
            <p>This step will transform all existing raw IP addresses in your database into hashed formats, significantly improving user privacy and your website’s compliance with data protection regulations.</p>', 'wp-statistics') ?>
    </div>
</div>
<div class="wps-audit-card__suggestion">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Need More Information?', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <?php echo __('<p>For a comprehensive guide on this process and to understand the benefits of IP address hashing, please refer to our detailed documentation: <a target="_blank" href="https://wp-statistics.com/resources/converting-ip-addresses-to-hash/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy">Converting IP Addresses to Hash</a>.</p>', 'wp-statistics') ?>
    </div>
</div>