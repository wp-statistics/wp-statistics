<?php

use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\PrivacyAudit\PrivacyAuditDataProvider;
use WP_Statistics\Service\Admin\WebsitePerformance\WebsitePerformanceDataProvider;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

$is_rtl             = is_rtl();
$text_align         = $is_rtl ? 'right' : 'left';
$text_align_reverse = $is_rtl ? 'left' : 'right';
$dir                = $is_rtl ? 'rtl' : 'ltr';

// Setting up the logo.
$final_logo = ' <a href="' . esc_url($logo_url) . '"  class="wp-statistics-logo" style=" font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif; margin: 0; padding: 0; text-decoration: none;"><img src="' . esc_url($logo_image) . '" width="168" height="38" title="WP Statistics" alt="WP Statistics" style="margin: 0; margin-bottom: 32px; padding: 0; text-decoration: none;"></a>';

// Advertisement For WP Statistics Advanced Report Plugin
$advanced_reporting_ad = is_plugin_active('wp-statistics-advanced-reporting/wp-statistics-advanced-reporting.php') ? '' :
    '<table class="better-reports" style="background-color: #404bf2; border: 1px solid #404bf2;  ' . ($email_footer ? 'border-radius: 12px 12px 0 0;' : 'border-radius: 12px;') . '  font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif;; margin: 0; margin-top: 39px;  text-align: center; text-decoration: none;">
        <tbody><tr><td style="padding: 32px 18px;">
        <h2 class="better-reports__title" style=" color: #fff; font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif;; font-size: 18px; font-weight: 600; line-height: 21.09px; margin: 0 0 24px; padding: 0; text-decoration: none;">' . __('Get Better Reports', 'wp-statistics') . '</h2>
        <p style=" color: #fff; font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif;; font-size: 15px; font-weight: 400; line-height: 25px; margin: 0 0 32px; padding: 0; text-decoration: none;">
           ' . __('Detailed and customizable email reports are available with the Advanced Reporting add-on. Make sure you always have the insights you need to make informed decisions by digging deeper into your website analytics.', 'wp-statistics') . '
        </p>
        <a href="https://wp-statistics.com/product/wp-statistics-advanced-reporting/?utm_source=wp_statistics&utm_medium=display&utm_campaign=email_report"  title="' . __('See the Full Picture — Try Advanced Reporting Today', 'wp-statistics') . '" style="background-color: #fff; background-image: url(\'' . esc_url(WP_STATISTICS_URL . 'assets/mail/images/arrow-right.png') . '\'); background-position: center right 24px; background-repeat: no-repeat; background-size: 16px; border-radius: 4px;  color: #404bf2; display: inline-block; font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif;; font-size: 15px; font-weight: 600; line-height: 17.58px; margin: 0; padding: 12px 50px 12px 16px;  text-decoration: none; word-break: break-word;">
            ' . __('See the Full Picture — Try Advanced Reporting Today', 'wp-statistics') . '
        </a>
   </td></tr></tbody></table>';

$privacyBox      = '';
$privacyAuditData = new PrivacyAuditDataProvider();
$complianceStatus = $privacyAuditData->getComplianceStatus();
if (intval($complianceStatus['percentage_ready']) !== 100 && !empty($complianceStatus['summary']) && intval($complianceStatus['summary']['action_required'])) {
    $privacyBox = '<table style="background-color: #B266200D;border-radius: 12px;margin-bottom: 24px;">
                <tbody>
                   <tr>
                        <td style="padding: 20px">
                            <table>
                                <tbody>
                                    <tr>
                                        <td style="vertical-align: top;padding-'.$text_align_reverse.': 16px"> <img src="' . esc_url(WP_STATISTICS_URL . '/assets/images/mail/alert-line.png') . '" width="24" height="24" style="vertical-align: middle"></td>
                                        <td>
                                            <p style="color:#B26620;padding:0;margin:0;margin-bottom: 6px;font-size: 17px;font-weight: 500;line-height: 19.92px">' . __('Attention required!', 'wp-statistics') . '</p>
                                            <p style="color:#B26620;padding:0;margin:0;margin-bottom: 12px;font-size: 14px;font-weight: 400;line-height: 16.41px">' .
                                        sprintf(
                                        // translators: %s: Count of non-compliance items.
                                            __('There are %d items that need to be addressed to ensure compliance with privacy laws.', 'wp-statistics'),
                                            intval($complianceStatus['summary']['action_required'])
                                        ) . '</p>
                                            <a href="' . esc_url(Menus::admin_url('privacy-audit')) . '" style="border-bottom: 1px solid #B26620;text-decoration: none;color:#B26620;font-size: 14px;font-weight: 500;line-height: 16.41px">' . __('Review Audit Details', 'wp-statistics') . '<img src="' . esc_url(WP_STATISTICS_URL . '/assets/images/mail/arrow-brown-'.$text_align_reverse.'.png') . '" width="6.67" height="10.91" style="vertical-align: middle;margin-'.$text_align.':6px" alt=""></a>
                                        </td>
                                    </tr>   
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>';
}

$tipOfEmail = Helper::getReportEmailTip();

// "Your performance at a glance" section variables
$startDate = date('Y-m-d', strtotime('-1 month'));
$endDate   = '';
if (!empty($schedule)) {
    $startDate = $schedule['start'];
    $endDate   = $schedule['end'];
}
$websitePerformanceDataProvider = new WebsitePerformanceDataProvider($startDate, $endDate);

$email_performance_html = '
    <div class="card performance_glance" style="background-color: #fff;border-radius: 12px;margin-bottom: 39px"> 
        <div class="card__header" style="background-color: #E1EBFE;border-radius: 12px 12px 0 0">
            <table style="border-collapse: separate; mso-table-lspace: 0; mso-table-rspace: 0; width: 100%;" cellpadding="15" bgcolor="#F7F7F7" align="center">
                <tbody>
                <tr>
                    <td style="color: #1E1E20;font-size: 17px;margin: 0;padding: 16px 32px;text-align: ' . $text_align . ';border-radius: 12px 12px 0 0;font-weight: 500;background-color: #E1EBFE;line-height: 19.92px;font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif;;">' . __('Your performance at a glance', 'wp-statistics') . '</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="card__body" style=" border-top: 0; padding: 24px 32px 32px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td width="40%" valign="top">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 20px;">
                            <tr>
                                <td width="24" style="vertical-align: top">
                                    <img src="' . esc_url(WP_STATISTICS_URL . 'assets/images/mail/visitor.png') .'" width="24" height="24">
                                </td>
                                <td style="padding-'.$text_align.': 6px;">
                                    <div style="margin-bottom: 6px;font-size: 19px;font-weight: 500;line-height: 23.44px;text-align: '.$text_align.';color:#3D3D44"><span style="float: '.$text_align.';margin-'.$text_align_reverse.': 10px;margin-top: -3px;" >' . Helper::formatNumberWithUnit($websitePerformanceDataProvider->getCurrentPeriodVisitors(), 1) . '</span>
                                        <span style="padding: 2px 4px;gap: 2px;border-radius: 4px;background-color: ' . ($websitePerformanceDataProvider->getPercentageChangeVisitors() >= 0 ? '#1961401A' : '#FCECEB') . ';font-size: 12px; font-weight: 600; line-height: 14.06px;color:' . ($websitePerformanceDataProvider->getPercentageChangeVisitors() >= 0 ? '#196140' : '#D54037') . ';display: inline-block">
                                            <img width="7" height="7" style="margin-'.$text_align_reverse.': 2px;" src="' . esc_url(WP_STATISTICS_URL . 'assets/images/mail/' . ($websitePerformanceDataProvider->getPercentageChangeVisitors() >= 0 ? 'up' : 'down') . '.png') .'"  >' . $websitePerformanceDataProvider->getPercentageChangeVisitors() . '%
                                        </span>
                                    </div>
                                     <span style="font-size: 14px;color:#3D3D44;line-height:16.41px">' . __('Visitors', 'wp-statistics') . '</span> 
                                </td>
                            </tr>
                        </table>
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 20px;">
                            <tr>
                                <td width="24" style="vertical-align: top">
                                    <img src="' . esc_url(WP_STATISTICS_URL . 'assets/images/mail/referrals.png') .'" width="24" height="24" >
                                </td>
                                <td style="padding-'.$text_align.': 6px;">
                                    <div style="margin-bottom: 6px;font-size: 19px;font-weight: 500;line-height: 23.44px;text-align: '.$text_align.';color:#3D3D44"><span  style="float: '.$text_align.';margin-'.$text_align_reverse.': 10px;margin-top: -3px;">' . Helper::formatNumberWithUnit($websitePerformanceDataProvider->getCurrentPeriodReferralsCount(), 1) . '</span>
                                        <span style="padding: 2px 4px;gap: 2px;border-radius: 4px;background-color: ' . ($websitePerformanceDataProvider->getPercentageChangeReferrals() >= 0 ? '#1961401A' : '#FCECEB') . ';font-size: 12px; font-weight: 600; line-height: 14.06px;color:' . ($websitePerformanceDataProvider->getPercentageChangeReferrals() >= 0 ? '#196140' : '#D54037') . ';display: inline-block">
                                            <img width="7" height="7" style="margin-'.$text_align_reverse.': 2px" src="' . esc_url(WP_STATISTICS_URL . 'assets/images/mail/' . ($websitePerformanceDataProvider->getPercentageChangeReferrals() >= 0 ? 'up' : 'down') . '.png') .'"  >' . $websitePerformanceDataProvider->getPercentageChangeReferrals() . '%
                                        </span>
                                    </div>
                                     <span  style="font-size: 14px;color:#3D3D44;line-height:16.41px">' . __('Referrals', 'wp-statistics') . '</span> 
                                </td>
                            </tr>
                        </table> 
                    </td>
                    <td width="60%" valign="top">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 20px;">
                            <tr>
                                <td width="24" style="vertical-align: top">
                                    <img src="' . esc_url(WP_STATISTICS_URL . 'assets/images/mail/views.png') .'" width="24" height="24" >
                                </td>
                                <td style="padding-'.$text_align.': 6px;">
                                    <div style="margin-bottom: 6px;font-size: 19px;font-weight: 500;line-height: 23.44px;text-align: '.$text_align.';color:#3D3D44"><span  style="float: '.$text_align.';margin-'.$text_align_reverse.': 10px;margin-top: -3px;" >' . Helper::formatNumberWithUnit($websitePerformanceDataProvider->getCurrentPeriodViews(), 1) . '</span> 
                                        <span style="padding: 2px 4px;gap: 2px;border-radius: 4px;background-color: ' . ($websitePerformanceDataProvider->getPercentageChangeViews() >= 0 ? '#1961401A' : '#FCECEB') . ';font-size: 12px; font-weight: 600; line-height: 14.06px;color:' . ($websitePerformanceDataProvider->getPercentageChangeViews() >= 0 ? '#196140' : '#D54037') . ';display: inline-block">
                                            <img width="7" height="7" style="width: 7px;height: 7px;margin-'.$text_align_reverse.': 4px" src="' . esc_url(WP_STATISTICS_URL . 'assets/images/mail/' . ($websitePerformanceDataProvider->getPercentageChangeViews() >= 0 ? 'up' : 'down') . '.png') .'"  >' . $websitePerformanceDataProvider->getPercentageChangeViews() . '%
                                        </span>
                                    </div>
                                    <span  style="font-size: 14px;color:#3D3D44;line-height:16.41px">' . __('Views', 'wp-statistics') . '</span> 
                                </td>
                            </tr>
                        </table>
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 20px;">
                            <tr>
                                <td width="24" style="vertical-align: top">
                                    <img src="' . esc_url(WP_STATISTICS_URL . 'assets/images/mail/contents.png') .'"  width="24" height="24">
                                </td>
                                <td style="padding-'.$text_align.': 6px;">
                                    <div style="margin-bottom: 6px;font-size: 19px;font-weight: 500;line-height: 23.44px;text-align: '.$text_align.';color:#3D3D44"><span  style="float: '.$text_align.';margin-'.$text_align_reverse.': 10px;margin-top: -3px;" >' . $websitePerformanceDataProvider->getCurrentPeriodContents() . '</span> 
                                        <span style="padding: 2px 4px;gap: 2px;border-radius: 4px;background-color: ' . ($websitePerformanceDataProvider->getPercentageChangeContents() >= 0 ? '#1961401A' : '#FCECEB') . ';color:' . ($websitePerformanceDataProvider->getPercentageChangeContents() >= 0 ? '#196140' : '#D54037') . ';font-size: 12px; font-weight: 600; line-height: 14.06px;display: inline-block">
                                            <img  width="7" height="7" style="margin-'.$text_align_reverse.': 2px" src="' . esc_url(WP_STATISTICS_URL . 'assets/images/mail/' . ($websitePerformanceDataProvider->getPercentageChangeContents() >= 0 ? 'up' : 'down') . '.png') .'"  >' . $websitePerformanceDataProvider->getPercentageChangeContents() . '%
                                        </span>
                                    </div>
                                     <span  style="font-size: 14px;color:#3D3D44;line-height:16.41px">' . __('Published Contents', 'wp-statistics') . '</span> 
                                </td>
                            </tr>
                        </table> 
                    </td>
                </tr>
            </table>';

$email_performance_html .= '<table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>';

// Initialize the first and second column content arrays
$firstColumnContent  = [];
$secondColumnContent = [];

// Add top author to the first column
if (!empty($websitePerformanceDataProvider->getTopAuthor())) {
    $firstColumnContent['top_author'] = '<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 20px;">
            <tr>
                <td width="24" style="vertical-align: top">
                    <img src="' . esc_url(WP_STATISTICS_URL . 'assets/images/mail/top-author.png') .'"  width="24" height="24">
                </td>
                <td style="padding-'.$text_align.': 6px;">
                    <div style="margin-bottom: 6px;font-size: 17px;font-weight: 500;line-height: 21.09px;text-align:  ' . $text_align . ';color:#3D3D44">' . esc_html($websitePerformanceDataProvider->getTopAuthor()) . '</div>
                    <span style="font-size: 14px;color:#3D3D44;line-height:16.41px">' . __('Top Author', 'wp-statistics') . '</span>
                </td>
            </tr>
        </table>';
}

if (!empty($websitePerformanceDataProvider->getTopCategory())) {
    $topCategoryHtml = '<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 20px;">
            <tr>
                <td width="24" style="vertical-align: top">
                    <img src="' . esc_url(WP_STATISTICS_URL . 'assets/images/mail/top-category.png') .'"  width="24" height="24">
                </td>
                <td style="padding-'.$text_align.': 6px;">
                    <div style="margin-bottom: 6px;font-size: 17px;font-weight: 500;line-height: 21.09px;text-align:  ' . $text_align . ';color:#3D3D44">' . esc_html($websitePerformanceDataProvider->getTopCategory()) . '</div>
                    <span style="font-size: 14px;color:#3D3D44;line-height:16.41px">' . __('Top Category', 'wp-statistics') . '</span>
                </td>
            </tr>
        </table>';

    // Add top category to the first column if it's empty
    if (empty($firstColumnContent)) {
        $firstColumnContent['top_category'] = $topCategoryHtml;
    } else {
        // When the first column already has an item, add top category to the second column
        $secondColumnContent['top_category'] = $topCategoryHtml;
    }
}

if (!empty($websitePerformanceDataProvider->getTopPost())) {
    $topPostHtml = '<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 20px;">
            <tr>
                <td width="24" style="vertical-align: top">
                    <img src="' . WP_STATISTICS_URL . 'assets/images/mail/top-content.png"  width="24" height="24">
                </td>
                <td style="padding-'.$text_align.': 6px;">
                    <div style="margin-bottom: 6px;font-size: 17px;font-weight: 500;line-height: 21.09px;text-align:  ' . $text_align . ';color:#3D3D44">' . esc_html($websitePerformanceDataProvider->getTopPost()) . '</div>
                    <span style="font-size: 14px;color:#3D3D44;line-height:16.41px">' . __('Top Content', 'wp-statistics') . '</span>
                </td>
            </tr>
        </table>';

    // Add top post to the first column if it's empty
    if (empty($firstColumnContent)) {
        $firstColumnContent['top_post'] = $topPostHtml;
    } else if (empty($secondColumnContent)) {
        // If second column is empty, add top post to the second column
        $secondColumnContent['top_post'] = $topPostHtml;
    } else {
        // When both columns already have an item, add top post to second row of the first column
        $firstColumnContent['top_post'] = $topPostHtml;
    }
}

if (!empty($websitePerformanceDataProvider->getTopReferral())) {
    $topReferralHtml = '<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 20px;">
            <tr>
                <td width="24" style="vertical-align: top">
                    <img src="' . WP_STATISTICS_URL . 'assets/images/mail/top-referral.png"  width="24" height="24">
                </td>
                <td style="padding-'.$text_align.': 6px;">
                    <div style="margin-bottom: 6px;font-size: 17px;font-weight: 500;line-height: 21.09px;text-align:  ' . $text_align . ';color:#3D3D44">' . esc_html($websitePerformanceDataProvider->getTopReferral()) . '</div>
                    <span style="font-size: 14px;color:#3D3D44;line-height:16.41px">' . __('Top Referral', 'wp-statistics') . '</span>
                </td>
            </tr>
        </table>';

    // Add top referral to the first column if it's empty
    if (empty($firstColumnContent)) {
        $firstColumnContent['top_referral'] = $topReferralHtml;
    } else if (empty($secondColumnContent)) {
        // If second column is empty, add top referral to the second column
        $secondColumnContent['top_referral'] = $topReferralHtml;
    } else if (count($firstColumnContent) == 1) {
        // When both columns already have an item but first column doesn't have a second row, add top referral to second row of the first column
        $firstColumnContent['top_referral'] = $topReferralHtml;
    } else {
        // Otherwise, add top referral to second row of the second column
        $secondColumnContent['top_referral'] = $topReferralHtml;
    }
}

// Create the table rows with the correct columns
$email_performance_html .= '<td width="40%" valign="top">' . implode('', $firstColumnContent) . '</td>';
$email_performance_html .= '<td width="60%" valign="top">' . implode('', $secondColumnContent) . '</td>';

$email_performance_html .= '</tr></table></div></div>';

$email_body = '
        <div class="mail-body" style="direction: ' . $dir . ';background-color: #F7F9FA;  font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif;; margin: 0; padding: 39px 0; text-decoration: none;">
            <div class="main-section" style=" font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif;; margin:0 auto;  padding: 0 5px; text-decoration: none; width: 618px; ">
                <div style="border-radius: 12px;margin-bottom: 24px;background-color: #fff;">
                    <table class="header" style="background-color: #E1EBFE;padding: 32px 34px; font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif;; margin: 0; text-align: ' . $text_align . ';text-decoration: none; width: 100%; ' . (!empty($content) || !empty($email_header) ? 'border-radius: 12px 12px 0 0;' : 'border-radius: 12px;') . ' ">
                        <tr style=" font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif;; margin: 0; padding: 0; text-decoration: none;">
                            <td style=" font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif;; margin: 0; padding: 0; text-decoration: none;">
                                 ' . $final_logo . '
                            </td>
                        </tr>
                        <tr style=" font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif;; margin: 0; padding: 0; text-decoration: none;">
                            <td style=" font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif;; margin: 0; padding: 0; text-decoration: none;">
                                <p style=" color: #0C0C0D ; font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif;; font-size:21px; font-weight: 600; line-height: 24.61px; margin: 0;  padding: 0">' . $email_title . '</p>
                            </td>
                        </tr>
                    </table>
                    ' . $email_header;
if (!empty($content)) {

    $email_body .= '<table>
                        <tbody>
                            <tr>
                               <td style="padding: 32px;white-space: pre-wrap">' .wp_kses_post($content) . '</td>                   
                            </tr>
                        </tbody>   
                    </table>';
}
$email_body .= '</div>
                ' .$email_performance_html . $privacyBox .'
                <div class="content" style="' . ($email_footer ? 'border-radius: 0;' : 'border-radius: 0 0 18px 18px;') . '  font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif; margin: 0;">
                     <table class="content__tip" style="background-color: #E1EBFE; border-radius: 12px;  font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif; margin: 0;  text-decoration: none;">
                        <tbody>
                            <tr>
                                <td style="padding: 20px;">
                                    <div class="content__tip--title" style=" font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif; margin: 0; margin-bottom: 16px; padding: 0; text-decoration: none;">
                                    <h2 style=" font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif; font-size: 17px; font-weight: 500; line-height: 19.92px; margin: 0; text-decoration: none;color: #303032">
                                       <img src="' . esc_url(WP_STATISTICS_URL . '/assets/images/mail/tip.png') . '" width="16" height="16" style="float:' . $text_align . ';margin-top: 2px;margin-' . $text_align_reverse . ':6px">' . $tipOfEmail['title'] . '
                                    </h2>
                                    </div>
                                    <div class="content__tip--description" style=" color: #303032; font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif; font-size: 16px; font-weight: 400; line-height: 22px; margin: 0; padding: 0; text-decoration: none;">
                                    ' . $tipOfEmail['content'] . '
                                    </div>
                                                            
                                </td>                       
                            </tr>                 
                        </tbody> 
                     </table>
                 </div>
                 ' . $advanced_reporting_ad . $email_footer . $copyright . '</div></div>';


echo $email_body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped