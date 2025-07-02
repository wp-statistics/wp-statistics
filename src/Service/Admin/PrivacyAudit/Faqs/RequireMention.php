<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit\Faqs;

use WP_Statistics\Service\Admin\PrivacyAudit\Audits\AnonymizeIpAddress;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\HashIpAddress;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\RecordUserPageVisits;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\StoreUserAgentString;

class RequireMention extends AbstractFaq
{
    public static function getStates()
    {
        $status = self::getStatus();
        $title  = esc_html__('Do I need to mention WP Statistics in my privacy policy?', 'wp-statistics');
        $icon   = '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19.4527 9.63567L18.5952 13.2932C17.8602 16.4519 16.4077 17.7294 13.6777 17.4669C13.2402 17.4319 12.7677 17.3532 12.2602 17.2307L10.7902 16.8807C7.14141 16.0144 6.01266 14.2119 6.87016 10.5544L7.72766 6.88818C7.90266 6.14443 8.11266 5.49693 8.37516 4.96318C9.39894 2.84568 11.1402 2.27693 14.0627 2.96818L15.5239 3.30943C19.1902 4.16693 20.3102 5.97818 19.4527 9.63567Z" stroke="#EC980C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M13.6781 17.4667C13.1356 17.8342 12.4531 18.1404 11.6218 18.4117L10.2393 18.8667C6.76555 19.9867 4.93679 19.0504 3.80804 15.5767L2.68804 12.1204C1.56804 8.64664 2.49554 6.80914 5.96929 5.68914L7.35179 5.23414C7.71054 5.12039 8.05179 5.02414 8.37554 4.96289C8.11304 5.49664 7.90304 6.14414 7.72804 6.88789L6.87054 10.5542C6.01304 14.2117 7.1418 16.0142 10.7906 16.8804L12.2606 17.2304C12.7681 17.3529 13.2406 17.4317 13.6781 17.4667Z" stroke="#EC980C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M11.5605 7.96484L15.8043 9.04109" stroke="#EC980C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M10.7031 11.3516L13.2406 11.9991" stroke="#EC980C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

        return [
            'success' => [
                'status'  => $status,
                'title'   => $title,
                'icon'    => $icon,
                'summary' => esc_html__('Mentioning Not Strictly Necessary', 'wp-statistics'),
                'notes'   => __('<p>According to your current setup, WP Statistics is not configured to record any personal data. This means that technically, you do not need to mention WP Statistics in your privacy policy. However, to foster an environment of utmost transparency with your users, we still encourage mentioning the use of WP Statistics. This helps inform users about the analytics tools employed by your site, reinforcing trust through transparency.</p>', 'wp-statistics')
            ],
            'warning' => [
                'status'  => $status,
                'title'   => $title,
                'icon'    => $icon,
                'summary' => esc_html__('Mentioning Required', 'wp-statistics'),
                'notes'   => __('<p>Your configuration indicates that WP Statistics collects personal data. In this scenario, it is crucial to mention WP Statistics in your privacy policy. This should include information on the type of data collected, its purpose, and how it is processed. Being transparent about the use of WP Statistics and its data handling practices is essential to comply with privacy regulations and to maintain trust with your website visitors</p><p>For more information on adjusting your settings to enhance privacy and for specifics on what to include in your  <b>privacy policy</b>, please see the Privacy Audit section of this page</p>', 'wp-statistics')
            ],
        ];
    }

    public static function getStatus()
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
}