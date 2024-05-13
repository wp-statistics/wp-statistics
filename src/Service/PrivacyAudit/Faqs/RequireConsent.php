<?php 
namespace WP_Statistics\Service\PrivacyAudit\Faqs;

use WP_Statistics\Service\PrivacyAudit\Audits\AnonymizeIpAddress;
use WP_Statistics\Service\PrivacyAudit\Audits\HashIpAddress;
use WP_Statistics\Service\PrivacyAudit\Audits\StoreUserAgentString;
use WP_Statistics\Service\PrivacyAudit\Audits\RecordUserPageVisits;

class RequireConsent extends AbstractFaq
{
    static public function getStatus()
    {
        $requirements = [
            RecordUserPageVisits::isOptionPassed(),
            HashIpAddress::isOptionPassed(),
            AnonymizeIpAddress::isOptionPassed(),
            StoreUserAgentString::isOptionPassed()
        ];

        if (in_array(false, $requirements)) {
            return 'warning';
        }

        return 'success';
    }

    static public function getStates()
    {
        $status = self::getStatus();
        $title  = esc_html__('Does WP Statistics require consent?', 'wp-statistics');

        return [
            'success' => [
                'status'    => $status,
                'title'     => $title,
                'summary'   => esc_html__('User Consent Not Required.', 'wp-statistics'),
                'notes'     => __('<p>Based on your current configuration, WP Statistics is not recording any personal data. Consequently, under these settings, your use of WP Statistics does not require obtaining user consent. This approach aligns with privacy-focused analytics, minimizing compliance burdens while respecting user privacy.</p>', 'wp-statistics')
            ],
            'warning' => [
                'status'    => $status,
                'title'     => $title,
                'summary'   => esc_html__('User Consent Required.', 'wp-statistics'),
                'notes'     => __('<p>Your current settings indicate that WP Statistics is configured to collect personal data. In this case, it is essential to obtain user consent to comply with privacy laws and regulations. For detailed information on which settings may necessitate user consent and how to adjust them, please refer to the <b>Privacy Audit</b> section of this page.</p>', 'wp-statistics')
            ],
        ];
    }
    
}