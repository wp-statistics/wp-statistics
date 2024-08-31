<?php if (!defined('ABSPATH')) exit;

if (empty($summary)) {
    exit;
}

// Remove 'www.' from referrers
$topReferrer           = trim(str_replace('www.', '', $summary['topReferrer']));
$thisPeriodTopReferrer = trim(str_replace('www.', '', $summary['thisPeriodTopReferrer']));

// Keep the host only
$topReferrerLabel           = !empty($topReferrer) ? wp_parse_url($topReferrer) : '';
$topReferrerLabel           = !empty($topReferrerLabel) && !empty($topReferrerLabel['host']) ? trim($topReferrerLabel['host']) : '';
$thisPeriodTopReferrerLabel = !empty($thisPeriodTopReferrer) ? wp_parse_url($thisPeriodTopReferrer) : '';
$thisPeriodTopReferrerLabel = !empty($thisPeriodTopReferrerLabel) && !empty($thisPeriodTopReferrerLabel['host']) ? trim($thisPeriodTopReferrerLabel['host']) : '';
?>

<p class="wps-fade-effect"><?php

    // Display the first part of text only if the post has been published more than a week ago
    if (strtotime('now') - strtotime($summary['publishDateString']) >= WEEK_IN_SECONDS) {
        echo sprintf(
            // translators: 1: Start date - 2: To date - 3: Views count - 4: Visitors count.
            __('Over the past week (<b>%s - %s</b>), this post has been <b>viewed %s times by %s visitors</b>', 'wp-statistics'),
            esc_html($summary['fromString']),
            esc_html($summary['toString']),
            number_format(intval($summary['thisPeriodViews'])),
            number_format(intval($summary['thisPeriodVisitors']))
        );

        // If post had any referrers in this period
        if (intval($summary['thisPeriodTopReferrerCount']) > 0 && !empty($topReferrerLabel)) {
            echo sprintf(
                // translators: 1: Referrer link - 2: Referrer name - 3: Referrer count.
                __(', with \'<a href="%s" target="_blank" rel="noreferrer nofollow">%s</a>\' leading with <b>%s referrals</b>', 'wp-statistics'),
                esc_url($thisPeriodTopReferrer),
                esc_html($thisPeriodTopReferrerLabel),
                number_format(intval($summary['thisPeriodTopReferrerCount']))
            );
        }

        echo '.<br />';
    }

    echo sprintf(
        // translators: 1: Views count - 2: Visitors count.
        __('In total, this post has been <b>viewed %s times by %s visitors</b>', 'wp-statistics'),
        number_format(intval($summary['totalViews'])),
        number_format(intval($summary['totalVisitors']))
    );

    // If post had any referrers in total
    if (intval($summary['topReferrerCount']) > 0 && !empty($topReferrerLabel)) {
        echo sprintf(
            // translators: 1: Referrer link - 2: Referrer name - 3: Referrer count.
            __(', with \'<a href="%s" target="_blank" rel="noreferrer nofollow">%s</a>\' leading with <b>%s referrals</b>', 'wp-statistics'),
            esc_url($topReferrer),
            esc_html($topReferrerLabel),
            number_format(intval($summary['topReferrerCount']))
        );
    }

    echo sprintf(
        // translators: %s: Content analytics link.
        __('. For more detailed insights, visit the <b><a href="%s" target="_blank">analytics section</a></b>.', 'wp-statistics'),
        esc_url($summary['contentAnalyticsUrl'])
    );

    ?>
</p>