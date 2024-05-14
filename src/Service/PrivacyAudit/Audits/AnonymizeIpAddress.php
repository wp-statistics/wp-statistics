<?php 
namespace WP_Statistics\Service\PrivacyAudit\Audits;

use WP_Statistics\Service\PrivacyAudit\Audits\Abstracts\ResolvableAudit;

class AnonymizeIpAddress extends ResolvableAudit
{
    public static $optionKey = 'anonymize_ips';

    public static function getPassedStateInfo()
    {
        return [
            'title' => esc_html__('The “Anonymize IP Addresses” feature is currently enabled on your website.', 'wp-statistics'),
            'notes' => __('<p>This setting ensures that the IP addresses of your visitors are anonymized by masking the last segment of their IP addresses before any processing or storage occurs. This significantly reduces the risk of personally identifying your users through their IP addresses.</p><p><b>How It Works?</b></p>
            <ol>
                <li><b>IPv4 Anonymization:</b> An IP address like 192.168.1.123 is transformed into 192.168.1.0.</li>
                <li><b>IPv6 Anonymization:</b> An IPv6 address like 2001:0db8:85a3:0000:0000:8a2e:0370:7334 becomes 2001:0db8:85a3::.</li>
                <li><b>Enhanced Privacy:</b> After anonymization, a hashing process is applied to the IP address, further securing user data and making re-identification through IP addresses highly unlikely.</li>
            </ol>
            <p><b>Best Practices:</b></p>
            <ol>
                <li><b>Privacy-First Approach:</b> Keeping this feature enabled is strongly recommended as it aligns with best data protection practices and compliance with various privacy laws and regulations.</li>
                <li><b>Transparency:</b> Ensure your privacy policy reflects this practice, enhancing trust with your site visitors.</li>
            </ol>', 'wp-statistics')
        ];
    }

    public static function getUnpassedStateInfo()
    {
        return [
            'title' => esc_html__('The “Anonymize IP Addresses” feature is currently disabled on your website.', 'wp-statistics'),
            'notes' => __('<p>This setting means that IP addresses could be stored or processed in their complete form, potentially allowing for the identification of individual users based on their IP addresses.</p>
            <p><b>Implications:</b></p>
            <ol>
                <li><b>Privacy Risks:</b> Without anonymization, IP addresses are considered Personally Identifiable Information (PII) and could pose privacy risks to your users.</li>
                <li><b>Legal Compliance:</b> Storing complete IP addresses may affect your compliance with privacy laws such as GDPR, requiring careful consideration and potentially additional safeguards.</li>
            </ol>
            <p><b>Recommendations:</b></p>
            <ol>
                <li><b>Enable Anonymization:</b> We recommend enabling the “Anonymize IP Addresses” feature to enhance user privacy and align with privacy laws and best practices.</li>
                <li><b>Review Privacy Practices:</b> If you have specific reasons for keeping this feature disabled, ensure you have adequate measures in place to protect user data and comply with applicable laws. This might include obtaining explicit consent from users for processing their complete IP addresses.</li>
            </ol>
            <div class="wps-privacy-list__content--note">
                <b>To enable this feature,</b> navigate to <b>Settings -> User Data Protection -> Anonymize IP Addresses</b> and check <b>"Enable"</b>.
            </div>', 'wp-statistics')
        ];
    }

    
}