<?php

namespace WP_Statistics\Service\PrivacyAudit;

use InvalidArgumentException;

class PrivacyAuditController
{

    /**
     * Get latest privacy status information
     */
    public function getPrivacyStatus_action_callback()
    {
        check_ajax_referer('wp_rest', 'wps_nonce');

        // Get the compliance, audit and faq list status
        $response['compliance_status'] = PrivacyAuditCheck::complianceStatus();
        $response['audit_list']        = PrivacyAuditCheck::auditListStatus();
        $response['faq_list']          = PrivacyAuditCheck::faqListStatus();

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
            $auditName   = isset($_POST['audit_name']) ? sanitize_text_field($_POST['audit_name']) : '';
            $auditAction = isset($_POST['audit_action']) ? sanitize_text_field($_POST['audit_action']) : '';

            // Find the audit class based on provided audit name
            $auditClass = PrivacyAuditCheck::getAudit($auditName);

            // If action is not defined in the class, throw error
            if (!method_exists($auditClass, $auditAction)) {
                throw new InvalidArgumentException(esc_html__(sprintf("%s method is not defined for %s", $auditAction, $auditName), 'wp-statistics'));
            }

            // Call specified action from the audit class
            $auditClass::$auditAction();

            // Get the updated audit item status
            $response['compliance_status'] = PrivacyAuditCheck::complianceStatus();
            $response['faq_list']          = PrivacyAuditCheck::faqListStatus();
            $response['audit_item']        = $auditClass::getState();

            // Send the response
            wp_send_json_success($response);

        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }

        exit;
    }
}
