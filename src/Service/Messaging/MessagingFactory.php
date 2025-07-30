<?php

namespace WP_Statistics\Service\Messaging;

use WP_Statistics\Components\Addons;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Messaging\Provider\MailProvider;
use WP_Statistics\Service\Messaging\Provider\SmsProvider;

/**
 * Static helper that routes outgoing messages to the appropriate provider.
 * All helpers return the boolean result from the provider’s `send()` call.
 *
 * @since 15.0.0
 */
class MessagingFactory
{
    /**
     * Pick a random tip to display in report e‑mails.
     *
     * @return array{title:string,content:string}  Associative array ready for templating.
     */
    public static function getReportEmailTip()
    {
        $is_rtl             = is_rtl();
        $text_align         = $is_rtl ? 'right' : 'left';
        $text_align_reverse = $is_rtl ? 'left' : 'right';
        $tips               = [
            [
                'title'   => __('Optimize Your Content Strategy', 'wp-statistics'),
                'content' => sprintf(
                    __('For maximum accuracy, enable the cache compatibility mode on your website and check your filtering settings. By following these steps, traffic data becomes more accurate. <div style="margin-top: 16px">For more details, read <a href="%1$s" style="color:#5100FD;font-size:14px;line-height:16.41px;font-weight:500;border-bottom: 1px solid #5100FD;text-decoration: none" target="_blank">%2$s <img width="6.67" height="10.91" style="margin-' . $text_align . ':6px" src="%3$s" alt=""></a></div>', 'wp-statistics'),
                    'https://wp-statistics.com/resources/enhancing-data-accuracy/?utm_source=wp-statistics&utm_medium=email&utm_campaign=tips',
                    'Enhancing Data Accuracy',
                    esc_url(WP_STATISTICS_URL . '/assets/images/mail/arrow-blue-' . $text_align_reverse . '.png')
                ),
            ],
            [
                'title'   => __('Optimize Your Data Accuracy', 'wp-statistics'),
                'content' => __(sprintf('For maximum accuracy, enable the cache compatibility mode on your website and check your filtering settings. By following these steps, traffic data becomes more accurate. <div style="margin-top: 16px">For more details, read  <a href="https://wp-statistics.com/resources/enhancing-data-accuracy/?utm_source=wp-statistics&utm_medium=email&utm_campaign=tips" style="color:#5100FD;font-size:14px;line-height:16.41px;font-weight:500;border-bottom: 1px solid #5100FD;text-decoration: none" > %1$s. <img src="' . esc_url(WP_STATISTICS_URL . '/assets/images/mail/arrow-blue-' . $text_align_reverse . '.png') . '" width="6.67" height="10.91" style="margin-' . $text_align . ':6px" alt=""></a></div>', 'Enhancing Data Accuracy '), 'wp-statistics'),
            ],
            [
                'title'   => __('Keep the plugin up-to-date', 'wp-statistics'),
                'content' => __('Ensure that your WP Statistics plugin is up-to-date in order to get the latest features and security improvements.', 'wp-statistics'),
            ],
            [
                'title'   => __('Maintain Privacy Compliance', 'wp-statistics'),
                'content' => __(sprintf('To ensure that your website complies with the latest privacy standards, use the Privacy Audit feature in WP Statistics. It provides actionable recommendations for improving your privacy compliance by assessing your WP Statistics\' current settings.<div style="margin-top: 16px"> For more information, refer to our <a href="https://wp-statistics.com/resources/privacy-audit/?utm_source=wp-statistics&utm_medium=email&utm_campaign=privacy" style="color:#5100FD;font-size:14px;line-height:16.41px;font-weight:500;border-bottom: 1px solid #5100FD;text-decoration: none">Privacy Audit Guide <img src="' . esc_url(WP_STATISTICS_URL . '/assets/images/mail/arrow-blue-' . $text_align_reverse . '.png') . '" width="6.67" height="10.91" style="margin-' . $text_align . ':6px" alt=""></a></div>'), 'wp-statistics'),
            ],
            [
                'title'   => __('WordPress Export and Erasure', 'wp-statistics'),
                'content' => __(sprintf('If you record PII data with WP Statistics, use WordPress data export and erasure features to manage this information. This ensures compliance with privacy regulations like GDPR.<div style="margin-top: 16px"> For more details, see our <a href="https://wp-statistics.com/resources/compliant-with-wordpress-data-export-and-erasure/?utm_source=wp-statistics&utm_medium=email&utm_campaign=tips" style="color:#5100FD;font-size:14px;line-height:16.41px;font-weight:500;border-bottom: 1px solid #5100FD;text-decoration: none">Data Export and Erasure Guide <img src="' . esc_url(WP_STATISTICS_URL . '/assets/images/mail/arrow-blue-' . $text_align_reverse . '.png') . '" width="6.67" height="10.91" style="margin-' . $text_align . ':6px" alt=""></a></div>'), 'wp-statistics'),
            ],
            [
                'title'   => __('Track Links and Downloads', 'wp-statistics'),
                'content' => __(sprintf('Track how users interact with your site\'s links and downloads using the Link and Download Tracker feature. You can use this information to improve content engagement and understand user behavior. <div style="margin-top: 16px"><a href="https://wp-statistics.com/add-ons/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=email&utm_campaign=dp" style="color:#5100FD;font-size:14px;line-height:16.41px;font-weight:500;border-bottom: 1px solid #5100FD;text-decoration: none">Read more <img src="' . esc_url(WP_STATISTICS_URL . '/assets/images/mail/arrow-blue-' . $text_align_reverse . '.png') . '" width="6.67" height="10.91" style=" margin-' . $text_align . ':6px" alt=""></a></div>'), 'wp-statistics'),
            ],
            [
                'title'   => __('Advanced Filtering', 'wp-statistics'),
                'content' => __(sprintf('Analyze specific query parameters, including UTM tags, for each piece of content. Tracking marketing campaigns and engagement allows you to refine your strategies and maximize their impact. <div style="margin-top: 16px"><a href="https://wp-statistics.com/add-ons/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=email&utm_campaign=dp" style="color:#5100FD;font-size:14px;line-height:16.41px;font-weight:500;border-bottom: 1px solid #5100FD;text-decoration: none">Read more <img src="' . esc_url(WP_STATISTICS_URL . '/assets/images/mail/arrow-blue-' . $text_align_reverse . '.png') . '" width="6.67" height="10.91" style="margin-' . $text_align . ':6px" alt=""></a></div>'), 'wp-statistics'),
            ],
            [
                'title'   => __('Weekly Performance Overview', 'wp-statistics'),
                'content' => __(sprintf('On the Overview page, the Weekly Performance Overview widget provides a quick snapshot of your main metrics. You can analyze traffic changes, identify trends, and make data-driven decisions to improve your site\'s performance with this feature. <div style="margin-top: 16px"><a href="https://wp-statistics.com/add-ons/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=email&utm_campaign=dp" style="color:#5100FD;font-size:14px;line-height:16.41px;font-weight:500;border-bottom: 1px solid #5100FD;text-decoration: none">Read more <img src="' . esc_url(WP_STATISTICS_URL . '/assets/images/mail/arrow-blue-' . $text_align_reverse . '.png') . '" width="6.67" height="10.91" style="margin-' . $text_align . ':6px" alt=""></a></div>'), 'wp-statistics'),
            ],
            [
                'title'   => __('Traffic by Hour Widget', 'wp-statistics'),
                'content' => __(sprintf('On the Overview page, the Traffic by Hour widget displays visitor patterns by hour. Ensure maximum engagement and efficiency by optimizing server resources and scheduling content releases for peak visitor times. <div style="margin-top: 16px"><a href="https://wp-statistics.com/add-ons/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=email&utm_campaign=dp" style="color:#5100FD;font-size:14px;line-height:16.41px;font-weight:500;border-bottom: 1px solid #5100FD;text-decoration: none">Read more <img src="' . esc_url(WP_STATISTICS_URL . '/assets/images/mail/arrow-blue-' . $text_align_reverse . '.png') . '" width="6.67" height="10.91" style="margin-' . $text_align . ':6px" alt=""></a></div>'), 'wp-statistics'),
            ],
            [
                'title'   => __('Content-Specific Analytics', 'wp-statistics'),
                'content' => __(sprintf('Analyze each piece of content in detail, including views, visitor locations, and online visitors. Based on user data, these insights can help you optimize content. <div style="margin-top: 16px"><a href="https://wp-statistics.com/add-ons/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=email&utm_campaign=dp" style="color:#5100FD;font-size:14px;line-height:16.41px;font-weight:500;border-bottom: 1px solid #5100FD;text-decoration: none">Read more <img src="' . esc_url(WP_STATISTICS_URL . '/assets/images/mail/arrow-blue-' . $text_align_reverse . '.png') . '" width="6.67" height="10.91" style="margin-' . $text_align . ':6px" alt=""></a></div>'), 'wp-statistics'),
            ],
            [
                'title'   => __('Custom Post Type Tracking', 'wp-statistics'),
                'content' => __(sprintf('Track all custom post types as well as posts and pages. This ensures complete analytics across all content types on your site. <div style="margin-top: 16px"><a href="https://wp-statistics.com/add-ons/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=email&utm_campaign=dp" style="color:#5100FD;font-size:14px;line-height:16.41px;font-weight:500;border-bottom: 1px solid #5100FD;text-decoration: none">Read more <img src="' . esc_url(WP_STATISTICS_URL . '/assets/images/mail/arrow-blue-' . $text_align_reverse . '.png') . '" width="6.67" height="10.91" style="margin-' . $text_align . ':6px" alt=""></a></div>'), 'wp-statistics'),
            ],
            [
                'title'   => __('Custom Taxonomy Analytics', 'wp-statistics'),
                'content' => __(sprintf('Track custom taxonomies along with default taxonomies like Categories and Tags to gain deeper insights into all taxonomies used on your site. <div style="margin-top: 16px"><a href="https://wp-statistics.com/add-ons/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=email&utm_campaign=dp" style="color:#5100FD;font-size:14px;line-height:16.41px;font-weight:500;border-bottom: 1px solid #5100FD;text-decoration: none">Read more <img src="' . esc_url(WP_STATISTICS_URL . '/assets/images/mail/arrow-blue-' . $text_align_reverse . '.png') . '" width="6.67" height="10.91" style="margin-' . $text_align . ':6px" alt=""></a></div>'), 'wp-statistics'),
            ],
            [
                'title'   => __('Real-Time Stats', 'wp-statistics'),
                'content' => __(sprintf('Monitor your website\'s traffic and activity in real time. Your WordPress statistics are displayed instantly, so you don\'t need to refresh your page every time someone visits your blog. Watch your website\'s performance live. <div style="margin-top: 16px"><a href="https://wp-statistics.com/add-ons/wp-statistics-realtime-stats/?utm_source=wp-statistics&utm_medium=email&utm_campaign=realtime" style="color:#5100FD;font-size:14px;line-height:16.41px;font-weight:500;border-bottom: 1px solid #5100FD;text-decoration: none">Read more <img src="' . esc_url(WP_STATISTICS_URL . '/assets/images/mail/arrow-blue-' . $text_align_reverse . '.png') . '" width="6.67" height="10.91" style="margin-' . $text_align . ':6px" alt=""></a></div>'), 'wp-statistics'),
            ],
            [
                'title'   => __('Mini Chart', 'wp-statistics'),
                'content' => __(sprintf('Track your content\'s performance with mini charts. Quick access to traffic data is provided by an admin bar. The chart type and color can be customized according to your preferences. Analyze your content\'s performance and make informed decisions to enhance its success. <div style="margin-top: 16px"><a href="https://wp-statistics.com/add-ons/wp-statistics-mini-chart/?utm_source=wp-statistics&utm_medium=email&utm_campaign=mini-chart" style="color:#5100FD;font-size:14px;line-height:16.41px;font-weight:500;border-bottom: 1px solid #5100FD;text-decoration: none">Read more <img src="' . esc_url(WP_STATISTICS_URL . '/assets/images/mail/arrow-blue-' . $text_align_reverse . '.png') . '" width="6.67" height="10.91" style="margin-' . $text_align . ':6px" alt=""></a></div>'), 'wp-statistics'),
            ],
        ];

        return $tips[array_rand($tips)];
    }

    /**
     * Send a schedule‑aware report e‑mail.
     *
     * @param string|array $to Recipient address or list.
     * @param string $subject Subject line.
     * @param string $content Message body (HTML allowed).
     * @param bool|string $template `true` for default layout or absolute path.
     * @param array $args Additional template variables.
     *
     * @return bool  True on success, false on failure.
     */
    public static function scheduleMail($to, $subject, $content, $template = true, $args = [])
    {
        $scheduleKey = Option::get('time_report', false);
        $isRtl       = is_rtl();
        $textAlign   = $isRtl ? 'right' : 'left';

        $emailTitle = '<table style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen-Sans,Ubuntu,Cantarell,Helvetica Neue,sans-serif;width:100%;text-align:' .
            esc_attr($textAlign) .
            ';font-size:21px;font-weight:500;line-height:24.61px;color:#0C0C0D;padding:0;"><tbody><tr><td>' .
            __('Your Website Performance Overview', 'wp-statistics') .
            '</td></tr></tbody></table>';

        $scheduleInfo = null;
        if ($scheduleKey && array_key_exists($scheduleKey, \WP_Statistics\Schedule::getSchedules())) {
            $schedules    = \WP_Statistics\Schedule::getSchedules();
            $scheduleInfo = $schedules[$scheduleKey];

            if ($scheduleInfo['interval'] === DAY_IN_SECONDS) {
                $reportDate = esc_html(
                    date_i18n(get_option('date_format', 'j F Y'), strtotime($scheduleInfo['start']))
                );
            } else {
                $reportDate = sprintf(
                /* translators: 1: start date, 2: end date */
                    __('%s - %s', 'wp-statistics'),
                    esc_html(date_i18n(get_option('date_format', 'j F Y'), strtotime($scheduleInfo['start']))),
                    esc_html(date_i18n(get_option('date_format', 'j F Y'), strtotime($scheduleInfo['end'])))
                );
            }

            if (!Addons::isActive('advanced-reporting')) {
                $emailTitle .= sprintf(
                    '<p style="margin-bottom:12px;margin-top:4px;font-size:14px;font-weight:400;line-height:16.41px;color:#56585A;">%1$s</p>' .
                    '<p style="margin:0"><a href="%2$s" title="%3$s" style="color:#56585A;font-size:16px;font-weight:500;line-height:18.75px;text-decoration:none">%3$s</a></p>',
                    esc_html($reportDate),
                    esc_url(get_site_url()),
                    esc_html(get_bloginfo('name'))
                );
            }
        }

        // Optional header / footer free‑content
        $emailHeader = '';
        $emailFooter = '';
        $dir         = $isRtl ? 'rtl' : 'ltr';

        $headerContent = wp_strip_all_tags(Option::get('email_free_content_header', ''));
        if ($headerContent !== '') {
            $emailHeader = '<div style="direction:' . $dir . ';background:#D0DEF5;padding:16px 32px;color:#0C0C0D;font-size:16px;font-weight:500;line-height:18.75px;text-align:' .
                $textAlign .
                ';white-space:pre-wrap;' .
                (!empty($content) ? 'border-radius:0;' : 'border-radius:0 0 12px 12px;') .
                '">' .
                $headerContent .
                '</div>';
        }

        $footerContent = wp_strip_all_tags(\WP_Statistics\Option::get('email_free_content_footer', ''));
        if ($footerContent !== '') {
            $emailFooter = '<div style="direction:' . $dir . ';background:#D0DEF5;padding:16px 32px;color:#0C0C0D;font-size:16px;font-weight:500;line-height:18.75px;text-align:' .
                $textAlign .
                ';white-space:pre-wrap;border-radius:0 0 18px 18px;">' .
                $footerContent .
                '</div>';
        }

        $args = [
            'title'        => $subject,
            'content'      => $content,
            'email_title'  => apply_filters('wp_statistics_email_title', $emailTitle),
            'email_header' => apply_filters('wp_statistics_email_header', $emailHeader),
            'email_footer' => apply_filters('wp_statistics_email_footer', $emailFooter),
            'schedule'     => $scheduleInfo,
        ];

        return self::mail($to, $subject, $content, $template, $args);
    }

    /**
     * Send a plain e‑mail using the default provider.
     *
     * @param string|array $to Recipient address or list.
     * @param string $subject Subject line.
     * @param string $content Message body (HTML allowed).
     * @param bool|string $template `true` for default layout or absolute path.
     * @param array $args Additional template variables.
     *
     * @return bool  True on success, false on failure.
     */
    public static function mail($to, $subject, $content, $template = true, $args = [])
    {
        $service = MessagingService::make(MailProvider::class);

        return $service->provider()
            ->init()
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($content)
            ->setTemplate($template, $args)
            ->send();
    }

    /**
     * Send an SMS message.
     *
     * @param string $to Destination phone number.
     * @param string $text Message text.
     *
     * @return bool  True on success, false on failure.
     */
    public static function sms($to, $text)
    {
        $service = MessagingService::make(SmsProvider::class);

        return $service->provider()
            ->init()
            ->setTo($to)
            ->setText($text)
            ->send();
    }
}