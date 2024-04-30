<?php
namespace WP_Statistics\Service\PrivacyAudit;

use InvalidArgumentException;

class PrivacyAuditController
{

    public function getPrivacyStatus_action_callback()
    {
        check_ajax_referer('wp_rest', 'wps_nonce');

        // Get the compliance and audit list status
        $response['compliance_status'] = PrivacyAuditCheck::complianceStatus();
        $response['audit_list']        = PrivacyAuditCheck::auditListStatus();

        // Send the response
        wp_send_json($response);
        exit;
    }

    public function updatePrivacyStatus_action_callback()
    {
        check_ajax_referer('wp_rest', 'wps_nonce');

        // Get and sanitize data
        $actionName = isset($_POST['action_name']) ? sanitize_text_field($_POST['action_name']) : '';
        $actionType = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';

        // Find the audit item based on provided action name
        $item = PrivacyAuditCheck::$list[$actionName];

        // If action is not defined in the class, throw error
        if (!method_exists($item, $actionType)) {
            throw new InvalidArgumentException(esc_html__('Undefined action type.', 'wp-statistics'));
        }

        // Run the action
        $item::$actionType();

        // Get the updated audit item status 
        $response['compliance_status']  = PrivacyAuditCheck::complianceStatus();
        $response['audit_item']         = $item::getState();

        // Send the response
        wp_send_json($response);
        exit;
    }
}
