<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml">
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
    <title><?php echo esc_html(get_bloginfo('name')); ?> - Statistics Report</title>
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
            color: <?php echo esc_attr($primaryColor); ?>;
            text-decoration: none;
        }

        /* Utility Classes */
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }

        /* Button */
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: <?php echo esc_attr($primaryColor); ?>;
            color: #ffffff !important;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 6px;
        }

        .btn:hover {
            opacity: 0.9;
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
        }

        .metric-value {
            font-size: 28px;
            font-weight: 700;
            color: #18181b;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .metric-label {
            font-size: 12px;
            color: #71717a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .metric-change {
            font-size: 12px;
            margin-top: 4px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .metric-change.up { color: #16a34a; }
        .metric-change.down { color: #dc2626; }
        .metric-change.neutral { color: #71717a; }

        /* List Items */
        .list-table {
            width: 100%;
            border-collapse: collapse;
        }

        .list-item {
            border-bottom: 1px solid #e4e4e7;
        }

        .list-item td {
            padding: 12px 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .list-rank {
            width: 32px;
            font-size: 14px;
            font-weight: 600;
            color: #71717a;
        }

        .list-title {
            font-size: 14px;
            color: #18181b;
        }

        .list-stat {
            text-align: right;
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
            h1, h2, h3, h4, h5, h6, .metric-value, .list-title, .list-stat {
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
                <?php echo $content; ?>
            </div>
            <div class="email-footer">
                <p>
                    <?php
                    printf(
                        /* translators: %s: Site name */
                        esc_html__('Sent from %s', 'wp-statistics'),
                        esc_html(get_bloginfo('name'))
                    );
                    ?>
                </p>
                <p style="margin-top: 8px;">
                    <?php esc_html_e('Powered by', 'wp-statistics'); ?>
                    <a href="https://wp-statistics.com" style="color: <?php echo esc_attr($primaryColor); ?>;">WP Statistics</a>
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
