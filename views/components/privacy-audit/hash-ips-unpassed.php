<div class="wps-mb-16"><?php echo esc_html__('With this setting enabled, raw IP addresses are stored in the database alongside visitor records. This allows for identification of individual users, impacting user privacy and your site\'s compliance with privacy laws.', 'wp-statistics') ?></div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Implications', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <ol>
            <li><?php echo __('<b>Reduced Privacy:</b> Storing raw IP addresses means visitor data is considered Personally Identifiable Information (PII), which could pose privacy risks to your users.', 'wp-statistics') ?></li>
            <li><?php echo __('<b>Compliance Risks:</b> Storing complete IP addresses may affect your compliance with privacy regulations such as GDPR, requiring additional safeguards or disclosures.', 'wp-statistics') ?></li>
        </ol>
    </div>
</div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Recommendations', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <ol>
            <li><?php echo __('<b>Consider Disabling:</b> To enhance user privacy and ensure compliance with privacy laws, it is advisable to disable the "Store IP Addresses" feature.', 'wp-statistics') ?></li>
            <li><?php echo __('<b>Disclosure:</b> If you need to store IP addresses, ensure transparent communication with your users by clearly disclosing this in your privacy policy.', 'wp-statistics') ?></li>
        </ol>
    </div>
</div>
<div class="wps-audit-card__suggestion">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('How to Disable This Feature', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <p><?php echo esc_html__('To disable this feature, navigate to Settings -> Privacy & Data Protection -> Store IP Addresses and toggle it off.', 'wp-statistics'); ?></p>
    </div>
</div>
