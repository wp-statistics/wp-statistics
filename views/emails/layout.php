<?php
/**
 * Master email report layout.
 *
 * Table-based, 600px max-width, inline CSS for cross-client compatibility.
 * Supports responsive (@media), RTL, and dark mode.
 *
 * @var string $logo_image    URL to logo image.
 * @var string $logo_url      URL the logo links to.
 * @var string $site_title    Site name.
 * @var string $content       Assembled section HTML from EmailReportRenderer.
 * @var string $copyright     Copyright HTML snippet.
 * @var bool   $is_rtl        Whether the site is RTL.
 * @var string $primary_color Primary brand color (default: #1e40af).
 * @var string $report_title  Report title (e.g., "Weekly Performance Report").
 * @var string $report_period Date range string.
 * @var string $dashboard_url URL to the WP Statistics dashboard.
 * @var string $settings_url  URL to notification settings.
 */

$primary_color = $primary_color ?? '#1e40af';
$report_title  = $report_title ?? '';
$report_period = $report_period ?? '';
$dashboard_url = $dashboard_url ?? '';
$settings_url  = $settings_url ?? '';
$content       = $content ?? '';
$copyright     = $copyright ?? '';
$footer_text   = $footer_text ?? '';
$logo_image    = $logo_image ?? '';
$logo_url      = $logo_url ?? '';
$site_title    = $site_title ?? '';
$is_rtl        = $is_rtl ?? false;

$dir        = $is_rtl ? 'rtl' : 'ltr';
$text_align = $is_rtl ? 'right' : 'left';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo esc_attr($dir); ?>" lang="<?php echo esc_attr(get_bloginfo('language')); ?>">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="color-scheme" content="light dark" />
    <meta name="supported-color-schemes" content="light dark" />
    <title><?php echo esc_html($report_title); ?></title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style type="text/css">
        /* Reset */
        body, table, td, p, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }

        /* Responsive */
        @media only screen and (max-width: 620px) {
            .email-container { width: 100% !important; max-width: 100% !important; }
            .stack-column { display: block !important; width: 100% !important; max-width: 100% !important; }
            .stack-column-center { text-align: center !important; }
            .mobile-padding { padding-left: 20px !important; padding-right: 20px !important; }
        }

        /* Dark mode */
        @media (prefers-color-scheme: dark) {
            .email-bg { background-color: #1a1a2e !important; }
            .email-body { background-color: #16213e !important; }
            .email-header-bg { background-color: #16213e !important; }
            .email-text { color: #e5e7eb !important; }
            .email-text-muted { color: #9ca3af !important; }
            .email-text-heading { color: #f3f4f6 !important; }
            .email-border { border-color: #374151 !important; }
            .email-footer-text { color: #9ca3af !important; }
            .email-footer-link { color: #9ca3af !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;" class="email-bg">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f3f4f6;" class="email-bg">
        <tr>
            <td align="center" style="padding:24px 16px;">

                <!-- Email Container -->
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" class="email-container" style="max-width:600px;width:100%;">

                    <!-- Header -->
                    <tr>
                        <td style="background-color:#ffffff;border-radius:8px 8px 0 0;padding:32px 40px 28px;text-align:<?php echo esc_attr($text_align); ?>;" class="mobile-padding email-header-bg">
                            <?php if (!empty($logo_image)) : ?>
                            <a href="<?php echo esc_url($logo_url); ?>" style="text-decoration:none;">
                                <img src="<?php echo esc_url($logo_image); ?>" alt="<?php echo esc_attr($site_title); ?>" width="140" style="display:block;margin-bottom:16px;max-width:140px;height:auto;" />
                            </a>
                            <?php else : ?>
                            <p style="margin:0 0 12px;font-size:13px;font-weight:600;color:#6b7280;" class="email-text-muted"><?php echo esc_html($site_title); ?></p>
                            <?php endif; ?>

                            <?php if (!empty($report_title)) : ?>
                            <p style="margin:0 0 4px;font-size:24px;font-weight:700;color:#111827;line-height:1.3;" class="email-text-heading"><?php echo esc_html($report_title); ?></p>
                            <?php endif; ?>

                            <?php if (!empty($report_period)) : ?>
                            <p style="margin:0;font-size:14px;color:#6b7280;" class="email-text-muted"><?php echo esc_html($report_period); ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- Header divider -->
                    <tr>
                        <td style="background-color:#ffffff;padding:0 40px;" class="mobile-padding email-body">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td style="border-top:1px solid #e5e7eb;font-size:1px;line-height:1px;" class="email-border">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="background-color:#ffffff;padding:28px 40px 36px;" class="mobile-padding email-body">
                            <?php
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Pre-rendered HTML sections
                            echo $content;
                            ?>
                        </td>
                    </tr>

                    <!-- Dashboard link -->
                    <?php if (!empty($dashboard_url)) : ?>
                    <tr>
                        <td style="background-color:#ffffff;padding:0 40px 32px;text-align:<?php echo esc_attr($text_align); ?>;" class="mobile-padding email-body">
                            <a href="<?php echo esc_url($dashboard_url); ?>" style="color:<?php echo esc_attr($primary_color); ?>;font-size:13px;font-weight:600;text-decoration:none;">
                                <?php esc_html_e('View full report on your dashboard', 'wp-statistics'); ?> &rarr;
                            </a>
                        </td>
                    </tr>
                    <?php endif; ?>

                    <!-- White card bottom rounded corners -->
                    <tr>
                        <td style="background-color:#ffffff;border-radius:0 0 8px 8px;height:4px;font-size:1px;line-height:1px;" class="email-body">&nbsp;</td>
                    </tr>

                    <!-- Footer (outside white card, on gray background) -->
                    <tr>
                        <td style="padding:20px 32px;text-align:center;" class="mobile-padding">
                            <?php if (!empty($footer_text)) : ?>
                            <p style="margin:0 0 10px;font-size:12px;color:#9ca3af;" class="email-footer-text"><?php echo esc_html($footer_text); ?></p>
                            <?php endif; ?>
                            <p style="margin:0 0 8px;font-size:12px;color:#9ca3af;" class="email-footer-text">
                                <?php if (!empty($settings_url)) : ?>
                                <a href="<?php echo esc_url($settings_url); ?>" style="color:#9ca3af;text-decoration:underline;" class="email-footer-link"><?php esc_html_e('Manage email preferences', 'wp-statistics'); ?></a>
                                <?php endif; ?>
                                <?php if (!empty($settings_url) && !empty($dashboard_url)) : ?>
                                    &nbsp;&middot;&nbsp;
                                <?php endif; ?>
                                <?php if (!empty($dashboard_url)) : ?>
                                <a href="<?php echo esc_url($dashboard_url); ?>" style="color:#9ca3af;text-decoration:underline;" class="email-footer-link"><?php esc_html_e('View Dashboard', 'wp-statistics'); ?></a>
                                <?php endif; ?>
                            </p>
                            <?php if (!empty($copyright)) : ?>
                            <p style="margin:0;font-size:11px;color:#b0b0b0;" class="email-footer-text">
                                <?php
                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                echo $copyright;
                                ?>
                            </p>
                            <?php endif; ?>
                        </td>
                    </tr>

                </table>
                <!-- /Email Container -->

            </td>
        </tr>
    </table>
</body>
</html>
