<?php

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly


// Setting up the logo.
$final_logo = '<a target="_blank" style="text-decoration: none;" href="' . $logo_url . '"><img class="logo-image" border="0" vspace="0" hspace="0" src="' . $logo_image . '" style="-ms-interpolation-mode: bicubic; border: none; max-width: 100%; width: 100%;" data-style="LogoWidthTo100px"/></a>';

// Advertisement For WP Statistics Advanced Report Plugin
$advanced_reporting_ad = is_plugin_active('wp-statistics-advanced-reporting/wp-statistics-advanced-reporting.php') ? '' :
    '<div class="advanced-report-section" style="padding: 0;margin: 40px 0 0 0;box-sizing: border-box;-webkit-font-smoothing: antialiased;border: 1px solid #979797;">
                                <h2 style="padding: 20px 30px;margin: 0;box-sizing: border-box;-webkit-font-smoothing: antialiased;text-align: center;color: white;font-weight: 700;font-size: 14px;background-color: #404bf2;display: block;">Advanced Reporting</h2>
                                <h3 style="padding: 0;margin: 30px auto 10px auto;box-sizing: border-box;-webkit-font-smoothing: antialiased;text-align: center;color: #4a4a4a;font-size: 16px;line-height: 1.6;font-weight: 700;">' . __('Are you looking for more reports with charts?', 'wp-statistics') . '</h3>
                                <p style="padding: 0;margin: auto;box-sizing: border-box;-webkit-font-smoothing: antialiased;color: #585858;text-align: center;line-height: 1.4;font-size: 14px;margin-bottom: 20px;max-width: 90%;">
                                    ' . __('A summary of stats, hits, search engine referrals, top referrals, pages and more in your email!', 'wp-statistics') . '
                                </p>
                
                                <a target="_blank" href="https://wp-statistics.com/product/wp-statistics-advanced-reporting/" style="width: 225px;padding: 12px 10px;margin: 40px auto;box-sizing: border-box;-webkit-font-smoothing: antialiased;background-color: #404bf2;display: block;text-align: center;font-weight: 700;font-size: 14px;color: white;text-decoration: none;">' . __('Unlock Advanced Reporting', 'wp-statistics') . '</a>
                            </div>';

$email_body = '<table role="presentation" class="body" style="background-color: #f6f6f6; border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;" cellspacing="0" cellpadding="0" border="0">
    <tbody>
    <tr>
        <td class="container" style="Margin: 0 auto !important; display: block; font-size: 14px; max-width: 580px; padding: 10px; vertical-align: top; width: 580px;">
            <div class="content" style="Margin: 0 auto; box-sizing: border-box; display: block; max-width: 580px; padding: 10px;">
                <table class="logo" style="border-collapse: separate; margin: 20px auto; mso-table-lspace: 0pt; mso-table-rspace: 0pt; text-align: center; width: 40%;" data-style="HeaderTo100%">
                    <tbody>
                    <tr>
                        <td style="font-size: 14px; vertical-align: top;">
                          ' . $final_logo . '
                        </td>
                    </tr>
                    </tbody>
                <tr>
                    <td align="center" valign="top" style="white-space: nowrap;border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 13px; font-weight: 400; line-height: 150%;padding-top: 20px;padding-bottom: 20px;color: #999999;font-family: Nunito;" class="footer">
                    ' . $email_title . '                        
                    </td>
                </tr>
            </table>            
            
            ' . $email_header . '

            
             <!-- START CENTERED WHITE CONTAINER -->
                <span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; mso-hide: all; opacity: 0; overflow: hidden; visibility: hidden; width: 0;">This is preheader text. Some clients will show this text as a preview.</span>
                <table role="presentation" class="main" style="background: #ffffff; border-collapse: separate; border-radius: 3px; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">

                    <!-- START MAIN CONTENT AREA -->
                    <tbody>
                    <tr>
                        <td class="wrapper" style="box-sizing: border-box; font-size: 14px; padding: 20px; vertical-align: top;">
                            <table role="presentation" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;" cellspacing="0" cellpadding="0" border="0">
                                <tbody>
                                <tr>
                                    <td style="font-size: 14px; vertical-align: top;">
                                    ' . wp_kses_post($content) . '
			                        </td>
			                    </tr>
			                        
                                <tr>
                                    <td>
                                    ' . $advanced_reporting_ad . '
                                    </td>
                                </tr>
                                    
			                    </tbody>
			                </table>
			            </td>
			        </tr>
             <!-- END MAIN CONTENT AREA -->
                </tbody>
            </table>

           <!-- START FOOTER -->
                <div class="footer" style="margin-top: 10px; clear: both; width: 100%;">
                
    
               ' . $email_footer . $copyright . '

                </div>
                <!-- END FOOTER -->

         <!-- END CENTERED WHITE CONTAINER -->
            </div>
        </td>
    </tr>
    </tbody>
</table>';

echo $email_body;