<div class="wps-mb-16"><?php echo esc_html__('This setting ensures that the IP addresses of your visitors are anonymized by masking the last segment of their IP addresses before any processing or storage occurs. This significantly reduces the risk of personally identifying your users through their IP addresses.', 'wp-statistics') ?></div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('How It Works?', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <ol>
            <li><?php echo __('<b>IPv4 Anonymization:</b> An IP address like 192.168.1.123 is transformed into 192.168.1.0.', 'wp-statistics') ?></li>
            <li><?php echo __('<b>IPv6 Anonymization:</b> An IPv6 address like 2001:0db8:85a3:0000:0000:8a2e:0370:7334 becomes 2001:0db8:85a3::.', 'wp-statistics') ?></li>
            <li><?php echo __('<b>Enhanced Privacy:</b> After anonymization, a hashing process is applied to the IP address, further securing user data and making re-identification through IP addresses highly unlikely.', 'wp-statistics') ?></li>
        </ol>
    </div>
</div>
<div class="wps-audit-card__suggestion">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Best Practices', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <ol>
            <li><?php echo __('<b>Privacy-First Approach:</b> Keeping this feature enabled is strongly recommended as it aligns with best data protection practices and compliance with various privacy laws and regulations.', 'wp-statistics') ?></li>
            <li><?php echo __('<b>Transparency:</b> Ensure your privacy policy reflects this practice, enhancing trust with your site visitors.', 'wp-statistics') ?></li>
        </ol>
    </div>
</div>
