<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit\Faqs;

class TransferData extends AbstractFaq
{
    public static function getStatus()
    {
        return 'success';
    }

    public static function getStates()
    {
        return [
            'success' => [
                'status'  => 'success',
                'icon'    => '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 16.2493C18 18.1823 14.866 19.7493 11 19.7493C7.134 19.7493 4 18.1823 4 16.2493V12.7266C4.43943 13.2661 5.06308 13.7143 5.75677 14.0611C7.13253 14.749 8.98721 15.1556 11 15.1556C13.0128 15.1556 14.8675 14.749 16.2433 14.0611C16.937 13.7143 17.5606 13.2661 18 12.7266V16.2493Z" fill="#019939"/><path d="M11 9.90559C13.0128 9.90559 14.8675 9.49906 16.2433 8.81118C16.937 8.46433 17.5606 8.0161 18 7.47656V10.9993C18 11.4368 16.4375 12.3916 15.6562 12.8872C14.4985 13.4661 12.8533 13.8431 11 13.8431C9.14679 13.8431 7.50147 13.4661 6.34374 12.8872C5.3125 12.3716 4 11.4368 4 10.9993V7.47656C4.43943 8.0161 5.06308 8.46433 5.75677 8.81118C7.13253 9.49906 8.98721 9.90559 11 9.90559Z" fill="#019939"/><path d="M15.656 7.63791C14.4983 8.21677 12.853 8.59375 10.9998 8.59375C9.14657 8.59375 7.50125 8.21677 6.34352 7.63791C5.89314 7.46146 4.88035 6.90055 4.17609 5.99443C4.05625 5.84024 4.00482 5.64371 4.03467 5.45073C4.05475 5.32098 4.08273 5.18733 4.11859 5.10448C4.72441 3.47985 7.57485 2.25 10.9998 2.25C14.4247 2.25 17.2751 3.47985 17.881 5.10448C17.9168 5.18733 17.9448 5.32098 17.9649 5.45073C17.9947 5.64371 17.9433 5.84024 17.8235 5.99443C17.1192 6.90055 16.1065 7.46146 15.656 7.63791Z" fill="#019939"/></svg>',
                'title'   => esc_html__('Does WP Statistics transfer data outside the EU?', 'wp-statistics'),
                'summary' => __('<b>No</b>, WP Statistics is designed to store all analytics data directly within your WordPress database.', 'wp-statistics'),
                'notes'   => __('<p>This means that the data resides on the same server as your website, following the same data storage practices. Since WP Statistics does not transfer data outside of your database, the location of your data is determined by your web hosting service. If your hosting servers are located within the EU, your data does not leave the EU. It is essential to be aware of your hosting provider’s data center locations to understand where your website’s data, including that collected by WP Statistics, is physically stored.</p>', 'wp-statistics')
            ]
        ];
    }

}