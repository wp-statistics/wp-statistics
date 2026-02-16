<?php
/**
 * Email Report Template
 *
 * Single unified template for WP Statistics email reports.
 *
 * Available variables:
 * - $site_name (string) - Site name
 * - $site_url (string) - Site URL
 * - $period (string) - Period type (daily, weekly, biweekly, monthly)
 * - $period_label (string) - Human-readable period label
 * - $date_range (string) - Formatted date range
 * - $metrics (array) - Metrics data with value, change, label
 * - $top_pages (array) - Top pages array
 * - $top_referrers (array) - Top referrers array
 * - $top_author (string|null) - Top author name
 * - $top_category (string|null) - Top category name
 * - $top_post (string|null) - Top post title
 * - $dashboard_url (string) - Dashboard URL
 * - $settings_url (string) - Settings URL
 * - $primary_color (string) - Primary brand color (hex)
 * - $is_rtl (bool) - RTL language support
 *
 * @package WP_Statistics\Service\Messaging\Templates\Emails
 * @since 15.0.0
 */

use WP_Statistics\Utils\Format;

// Set defaults
$primary_color = isset($primary_color) ? $primary_color : '#404BF2';
$is_rtl        = isset($is_rtl) ? $is_rtl : is_rtl();
$dir           = $is_rtl ? 'rtl' : 'ltr';
$align         = $is_rtl ? 'right' : 'left';
$align_opp     = $is_rtl ? 'left' : 'right';
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr(get_locale()); ?>" dir="<?php echo esc_attr($dir); ?>" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
    <meta charset="utf-8">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no, url=no">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings xmlns:o="urn:schemas-microsoft-com:office:office">
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <style>
        td,th,div,p,a,h1,h2,h3,h4,h5,h6 {font-family: "Segoe UI", sans-serif; mso-line-height-rule: exactly;}
    </style>
    <![endif]-->
    <title><?php echo esc_html($site_name); ?> - <?php esc_html_e('Statistics Report', 'wp-statistics'); ?></title>
    <style>
        :root {
            color-scheme: light dark;
            supported-color-schemes: light dark;
        }

        body {
            margin: 0;
            padding: 0;
            width: 100%;
            word-break: break-word;
            -webkit-font-smoothing: antialiased;
            background-color: #f4f4f5;
        }

        .email-wrapper {
            background-color: #f4f4f5;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }

        .email-body {
            padding: 32px 24px;
        }

        /* Typography */
        h1, h2, h3, h4, h5, h6 {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #18181b;
        }

        p {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #52525b;
        }

        a {
            color: <?php echo esc_attr($primary_color); ?>;
            text-decoration: none;
        }

        /* Button */
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: <?php echo esc_attr($primary_color); ?>;
            color: #ffffff !important;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 6px;
        }

        /* Section */
        .section {
            margin-bottom: 32px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #18181b;
            margin-bottom: 16px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        /* Metrics Grid */
        .metrics-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .metric-cell {
            padding: 16px;
            text-align: center;
            border: 1px solid #e4e4e7;
            vertical-align: top;
            width: 25%;
        }

        .metric-value {
            font-size: 24px;
            font-weight: 700;
            color: #18181b;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .metric-label {
            font-size: 11px;
            color: #71717a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            margin-top: 4px;
        }

        .metric-change {
            font-size: 12px;
            margin-top: 4px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .change-up { color: #16a34a; }
        .change-down { color: #dc2626; }
        .change-neutral { color: #71717a; }

        /* List */
        .list-table {
            width: 100%;
            border-collapse: collapse;
        }

        .list-item {
            border-bottom: 1px solid #e4e4e7;
        }

        .list-item:last-child {
            border-bottom: none;
        }

        .list-item td {
            padding: 12px 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .list-rank {
            width: 28px;
            font-size: 14px;
            font-weight: 600;
            color: #71717a;
        }

        .list-title {
            font-size: 14px;
            color: #18181b;
        }

        .list-stat {
            text-align: <?php echo esc_attr($align_opp); ?>;
            font-size: 14px;
            font-weight: 600;
            color: #18181b;
        }

        /* Divider */
        .divider {
            height: 1px;
            background-color: #e4e4e7;
            margin: 24px 0;
        }

        /* Footer */
        .email-footer {
            padding: 24px;
            background-color: #fafafa;
            text-align: center;
        }

        .email-footer p {
            font-size: 12px;
            color: #71717a;
        }

        /* Dark Mode */
        @media (prefers-color-scheme: dark) {
            .email-wrapper {
                background-color: #18181b !important;
            }
            .email-container {
                background-color: #27272a !important;
            }
            h1, h2, h3, h4, h5, h6, .metric-value, .list-title, .list-stat, .section-title {
                color: #fafafa !important;
            }
            p, .metric-label, .list-rank {
                color: #a1a1aa !important;
            }
            .metric-cell, .list-item {
                border-color: #3f3f46 !important;
            }
            .divider {
                background-color: #3f3f46 !important;
            }
            .email-footer {
                background-color: #1f1f23 !important;
            }
        }

        /* Mobile */
        @media only screen and (max-width: 600px) {
            .email-body {
                padding: 24px 16px !important;
            }
            .metric-cell {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper" style="background-color: #f4f4f5; padding: 32px 16px;">
        <!--[if mso]>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
        <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0">
        <tr>
        <td>
        <![endif]-->
        <div class="email-container" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden;">
            <div class="email-body">

                <!-- Header -->
                <div class="section" style="text-align: center; margin-bottom: 32px;">
                    <h1 style="font-size: 24px; font-weight: 700; color: #18181b; margin-bottom: 8px;">
                        <?php
                        printf(
                            /* translators: %s: Period label (Daily, Weekly, etc.) */
                            esc_html__('%s Statistics Report', 'wp-statistics'),
                            esc_html($period_label)
                        );
                        ?>
                    </h1>
                    <p style="font-size: 14px; color: #71717a;">
                        <?php echo esc_html($date_range); ?>
                    </p>
                </div>

                <!-- Metrics Grid -->
                <?php if (!empty($metrics)) : ?>
                <div class="section">
                    <table class="metrics-grid" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <?php foreach ($metrics as $key => $metric) : ?>
                            <td class="metric-cell" style="padding: 16px; text-align: center; border: 1px solid #e4e4e7; vertical-align: top; width: 25%;">
                                <div class="metric-value" style="font-size: 24px; font-weight: 700; color: #18181b;">
                                    <?php echo esc_html(Format::abbreviateNumber($metric['value'])); ?>
                                </div>
                                <div class="metric-label" style="font-size: 11px; color: #71717a; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px;">
                                    <?php echo esc_html($metric['label']); ?>
                                </div>
                                <?php if (isset($metric['change']) && $metric['change'] != 0) : ?>
                                    <?php
                                    $changeClass = $metric['change'] > 0 ? 'change-up' : ($metric['change'] < 0 ? 'change-down' : 'change-neutral');
                                    $changeArrow = $metric['change'] > 0 ? '&uarr;' : ($metric['change'] < 0 ? '&darr;' : '&rarr;');
                                    $changeColor = $metric['change'] > 0 ? '#16a34a' : ($metric['change'] < 0 ? '#dc2626' : '#71717a');
                                    ?>
                                    <div class="metric-change <?php echo esc_attr($changeClass); ?>" style="font-size: 12px; margin-top: 4px; color: <?php echo esc_attr($changeColor); ?>;">
                                        <?php echo $changeArrow; ?> <?php echo esc_html(abs($metric['change'])); ?>%
                                    </div>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                    </table>
                </div>
                <?php endif; ?>

                <!-- Top Pages -->
                <?php if (!empty($top_pages)) : ?>
                <div class="section">
                    <div class="section-title" style="font-size: 16px; font-weight: 600; color: #18181b; margin-bottom: 16px;">
                        <?php esc_html_e('Top Pages', 'wp-statistics'); ?>
                    </div>
                    <table class="list-table" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <?php foreach ($top_pages as $index => $page) : ?>
                        <tr class="list-item" style="border-bottom: 1px solid #e4e4e7;">
                            <td class="list-rank" style="width: 28px; font-size: 14px; font-weight: 600; color: #71717a; padding: 12px 0;">
                                <?php echo esc_html($index + 1); ?>.
                            </td>
                            <td class="list-title" style="font-size: 14px; color: #18181b; padding: 12px 0;">
                                <?php if (!empty($page['url'])) : ?>
                                    <a href="<?php echo esc_url($page['url']); ?>" style="color: #18181b; text-decoration: none;">
                                        <?php echo esc_html($page['title']); ?>
                                    </a>
                                <?php else : ?>
                                    <?php echo esc_html($page['title']); ?>
                                <?php endif; ?>
                            </td>
                            <td class="list-stat" style="text-align: <?php echo esc_attr($align_opp); ?>; font-size: 14px; font-weight: 600; color: #18181b; padding: 12px 0;">
                                <?php echo esc_html(Format::abbreviateNumber($page['views'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>

                <!-- Top Referrers -->
                <?php if (!empty($top_referrers)) : ?>
                <div class="section">
                    <div class="section-title" style="font-size: 16px; font-weight: 600; color: #18181b; margin-bottom: 16px;">
                        <?php esc_html_e('Top Referrers', 'wp-statistics'); ?>
                    </div>
                    <table class="list-table" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <?php foreach ($top_referrers as $index => $referrer) : ?>
                        <tr class="list-item" style="border-bottom: 1px solid #e4e4e7;">
                            <td class="list-rank" style="width: 28px; font-size: 14px; font-weight: 600; color: #71717a; padding: 12px 0;">
                                <?php echo esc_html($index + 1); ?>.
                            </td>
                            <td class="list-title" style="font-size: 14px; color: #18181b; padding: 12px 0;">
                                <?php echo esc_html($referrer['domain']); ?>
                            </td>
                            <td class="list-stat" style="text-align: <?php echo esc_attr($align_opp); ?>; font-size: 14px; font-weight: 600; color: #18181b; padding: 12px 0;">
                                <?php echo esc_html(Format::abbreviateNumber($referrer['visitors'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>

                <div class="divider" style="height: 1px; background-color: #e4e4e7; margin: 24px 0;"></div>

                <!-- CTA Button -->
                <div style="text-align: center; margin: 32px 0;">
                    <a href="<?php echo esc_url($dashboard_url); ?>" class="btn" style="display: inline-block; padding: 12px 24px; background-color: <?php echo esc_attr($primary_color); ?>; color: #ffffff; font-size: 14px; font-weight: 600; text-decoration: none; border-radius: 6px;">
                        <?php esc_html_e('View Full Dashboard', 'wp-statistics'); ?>
                    </a>
                </div>

            </div>

            <!-- Footer -->
            <div class="email-footer" style="padding: 24px; background-color: #fafafa; text-align: center;">
                <p style="font-size: 12px; color: #71717a;">
                    <?php
                    printf(
                        /* translators: %s: Site name */
                        esc_html__('Sent from %s', 'wp-statistics'),
                        esc_html($site_name)
                    );
                    ?>
                </p>
                <p style="font-size: 12px; color: #71717a; margin-top: 8px;">
                    <?php esc_html_e('Powered by', 'wp-statistics'); ?>
                    <a href="https://wp-statistics.com" style="color: <?php echo esc_attr($primary_color); ?>;">WP Statistics</a>
                </p>
            </div>
        </div>
        <!--[if mso]>
        </td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        <![endif]-->
    </div>
</body>
</html>
