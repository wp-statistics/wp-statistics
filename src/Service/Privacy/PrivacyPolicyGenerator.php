<?php

namespace WP_Statistics\Service\Privacy;

use WP_Statistics\Components\Option;
use WP_Statistics\Utils\User;

/**
 * Privacy Policy Generator for WP Statistics v15.
 *
 * Generates dynamic privacy policy content based on current plugin settings.
 * Used for WordPress Privacy Policy page.
 *
 * @since 15.0.0
 */
class PrivacyPolicyGenerator
{
    /**
     * Generate privacy policy content.
     *
     * @return string HTML content for privacy policy.
     */
    public function generate()
    {
        $content = '<div class="wp-suggested-text">';

        // Admin-only tutorial text
        $content .= $this->getTutorialSection();

        // Consent-based content
        $content .= $this->getConsentSection();

        // Logged-in user tracking
        $content .= $this->getVisitorLogSection();

        // IP address handling
        $content .= $this->getIpAddressSection();

        $content .= '</div>';

        /**
         * Filter the privacy policy content.
         *
         * @param string $content Generated privacy policy HTML.
         */
        return apply_filters('wp_statistics_privacy_policy_content', $content);
    }

    /**
     * Get tutorial section (admin only).
     *
     * @return string HTML content.
     */
    private function getTutorialSection()
    {
        if (!User::isAdmin()) {
            return '';
        }

        return '<p class="privacy-policy-tutorial">' .
            __('The text that follows has been generated from your current <b>WP Statistics</b> configuration and details the visitor data your site collects, the reasons for that collection, how the information is stored, and who can access it. Copy the wording into your privacy policy and adjust the style as needed; if you later change any WP Statistics option or install another tool that affects data collection, regenerate this section so your policy remains accurate. For complete compliance, have a qualified lawyer review the final version.', 'wp-statistics') .
            '</p>' .
            '<p>' .
            __('<b>We use the WP Statistics plugin to analyze traffic on our website.</b> WP Statistics stores its data on our own server and does not transmit it to any third parties. Below, we describe what information we collect through WP Statistics, why we collect it, and how it is handled. If you have questions or concerns about our analytics practices, please contact us or review the <a href="https://wp-statistics.com/resources/wp-statistics-data-privacy/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy-policy" target="_blank">WP Statistics Data Privacy guide.</a>', 'wp-statistics') .
            '</p>';
    }

    /**
     * Get consent-based privacy section.
     *
     * @return string HTML content.
     */
    private function getConsentSection()
    {
        if (!Option::getValue('consent_level_integration')) {
            return '';
        }

        return '<p>' .
            __('We do <b>not</b> collect or store any information that can identify you personally. This means:', 'wp-statistics') .
            '</p>' .
            '<ul>' .
            '<li>' . __('<b>No Cookies</b>: We do not set tracking cookies or similar identifiers in your browser.', 'wp-statistics') . '</li>' .
            '<li>' . __('<b>Anonymized IP</b>: Your IP address is anonymized and securely processed (e.g., hashed) so it can\'t be reversed to identify you.', 'wp-statistics') . '</li>' .
            '<li>' . __('<b>Aggregated Statistics</b>: We only track general activity like page views or country of access.', 'wp-statistics') . '</li>' .
            '<li>' . __('<b>Local Storage</b>: All data is stored locally on our server; we never share it with third parties.', 'wp-statistics') . '</li>' .
            '</ul>';
    }

    /**
     * Get visitor log section.
     *
     * @return string HTML content.
     */
    private function getVisitorLogSection()
    {
        if (!Option::getValue('visitors_log')) {
            return '';
        }

        return '<p>' .
            __('We track certain details about logged-in visitors on our site. Specifically:', 'wp-statistics') .
            '</p>' .
            '<ul>' .
            '<li>' . __('<b>User ID & Page Views</b>: If you are logged in, we associate your account (username/ID) with your page visits.', 'wp-statistics') . '</li>' .
            '<li>' . __('<b>Purpose</b>: This helps us understand how registered members use our site and improve their experience.', 'wp-statistics') . '</li>' .
            '<li>' . __('<b>Local & Secure Storage</b>: These logs are kept securely on our server and are not shared externally.', 'wp-statistics') . '</li>' .
            '<li>' . __('<b>Your Choices</b>: You can choose to browse without logging in if you prefer not to link your visits with your user account. You may also contact us to request removal of any collected data where legally applicable.', 'wp-statistics') . '</li>' .
            '</ul>';
    }

    /**
     * Get IP address handling section.
     *
     * @return string HTML content.
     */
    private function getIpAddressSection()
    {
        // Skip IP section when not storing raw IPs
        if (!Option::getValue('store_ip')) {
            return '';
        }

        return '<p>' .
            __('We store visitors\' <b>IP addresses</b> in a way that may allow them to be identifiable. Specifically:', 'wp-statistics') .
            '</p>' .
            '<ul>' .
            '<li>' . __('<b>Collecting IP Data</b>: Your IP address is recorded alongside your page visits, which can sometimes be used to estimate your location (country, city).', 'wp-statistics') . '</li>' .
            '<li>' . __('<b>Why We Store IPs</b>: This helps us detect unique visits, identify possible misuse or security issues, and gather accurate traffic data.', 'wp-statistics') . '</li>' .
            '<li>' . __('<b>Data Protection</b>: We keep IP records on our own server, secured and accessible only to authorized staff. We do not share them unless required by law.', 'wp-statistics') . '</li>' .
            '<li>' . __('<b>Privacy Considerations</b>: Since IP addresses may be considered personal data, we take reasonable measures to protect them. You can contact us regarding removal or other data rights if you feel your IP address is personally identifying.', 'wp-statistics') . '</li>' .
            '</ul>';
    }
}
