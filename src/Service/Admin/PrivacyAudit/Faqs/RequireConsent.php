<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit\Faqs;

use WP_Statistics\Service\Admin\PrivacyAudit\Audits\AnonymizeIpAddress;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\HashIpAddress;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\RecordUserPageVisits;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\StoreUserAgentString;

class RequireConsent extends AbstractFaq
{
    public static function getStates()
    {
        $status = self::getStatus();
        $title  = esc_html__('Does WP Statistics require consent?', 'wp-statistics');
        $icon   = '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12.9939 2.38321C12.6699 2.25 12.2591 2.25 11.4375 2.25C10.6159 2.25 10.2051 2.25 9.88105 2.38321C9.44897 2.56083 9.1057 2.90151 8.92674 3.3303C8.84504 3.52605 8.81307 3.75369 8.80055 4.08574C8.78217 4.57372 8.53001 5.0254 8.1039 5.26956C7.67778 5.51372 7.15756 5.5046 6.72255 5.27641C6.42653 5.12114 6.2119 5.03479 6.00024 5.00714C5.53658 4.94656 5.06766 5.07125 4.69664 5.3538C4.41837 5.56571 4.21296 5.91879 3.80216 6.62494C3.39136 7.3311 3.18596 7.68417 3.14018 8.0293C3.07913 8.48945 3.20478 8.95483 3.48948 9.32306C3.61943 9.49115 3.80205 9.63237 4.08549 9.80912C4.50217 10.069 4.77028 10.5117 4.77025 11C4.77023 11.4883 4.50213 11.9309 4.08549 12.1907C3.802 12.3675 3.61935 12.5089 3.48939 12.6769C3.20469 13.0451 3.07905 13.5105 3.14009 13.9706C3.18587 14.3157 3.39128 14.6689 3.80207 15.375C4.21288 16.0811 4.41828 16.4343 4.69655 16.6461C5.06757 16.9286 5.53649 17.0533 6.00015 16.9928C6.21181 16.9651 6.42642 16.8788 6.72241 16.7236C7.15745 16.4954 7.67771 16.4862 8.10385 16.7304C8.52999 16.9746 8.78216 17.4263 8.80055 17.9143C8.81307 18.2463 8.84504 18.474 8.92674 18.6697C9.1057 19.0985 9.44897 19.4392 9.88105 19.6168C10.2051 19.75 10.6159 19.75 11.4375 19.75C12.2591 19.75 12.6699 19.75 12.9939 19.6168C13.426 19.4392 13.7693 19.0985 13.9482 18.6697C14.0299 18.474 14.062 18.2463 14.0745 17.9143C14.0929 17.4263 14.3449 16.9746 14.7711 16.7304C15.1972 16.4862 15.7175 16.4954 16.1525 16.7236C16.4485 16.8788 16.6631 16.9651 16.8747 16.9927C17.3384 17.0533 17.8073 16.9286 18.1783 16.6461C18.4567 16.4342 18.662 16.0811 19.0728 15.3749C19.4836 14.6688 19.689 14.3157 19.7349 13.9706C19.7958 13.5105 19.6702 13.045 19.3856 12.6769C19.2555 12.5088 19.0729 12.3674 18.7894 12.1907C18.3728 11.9309 18.1047 11.4882 18.1047 10.9999C18.1047 10.5116 18.3728 10.0691 18.7894 9.8093C19.073 9.63246 19.2556 9.49124 19.3856 9.32306C19.6703 8.95489 19.7959 8.48951 19.7349 8.02935C19.6891 7.68423 19.4837 7.33115 19.0729 6.625C18.6621 5.91885 18.4567 5.56577 18.1784 5.35386C17.8074 5.07131 17.3385 4.94662 16.8748 5.0072C16.6632 5.03485 16.4485 5.12119 16.1526 5.27645C15.7176 5.50464 15.1973 5.51377 14.7712 5.26959C14.345 5.02542 14.0929 4.5737 14.0744 4.0857C14.0619 3.75367 14.0299 3.52604 13.9482 3.3303C13.7693 2.90151 13.426 2.56083 12.9939 2.38321ZM11.4375 13.625C12.8983 13.625 14.0824 12.4498 14.0824 11C14.0824 9.55021 12.8983 8.375 11.4375 8.375C9.97669 8.375 8.79251 9.55021 8.79251 11C8.79251 12.4498 9.97669 13.625 11.4375 13.625Z" fill="#EC980C"/></svg>';

        return [
            'success' => [
                'status'  => $status,
                'icon'    => $icon,
                'title'   => $title,
                'summary' => esc_html__('User Consent Not Required.', 'wp-statistics'),
                'notes'   => __('<p>Based on your current configuration, WP Statistics is not recording any personal data. Consequently, under these settings, your use of WP Statistics does not require obtaining user consent. This approach aligns with privacy-focused analytics, minimizing compliance burdens while respecting user privacy.</p>', 'wp-statistics')
            ],
            'warning' => [
                'icon'    => $icon,
                'status'  => $status,
                'title'   => $title,
                'summary' => esc_html__('User Consent Required.', 'wp-statistics'),
                'notes'   => __('<p>Your current settings indicate that WP Statistics is configured to collect personal data. In this case, it is essential to obtain user consent to comply with privacy laws and regulations. For detailed information on which settings may necessitate user consent and how to adjust them, please refer to the <b>Privacy Audit</b> section of this page.</p><p>To have consent for WP Statistics on your site, read <a target="_blank" href="https://wp-statistics.com/resources/integrating-wp-statistics-with-consent-management-plugins/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy">Integrating WP Statistics with Consent Management Plugins</a>.</p>', 'wp-statistics')
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