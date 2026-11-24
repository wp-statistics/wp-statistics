<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\PrivacyAudit\PrivacyAuditDataProvider;
use WP_Statistics\Service\Admin\WebsitePerformance\WebsitePerformanceDataProvider;
use WP_STATISTICS\Option;

$is_rtl             = is_rtl();
$text_align         = $is_rtl ? 'right' : 'left';
$text_align_reverse = $is_rtl ? 'left' : 'right';
$dir                = $is_rtl ? 'rtl' : 'ltr';

// Setting up the logo.
$final_logo = ' <a href="' . esc_url($logo_url) . '"  class="wp-statistics-logo" style=" font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif; margin: 0; padding: 0; text-decoration: none;"><img src="' . esc_url($logo_image) . '" width="168" height="38" title="WP Statistics" alt="WP Statistics" style="margin: 0; margin-bottom: 32px; padding: 0; text-decoration: none;"></a>';

// Alerts, for example "visits stopped", pass is_report false: same frame, no report sections.
$is_report             = !isset($is_report) || !empty($is_report);
$advanced_reporting_ad = '';
$privacyBox            = '';
$email_performance_html = '';
$tip_html              = '';

if ($is_report) {

// Advertisement For WP Statistics Advanced Report Plugin
// Not when Advanced Reporting runs, and not under white label (or a hidden Add-ons menu).
$advanced_reporting_ad = (is_plugin_active('wp-statistics-advanced-reporting/wp-statistics-advanced-reporting.php') || \WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper::areAddonPromptsHidden()) ? '' :
    '<table class="better-reports" width="100%" style="width: 100%; background-color: #404bf2; border: 1px solid #404bf2;  ' . ($email_footer ? 'border-radius: 12px 12px 0 0;' : 'border-radius: 12px;') . '  font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif;; margin: 0; margin-top: 39px;  text-align: center; text-decoration: none;">
        <tbody><tr><td style="padding: 32px 18px;">
        <h2 class="better-reports__title" style=" color: #fff; font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif;; font-size: 18px; font-weight: 600; line-height: 21.09px; margin: 0 0 24px; padding: 0; text-decoration: none;">' . __('Get Better Reports', 'wp-statistics') . '</h2>
        <p style=" color: #fff; font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif;; font-size: 15px; font-weight: 400; line-height: 25px; margin: 0 0 32px; padding: 0; text-decoration: none;">
           ' . __('Detailed and customizable email reports are available with the Advanced Reporting add-on. Make sure you always have the insights you need to make informed decisions by digging deeper into your website analytics.', 'wp-statistics') . '
        </p>
        <a href="' . esc_url(\WP_Statistics\Service\Admin\LicenseManagement\UpsellLink::addon('wp-statistics-advanced-reporting', 'email')) . '"  title="' . __('See the Full Picture — Try Advanced Reporting Today', 'wp-statistics') . '" style="background-color: #fff; background-image: url(\'' . esc_url(WP_STATISTICS_URL . 'assets/mail/images/arrow-right.png') . '\'); background-position: center right 24px; background-repeat: no-repeat; background-size: 16px; border-radius: 4px;  color: #404bf2; display: inline-block; font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif;; font-size: 15px; font-weight: 600; line-height: 17.58px; margin: 0; padding: 12px 50px 12px 16px;  text-decoration: none; word-break: break-word;">
            ' . __('See the Full Picture — Try Advanced Reporting Today', 'wp-statistics') . '
        </a>
   </td></tr></tbody></table>';

$privacyBox      = '';
$privacyAuditData = new PrivacyAuditDataProvider();
$complianceStatus = $privacyAuditData->getComplianceStatus();
if (intval($complianceStatus['percentage_ready']) !== 100 && !empty($complianceStatus['summary']) && intval($complianceStatus['summary']['action_required']) && Option::get('privacy_audit') && Option::get('show_privacy_issues_in_report')) {
    $privacyBox = '<table width="100%" style="width: 100%; background-color: #B266200D;border-radius: 12px;margin-bottom: 24px;">
                <tbody>
                   <tr>
                        <td style="padding: 20px">
                            <table>
                                <tbody>
                                    <tr>
                                        <td style="vertical-align: top;padding-'.$text_align_reverse.': 16px"> <img alt="alert icon" src="' . esc_url(WP_STATISTICS_URL . '/assets/images/mail/alert-line.png') . '" width="24" height="24" style="vertical-align: middle"></td>
                                        <td>
                                            <p style="color:#B26620;padding:0;margin:0;margin-bottom: 6px;font-size: 17px;font-weight: 500;line-height: 19.92px">' . __('Attention required!', 'wp-statistics') . '</p>
                                            <p style="color:#B26620;padding:0;margin:0;margin-bottom: 12px;font-size: 14px;font-weight: 400;line-height: 16.41px">' .
                                        sprintf(
                                        // translators: %s: Count of non-compliance items.
                                            __('There are %d items that need to be addressed to ensure compliance with privacy laws.', 'wp-statistics'),
                                            intval($complianceStatus['summary']['action_required'])
                                        ) . '</p>
                                            <a href="' . esc_url(Menus::admin_url('privacy-audit')) . '" style="border-bottom: 1px solid #B26620;text-decoration: none;color:#B26620;font-size: 14px;font-weight: 500;line-height: 16.41px">' . __('Review Audit Details', 'wp-statistics') . '<img src="' . esc_url(WP_STATISTICS_URL . '/assets/images/mail/arrow-brown-'.$text_align_reverse.'.png') . '" width="6.67" height="10.91" style="vertical-align: middle;margin-'.$text_align.':6px" alt="arrow"></a>
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
$startDate = gmdate('Y-m-d', strtotime('-1 month'));
$endDate   = '';
if (!empty($schedule)) {
    $startDate = $schedule['start'];
    $endDate   = $schedule['end'];
}
$websitePerformanceDataProvider = new WebsitePerformanceDataProvider($startDate, $endDate);

// The layout can be included more than once per request (for example a report and an
// alert sent together), so the helpers are declared once, with prefixed names.
if (!function_exists('wp_statistics_email_performance_styles')) {
function wp_statistics_email_performance_styles($percentageChange) {
    $styles = [
        'background' => '#A9AAAE1A', // Default for 0 change
        'color' => '#A9AAAE',
        'image' => 'arrow.png'
    ];
    if ($percentageChange > 0) {
        $styles['background'] = '#1961401A';
        $styles['color'] = '#196140';
        $styles['image'] = 'up.png';
    } elseif ($percentageChange < 0) {
        $styles['background'] = '#FCECEB';
        $styles['color'] = '#D54037';
        $styles['image'] = 'down.png';
    }
    return $styles;
}

}

if (!function_exists('wp_statistics_email_performance_section')) {
function wp_statistics_email_performance_section($icon, $currentValue, $percentageChange, $label, $text_align = null, $text_align_reverse = null) {
    if (is_null($text_align)) {
        $text_align = is_rtl() ? 'right' : 'left';
    }
    if (is_null($text_align_reverse)) {
        $text_align_reverse = is_rtl() ? 'left' : 'right';
    }

    // Build the percentage change badge only if we have valid data
    $percentageBadge = '';
    if ($percentageChange !== null) {
        $styles = wp_statistics_email_performance_styles($percentageChange);
        $percentageBadge = '
                        <span style="padding: 2px 4px;gap: 2px;border-radius: 4px;background-color: ' . $styles['background'] . ';font-size: 12px; font-weight: 600; line-height: 14.06px;color:' . $styles['color'] . ';display: inline-block">
                            <img alt="' . $label . '" width="7" height="7" style="margin-' . $text_align_reverse . ': 2px;" src="' . esc_url(WP_STATISTICS_URL . 'assets/images/mail/' . $styles['image']) . '"  >
                            ' . $percentageChange . '%
                        </span>';
    }

    return '
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 20px;">
            <tr>
                <td width="24" style="vertical-align: top">
                    <img alt="' . $label . '" src="' . esc_url(WP_STATISTICS_URL . 'assets/images/mail/' . $icon) . '" width="24" height="24">
                </td>
                <td style="padding-' . $text_align . ': 6px;">
                    <div style="margin-bottom: 6px;font-size: 19px;font-weight: 500;line-height: 23.44px;text-align: ' . $text_align . ';color:#3D3D44">
                        <span style="float: ' . $text_align . ';margin-' . $text_align_reverse . ': 10px;margin-top: -3px;">' . $currentValue . '</span>' . $percentageBadge . '
                    </div>
                    <span style="font-size: 14px;color:#3D3D44;line-height:16.41px">' . $label . '</span>
                </td>
            </tr>
        </table>';
}
}


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
                    <td width="40%" >
                          ' . wp_statistics_email_performance_section(
                            'visitor.png',
                            Helper::formatNumberWithUnit($websitePerformanceDataProvider->getCurrentPeriodVisitors(), 1),
                            $websitePerformanceDataProvider->getPercentageChangeVisitors(),
                            __('Visitors', 'wp-statistics')
                        ) . '
                        ' . wp_statistics_email_performance_section(
                            'referrals.png',
                            Helper::formatNumberWithUnit($websitePerformanceDataProvider->getCurrentPeriodReferralsCount(), 1),
                            $websitePerformanceDataProvider->getPercentageChangeReferrals(),
                            __('Referrals', 'wp-statistics')
                        ) . '
                    </td>
                    <td width="60%" >
                        ' . wp_statistics_email_performance_section(
                            'views.png',
                            Helper::formatNumberWithUnit($websitePerformanceDataProvider->getCurrentPeriodViews(), 1),
                            $websitePerformanceDataProvider->getPercentageChangeViews(),
                            __('Views', 'wp-statistics')
                        ) . '
                        ' . wp_statistics_email_performance_section(
                            'contents.png',
                            $websitePerformanceDataProvider->getCurrentPeriodContents(),
                            $websitePerformanceDataProvider->getPercentageChangeContents(),
                            __('Published Contents', 'wp-statistics')
                        ) . '
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
                    <img alt="' . __('Top Author', 'wp-statistics') . '" src="' . esc_url(WP_STATISTICS_URL . 'assets/images/mail/top-author.png') .'"  width="24" height="24">
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
                    <img alt="' . __('Top Category', 'wp-statistics') . '" src="' . esc_url(WP_STATISTICS_URL . 'assets/images/mail/top-category.png') .'"  width="24" height="24">
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
                    <img alt="' . __('Top Content', 'wp-statistics') . '" src="' . WP_STATISTICS_URL . 'assets/images/mail/top-content.png"  width="24" height="24">
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
                    <img alt="' . __('Top Referral', 'wp-statistics') . '" src="' . WP_STATISTICS_URL . 'assets/images/mail/top-referral.png"  width="24" height="24">
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
$email_performance_html .= '<td width="40%" >' . implode('', $firstColumnContent) . '</td>';
$email_performance_html .= '<td width="60%" >' . implode('', $secondColumnContent) . '</td>';

$email_performance_html .= '</tr></table></div></div>';

    $tip_html = '<div class="content" style="' . ($email_footer ? 'border-radius: 0;' : 'border-radius: 0 0 18px 18px;') . '  font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif; margin: 0;">
                     <table class="content__tip" width="100%" style="width: 100%; background-color: #E1EBFE; border-radius: 12px;  font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif; margin: 0;  text-decoration: none;">
                        <tbody>
                            <tr>
                                <td style="padding: 20px;">
                                    <div class="content__tip--title" style=" font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif; margin: 0; margin-bottom: 16px; padding: 0; text-decoration: none;">
                                    <h2 style=" font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif; font-size: 17px; font-weight: 500; line-height: 19.92px; margin: 0; text-decoration: none;color: #303032">
                                       <img alt="' . $tipOfEmail['title'] . '" src="' . esc_url(WP_STATISTICS_URL . '/assets/images/mail/tip.png') . '" width="16" height="16" style="float:' . $text_align . ';margin-top: 2px;margin-' . $text_align_reverse . ':6px">' . $tipOfEmail['title'] . '
                                    </h2>
                                    </div>
                                    <div class="content__tip--description" style=" color: #303032; font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif; font-size: 16px; font-weight: 400; line-height: 22px; margin: 0; padding: 0; text-decoration: none;">
                                    ' . $tipOfEmail['content'] . '
                                    </div>
                                                            
                                </td>                       
                            </tr>                 
                        </tbody> 
                     </table>
                 </div>';
}

$email_body = '
        <div class="mail-body" style="direction: ' . $dir . ';background-color: #F7F9FA;  font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif;; margin: 0; padding: 39px 0; text-decoration: none;">
            <div class="main-section" style=" font-family:  -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue,sans-serif;; margin:0 auto;  padding: 0 5px; text-decoration: none; width: 100%; max-width: 618px; box-sizing: border-box;">
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

    $email_body .= '<table width="100%" style="width: 100%; table-layout: fixed;">
                        <tbody>
                            <tr>
                               <td style="padding: 32px;white-space: pre-wrap;font-size: 16px;overflow-wrap: break-word;word-wrap: break-word;">' .wp_kses_post($content) . '</td>                   
                            </tr>
                        </tbody>   
                    </table>';
}
$email_body .= '</div>
                ' .$email_performance_html . $privacyBox .'
                 ' . $tip_html . '
                 ' . $advanced_reporting_ad . $email_footer . $copyright . '</div></div>';


echo $email_body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped