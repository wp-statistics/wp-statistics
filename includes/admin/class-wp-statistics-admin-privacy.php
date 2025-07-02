<?php

namespace WP_STATISTICS\Admin;

use WP_STATISTICS\Option;
use WP_STATISTICS\PrivacyErasers;
use WP_STATISTICS\PrivacyExporter;

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
        $content = '<div class="wp-suggested-text">' .
            '<p class="privacy-policy-tutorial">' .
            __('The template provided outlines the essential personal data that may be collected and stored by your store. This may change based on your settings and the plugins you use. It is advisable to seek legal counsel to determine the precise information to include in your privacy policy.', 'wp-statistics') .
            '</p>' .
            '<p>' . __('We gather information about you during your website visit.', 'wp-statistics') . '</p>' .
            '<h2>' . __('Information We Collect and Store', 'wp-statistics') . '</h2>' .
            '<p>' . __('During your visit, we monitor:', 'wp-statistics') . '</p>' .
            '<ul>' .
            '<li>' . __('Pages Viewed: This helps us analyze site usage statistics and understand user behavior.', 'wp-statistics') . '</li>' .
            '<li>' . __('Browser user agent: we’ll use this for purposes like creating charts of views, most used browsers, etc.', 'wp-statistics') . '</li>';

        if (!Option::get('anonymize_ips') and !Option::get('hash_ips')) {
            $content .= '<li>' . __('IP address', 'wp-statistics') . '</li>';
        } else {
            if (Option::get('anonymize_ips')) {
                $content .= '<li>' . __('An anonymize string created from your ip address, For example, 888.888.888.888 > 888.888.888.000).', 'wp-statistics') . '</li>';
            }

            if (Option::get('hash_ips')) {
                $content .= '<li>' . __('An hashed string created from your ip address.', 'wp-statistics') . '</li>';
            }
        }

        $content .= '</ul></div>';

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
