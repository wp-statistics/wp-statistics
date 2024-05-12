<?php 
namespace WP_Statistics\Service\PrivacyAudit\Faqs;

class RequireCookieBanner extends AbstractFaq
{
    static public function getStatus()
    {
        return 'success';
    }
    
    static public function getStates()
    {
        return [
            'success' => [
                'status'    => 'success',
                'title'     => esc_html__('Does WP Statistics require a cookie banner?', 'wp-statistics'),
                'summary'   => __('<b>No</b>, WP Statistics does not require a cookie banner.', 'wp-statistics'),
                'notes'     => __('<p>Unlike many analytics solutions that rely on cookies to track users across a website, WP Statistics employs a method of counting unique visitors that does not involve the use of cookies. This approach ensures privacy compliance and minimizes the need for user consent related to cookie usage.</p><p><b>Why a Cookie Banner is Not Required</b></p><p>WP Statistics distinguishes itself by utilizing a cookieless tracking mechanism. This means the plugin can provide accurate analytics insights without storing any data on visitors’ devices, thereby respecting user privacy and reducing regulatory burdens for website owners.</p><p><b>More Information</b></p><p>For a comprehensive understanding of how WP Statistics counts unique visitors without cookies, and the advantages of this approach, please refer to our detailed documentation: <a target="_blank" href="  https://wp-statistics.com/resources/counting-unique-visitors-without-cookies/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy" title="Counting Unique Visitors Without Cookies"> Counting Unique Visitors Without Cookies</a>.</p>', 'wp-statistics')
            ]
        ];
    }
    
}