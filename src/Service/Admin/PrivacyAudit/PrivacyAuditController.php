<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit;

use InvalidArgumentException;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;

class PrivacyAuditController
{
    private $dataProvider;

    public function __construct()
    {
        $this->dataProvider = new PrivacyAuditDataProvider();
    }

    /**
     * Get latest privacy status information
     */
    public function getPrivacyStatus_action_callback()
    {
        check_ajax_referer('wp_rest', 'wps_nonce');

        // Get the compliance, audit and faq list status
        $response['compliance_status'] = $this->dataProvider->getComplianceStatus();
        $response['audit_list']        = $this->dataProvider->getAuditsStatus();
        $response['faq_list']          = $this->dataProvider->getFaqsStatus();

        // Send the response
        wp_send_json_success($response);
        exit;
    }


    /**
     * Update privacy audit status
     */
    public function updatePrivacyStatus_action_callback()
    {
        try {
            check_ajax_referer('wp_rest', 'wps_nonce');

            // Get and sanitize data
            $auditName   = Request::get('audit_name');
            $auditAction = Request::get('audit_action');

            // Find the audit class based on provided audit name
            $auditClass = $this->dataProvider->getAudit($auditName);

            // If action is not defined in the class, throw error
            if (!method_exists($auditClass, $auditAction)) {
                throw new InvalidArgumentException(esc_html__(sprintf("%s method is not defined for %s", $auditAction, $auditName), 'wp-statistics'));
            }

            // Call specified action from the audit class
            $auditClass::$auditAction();

            // Get the updated audit item status
            $response['compliance_status'] = $this->dataProvider->getComplianceStatus();
            $response['faq_list']          = $this->dataProvider->getFaqsStatus();
            $response['audit_item']        = $auditClass::getState();

            // Send the response
            wp_send_json_success($response);

        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }

        exit;
    }

    /**
     * Privacy compliance test result for WordPress site health.
     * 
     * @return array $result
     */
    public function privacyComplianceTest()
    {
        $complianceStatus   = $this->dataProvider->getComplianceStatus();
        $isPrivacyCompliant = $complianceStatus['percentage_ready'] == 100;

		$result = [
			'label'       => esc_html__( 'Your WP Statistics settings are privacy-compliant.', 'wp-statistics' ),
			'status'      => 'good',
			'badge'       => [
				'label' => esc_html__('Privacy', 'wp-statistics'),
				'color' => 'blue'
			],
			'description' => sprintf(
                __('<p>The settings in your WP Statistics account comply with the privacy regulations. Visit the <a target="_blank" href="%s">%s<span aria-hidden="true" class="dashicons dashicons-external"></span></a> to learn more about best practices.</p>', 'wp-statistics'),
                esc_url(Menus::admin_url(Menus::get_page_slug('privacy-audit'))),
                esc_html__('Privacy Audit page', 'wp-statistics')
            ),
            'test'        => 'wp_statistics_privacy_compliance_status'
		];

		if ($isPrivacyCompliant == false) {
			$result['label']          = esc_html__('Your WP Statistics settings are not privacy-compliant. Please update your settings.', 'wp-statistics');
			$result['description']    = sprintf(
                __('<p>Your WP Statistics settings do not meet the necessary privacy standards. Immediate adjustments are required to ensure compliance and protect user data. Please review and update your settings as recommended on the <a target="_blank" href="%s">%s<span aria-hidden="true" class="dashicons dashicons-external"></span></a>.</p>', 'wp-statistics'),
                esc_url(Menus::admin_url(Menus::get_page_slug('privacy-audit'))),
                esc_html__('Privacy Audit page', 'wp-statistics')
            );
			$result['status']         = 'recommended';
			$result['badge']['color'] = 'orange';
		}

		return $result;
    }
}
