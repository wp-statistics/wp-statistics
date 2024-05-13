<?php 
namespace WP_Statistics\Service\PrivacyAudit\Audits;

use WP_Statistics\Service\PrivacyAudit\Audits\Abstracts\ResolvableAudit;

class HashIpAddress extends ResolvableAudit
{
    public static $optionKey = 'hash_ips';

    public static function getPassedStateInfo()
    {
        return [
            'title' => esc_html__('The “Hash IP Addresses” feature is currently enabled on your website.', 'wp-statistics'),
            'notes' => __('<p>This setting applies a secure, irreversible hashing process to IP addresses, transforming them into unique, non-reversible strings. This method of pseudonymization protects user privacy by preventing the possibility of tracing the hash back to the original IP address.</p><p><b>How It Works?</b></p>
            <ol>
                <li><b>Unique Visitor Counting: </b> The system counts unique visitors by hashing a combination of the IP address, User-Agent string, and a daily-changing salt. This ensures each visitor’s identifier is unique and secure for that day.</li>
                <li><b>Privacy Enhancement: </b> Through this process, WP Statistics supports privacy compliance by anonymizing visitor data, thus aligning with stringent privacy regulations.</li>
            </ol>
            <p><b>Recommendations:</b></p>
            <ol>
                <li><b>Maintain Enabled Status: </b> Keeping this feature enabled is recommended to uphold the highest standards of user privacy and security. This default setting ensures that all IP addresses are hashed from the start, offering a robust privacy-first approach.</li>
                <li><b>Retroactive Hashing: </b> For users seeking to enhance privacy for previously stored data, WP Statistics offers guidance on converting existing IP addresses to hashes, further strengthening privacy measures.</li>
            </ol>', 'wp-statistics')
        ];
    }

    public static function getUnpassedStateInfo()
    {
        return [
            'title' => esc_html__('The “Hash IP Addresses” feature is currently disabled on your website.', 'wp-statistics'),
            'notes' => __('<p>With this setting deactivated, IP addresses are not subjected to the secure, irreversible hashing process and may be stored in their original form. This could potentially allow for the identification of individual users, impacting user privacy and your site’s compliance with privacy laws.</p>
            <p><b>Implications:</b></p>
            <ol>
                <li><b>Reduced Privacy:</b> Disabling hashing reduces the level of privacy protection for user data, as IP addresses can be stored in a form that may be traceable to individuals.</li>
                <li><b>Compliance Risks:</b> Operating without this layer of data protection may affect your website’s alignment with privacy regulations, necessitating additional safeguards or disclosures.</li>
            </ol>
            <p><b>Recommendations:</b></p>
            <ol>
                <li><b>Consider Re-Enabling:</b> To enhance user privacy and ensure compliance with privacy laws, it is advisable to re-enable the “Hash IP Addresses” feature.</li>
                <li><b>Disclosure:</b> If there are specific reasons for keeping hashing disabled, ensure transparent communication with your users by clearly disclosing this in your privacy policy, including the implications for their data privacy.</li>
            </ol>
            <div class="wps-privacy-list__content--note">
                <b>To enable this feature,</b> navigate to <b>Settings -> User Data Protection -> Hash IP Addresses</b> and check <b>"Enable"</b>.
            </div>', 'wp-statistics')
        ];
    }
}