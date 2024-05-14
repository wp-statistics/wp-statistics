<?php 
namespace WP_Statistics\Service\PrivacyAudit\Faqs;

use WP_Statistics\Service\PrivacyAudit\Audits\AnonymizeIpAddress;
use WP_Statistics\Service\PrivacyAudit\Audits\HashIpAddress;
use WP_Statistics\Service\PrivacyAudit\Audits\StoreUserAgentString;
use WP_Statistics\Service\PrivacyAudit\Audits\RecordUserPageVisits;

class RequireMention extends AbstractFaq
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
        $title  = esc_html__('Do I need to mention WP Statistics in my privacy policy?', 'wp-statistics');

        return [
            'success' => [
                'status'    => $status,
                'title'     => $title,
                'summary'   => esc_html__('Mentioning Not Strictly Necessary', 'wp-statistics'),
                'notes'     => __('<p>According to your current setup, WP Statistics is not configured to record any personal data. This means that technically, you do not need to mention WP Statistics in your privacy policy. However, to foster an environment of utmost transparency with your users, we still encourage mentioning the use of WP Statistics. This helps inform users about the analytics tools employed by your site, reinforcing trust through transparency.</p>', 'wp-statistics')
            ],
            'warning' => [
                'status'    => $status,
                'title'     => $title,
                'summary'   => esc_html__('Mentioning Required', 'wp-statistics'),
                'notes'     => __('<p>Your configuration indicates that WP Statistics collects personal data. In this scenario, it is crucial to mention WP Statistics in your privacy policy. This should include information on the type of data collected, its purpose, and how it is processed. Being transparent about the use of WP Statistics and its data handling practices is essential to comply with privacy regulations and to maintain trust with your website visitors</p><p>For more information on adjusting your settings to enhance privacy and for specifics on what to include in your  <b>privacy policy</b>, please see the Privacy Audit section of this page</p>', 'wp-statistics')
            ],
        ];
    }    
}