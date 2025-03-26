<?php

use WP_Statistics\Service\Admin\NoticeHandler\Notice;

if (!WP_STATISTICS\Option::get('share_anonymous_data') && !in_array('share_anonymous_data', get_option('wp_statistics_dismissed_notices', []))) {
    $notice = [
        'title'   => __('Help Us Improve WP Statistics!', 'wp-statistics'),
        'content' => __('We’ve added a new Usage Tracking option to help us understand how WP Statistics is used and identify areas for improvement. By enabling this feature, you’ll help us make the plugin better for everyone. No personal or sensitive data is collected.', 'wp-statistics'),
        'links'   => [
            'learn_more'      => [
                'text' => __('Learn More', 'wp-statistics'),
                'url'  => 'https://wp-statistics.com/resources/sharing-your-data-with-us/?utm_source=wp-statistics&utm_medium=link&utm_campaign=doc',
            ],
            'primary_button' => [
                'text'       => __('Enable Share Anonymous Data', 'wp-statistics'),
                'url'        => '#',
                'attributes' => [
                    'data-option' => 'share_anonymous_data',
                    'data-value'  => 'true',
                ],
                'class'      => 'wps-option__updater notice--enable-usage',
            ]
        ]
    ];
    Notice::renderNotice($notice, 'share_anonymous_data', 'setting', true, 'action');
}
?>
