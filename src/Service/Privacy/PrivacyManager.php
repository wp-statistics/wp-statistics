<?php

namespace WP_Statistics\Service\Privacy;

/**
 * Privacy Manager for WP Statistics v15.
 *
 * Handles GDPR compliance features including:
 * - WordPress Privacy API integration (export/erase)
 * - Privacy policy content generation
 *
 * @since 15.0.0
 */
class PrivacyManager
{
    /**
     * Privacy exporter instance.
     *
     * @var PrivacyExporter
     */
    private $exporter;

    /**
     * Privacy eraser instance.
     *
     * @var PrivacyEraser
     */
    private $eraser;

    /**
     * Privacy policy generator instance.
     *
     * @var PrivacyPolicyGenerator
     */
    private $policyGenerator;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->exporter        = new PrivacyExporter();
        $this->eraser          = new PrivacyEraser();
        $this->policyGenerator = new PrivacyPolicyGenerator();

        $this->registerHooks();
    }

    /**
     * Register WordPress hooks.
     *
     * @return void
     */
    private function registerHooks()
    {
        // Register privacy exporters and erasers
        add_filter('wp_privacy_personal_data_exporters', [$this, 'registerExporters']);
        add_filter('wp_privacy_personal_data_erasers', [$this, 'registerErasers']);

        // Add privacy policy content
        add_action('admin_init', [$this, 'addPrivacyPolicyContent']);
    }

    /**
     * Register data exporters with WordPress Privacy API.
     *
     * @param array $exporters List of exporter callbacks.
     * @return array Modified exporters list.
     */
    public function registerExporters($exporters = [])
    {
        $exporters['wp-statistics-visitor-data'] = [
            'exporter_friendly_name' => __('Visitor Data - WP Statistics', 'wp-statistics'),
            'callback'               => [$this->exporter, 'export'],
        ];

        return $exporters;
    }

    /**
     * Register data erasers with WordPress Privacy API.
     *
     * @param array $erasers List of eraser callbacks.
     * @return array Modified erasers list.
     */
    public function registerErasers($erasers = [])
    {
        $erasers['wp-statistics-visitor-data'] = [
            'eraser_friendly_name' => __('Visitor Data - WP Statistics', 'wp-statistics'),
            'callback'             => [$this->eraser, 'erase'],
        ];

        return $erasers;
    }

    /**
     * Add privacy policy content to WordPress privacy page.
     *
     * @return void
     */
    public function addPrivacyPolicyContent()
    {
        if (function_exists('wp_add_privacy_policy_content')) {
            wp_add_privacy_policy_content(
                __('WP Statistics', 'wp-statistics'),
                $this->policyGenerator->generate()
            );
        }
    }
}
