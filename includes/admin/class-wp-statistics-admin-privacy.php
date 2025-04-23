<?php

namespace WP_STATISTICS\Admin;

use WP_STATISTICS\Option;
use WP_STATISTICS\PrivacyErasers;
use WP_STATISTICS\PrivacyExporter;
use WP_STATISTICS\User;
use WP_Statistics\Service\Admin\PrivacyAudit\Faqs\RequireConsent;

class Privacy
{
    public function __construct()
    {
        add_action('admin_init', array($this, 'add_privacy_message'));

        add_filter('wp_privacy_personal_data_exporters', array($this, 'register_exporters'));
        add_filter('wp_privacy_personal_data_erasers', array($this, 'register_erasers'));
    }

    /**
     * Adds the privacy message on WP Statistics privacy page.
     */
    public function add_privacy_message()
    {
        if (function_exists('wp_add_privacy_policy_content')) {
            wp_add_privacy_policy_content(__('WP Statistics', 'wp-statistics'), $this->get_privacy_message());
        }
    }

    /**
     * Add privacy policy content for the privacy policy page.
     *
     * @since 3.4.0
     */
    private function get_privacy_message()
    {
        $content = '<div class="wp-suggested-text">';

        if (User::isAdmin()) {
            $content .= '<p class="privacy-policy-tutorial">' .
                __('The text that follows has been generated from your current <b>WP Statistics</b> configuration and details the visitor data your site collects, the reasons for that collection, how the information is stored, and who can access it. Copy the wording into your privacy policy and adjust the style as needed; if you later change any WP Statistics option or install another tool that affects data collection, regenerate this section so your policy remains accurate. For complete compliance, have a qualified lawyer review the final version.', 'wp-statistics') .
                '</p>' .
                '<p>' . __('<b>We use the WP Statistics plugin to analyze traffic on our website.</b> WP Statistics stores its data on our own server and does not transmit it to any third parties. Below, we describe what information we collect through WP Statistics, why we collect it, and how it is handled. If you have questions or concerns about our analytics practices, please contact us or review the <a href="https://wp-statistics.com/resources/wp-statistics-data-privacy/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy-policy" target="_blank">WP Statistics Data Privacy guide.</a>', 'wp-statistics') . '</p>';
        }

        if (RequireConsent::getStatus() == 'success') {
            $content .= '<p>' .
                __('We do <b>not</b> collect or store any information that can identify you personally. This means:', 'wp-statistics') .
                '</p>' .
                '<ul>' .
                '<li>' . __('<b>No Cookies</b>: We do not set tracking cookies or similar identifiers in your browser.', 'wp-statistics') . '</li>' .
                '<li>' . __('<b>Anonymized IP</b>: Your IP address is anonymized and securely processed (e.g., hashed) so it can’t be reversed to identify you.', 'wp-statistics') . '</li>' .
                '<li>' . __('<b>Aggregated Statistics</b>: We only track general activity like page views or country of access.', 'wp-statistics') . '</li>' .
                '<li>' . __('<b>Local Storage</b>: All data is stored locally on our server; we never share it with third parties.', 'wp-statistics') . '</li>' .
                '</ul>';
        }

        if (Option::get('visitors_log')) {
            $content .= '<p>' .
                __('We track certain details about logged-in visitors on our site. Specifically:', 'wp-statistics') .
                '</p>' .
                '<ul>' .
                '<li>' . __('<b>User ID & Page Views</b>: If you are logged in, we associate your account (username/ID) with your page visits.', 'wp-statistics') . '</li>' .
                '<li>' . __('<b>Purpose</b>: This helps us understand how registered members use our site and improve their experience.', 'wp-statistics') . '</li>' .
                '<li>' . __('<b>Local & Secure Storage</b>: These logs are kept securely on our server and are not shared externally.', 'wp-statistics') . '</li>' .
                '<li>' . __('<b>Your Choices</b>: You can choose to browse without logging in if you prefer not to link your visits with your user account. You may also contact us to request removal of any collected data where legally applicable.', 'wp-statistics') . '</li>' .
                '</ul>';
        }

        if (!Option::get('anonymize_ips') || !Option::get('hash_ips')) {
            $content .= '<p>' .
                __('We store visitors’ <b>IP addresses</b> in a way that may allow them to be identifiable. Specifically:', 'wp-statistics') .
                '</p>' .
                '<ul>' .
                '<li>' . __('<b>Collecting IP Data</b>: Your IP address is recorded alongside your page visits, which can sometimes be used to estimate your location (country, city).', 'wp-statistics') . '</li>' .
                '<li>' . __('<b>Why We Store IPs</b>: This helps us detect unique visits, identify possible misuse or security issues, and gather accurate traffic data.', 'wp-statistics') . '</li>' .
                '<li>' . __('<b>Data Protection</b>: We keep IP records on our own server, secured and accessible only to authorized staff. We do not share them unless required by law.', 'wp-statistics') . '</li>' .
                '<li>' . __('<b>Privacy Considerations</b>: Since IP addresses may be considered personal data, we take reasonable measures to protect them. You can contact us regarding removal or other data rights if you feel your IP address is personally identifying.', 'wp-statistics') . '</li>' .
                '</ul>';
        }

        if (Option::get('store_ua')) {
            $content .= '<p>' .
                __('We record <b>full User-Agent strings</b> for each visitor’s browser/device. This includes:', 'wp-statistics') .
                '</p>' .
                '<ul>' .
                '<li>' . __('<b>Detailed Browser/OS Info</b>: The User-Agent string reveals your browser version, operating system type, and sometimes device model or other technical specifics.', 'wp-statistics') . '</li>' .
                '<li>' . __('<b>Purpose</b>: We use this information for diagnostic or compatibility analysis, to ensure our site functions well across different setups.', 'wp-statistics') . '</li>' .
                '<li>' . __('<b>Potential Identifiability</b>: Although this data generally does not include your name or email, detailed User-Agent strings can (in rare cases) be used to identify unique browsing configurations.', 'wp-statistics') . '</li>' .
                '<li>' . __('<b>Local Storage</b>: All User-Agent data is stored on our server and not shared with third parties unless required by law. We periodically review and may remove these logs.', 'wp-statistics') . '</li>' .
                '</ul>';
        }

        $content .= '</div>';

        return apply_filters('wp_statistics_privacy_policy_content', $content);
    }

    /**
     * Integrate this exporter implementation within the WordPress core exporters.
     *
     * @param array $exporters List of exporter callbacks.
     * @return array
     */
    public function register_exporters($exporters = array())
    {
        $exporters['wp-statistics-visitor-data'] = array(
            'exporter_friendly_name' => __('Visitor Data - WP Statistics', 'wp-statistics'),
            'callback'               => array(PrivacyExporter::class, 'visitorsDataExporter'),
        );

        return $exporters;
    }

    /**
     * Integrate this eraser implementation within the WordPress core erasers.
     *
     * @param array $erasers List of eraser callbacks.
     * @return array
     */
    public function register_erasers($erasers = array())
    {
        $erasers['wp-statistics-visitor-data'] = array(
            'eraser_friendly_name' => __('Visitor Data - WP Statistics', 'wp-statistics'),
            'callback'             => array(PrivacyErasers::class, 'visitorsDataEraser'),
        );

        return $erasers;
    }
}

new Privacy();
