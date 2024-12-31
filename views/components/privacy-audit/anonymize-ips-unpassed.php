<div class="wps-mb-16"><?php echo esc_html__('This setting means that IP addresses could be stored or processed in their complete form, potentially allowing for the identification of individual users based on their IP addresses.', 'wp-statistics') ?></div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Implications', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <ol>
            <li><?php echo __('<b>Privacy Risks:</b> Without anonymization, IP addresses are considered Personally Identifiable Information (PII) and could pose privacy risks to your users.', 'wp-statistics') ?></li>
            <li><?php echo __('<b>Legal Compliance:</b> Storing complete IP addresses may affect your compliance with privacy laws such as GDPR, requiring careful consideration and potentially additional safeguards.', 'wp-statistics') ?></li>
        </ol>
    </div>
</div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Recommendations', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <ol>
            <li><?php echo __('<b>Enable Anonymization:</b> We recommend enabling the “Anonymize IP Addresses” feature to enhance user privacy and align with privacy laws and best practices.', 'wp-statistics') ?></li>
            <li><?php echo __('<b>Review Privacy Practices:</b> If you have specific reasons for keeping this feature disabled, ensure you have adequate measures in place to protect user data and comply with applicable laws. This might include obtaining explicit consent from users for processing their complete IP addresses.', 'wp-statistics') ?></li>
        </ol>
    </div>
</div>
<div class="wps-audit-card__suggestion">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('How to Enable This Feature', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <p><?php echo esc_html__('To enable this feature, navigate to Settings -> Privacy & Data Protection -> Anonymize IP Addresses and check "Enable".', 'wp-statistics'); ?></p>
    </div>
</div>