<div class="wps-mb-16"><?php echo esc_html__('With this setting deactivated, IP addresses are not subjected to the secure, irreversible hashing process and may be stored in their original form. This could potentially allow for the identification of individual users, impacting user privacy and your site’s compliance with privacy laws.', 'wp-statistics') ?></div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Implications', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <ol>
            <li><?php echo __('<b>Reduced Privacy:</b> Disabling hashing reduces the level of privacy protection for user data, as IP addresses can be stored in a form that may be traceable to individuals.', 'wp-statistics') ?></li>
            <li><?php echo __('<b>Compliance Risks:</b> Operating without this layer of data protection may affect your website’s alignment with privacy regulations, necessitating additional safeguards or disclosures.', 'wp-statistics') ?></li>
        </ol>
    </div>
</div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Recommendations', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <ol>
            <li><?php echo __('<b>Consider Re-Enabling:</b> To enhance user privacy and ensure compliance with privacy laws, it is advisable to re-enable the “Hash IP Addresses” feature.', 'wp-statistics') ?></li>
            <li><?php echo __('<b>Disclosure:</b> If there are specific reasons for keeping hashing disabled, ensure transparent communication with your users by clearly disclosing this in your privacy policy, including the implications for their data privacy.', 'wp-statistics') ?></li>
        </ol>
    </div>
</div>
<div class="wps-audit-card__suggestion">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('How to Enable This Feature', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <p><?php echo esc_html__('To enable this feature, navigate to Settings -> Privacy & Data Protection -> Hash IP Addresses and check "Enable".', 'wp-statistics'); ?></p>
    </div>
</div>