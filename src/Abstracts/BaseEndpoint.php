<?php

namespace WP_Statistics\Abstracts;

use WP_Statistics\Components\Ajax;
use WP_Statistics\Utils\Request;
use WP_Statistics\Utils\User;
use Exception;

/**
 * Abstract base for AJAX endpoints that use a single action + sub_action routing pattern.
 *
 * Subclasses define getActionName(), getSubActions(), and getErrorCode().
 * Shared register(), handleRequest(), and verifyRequest() live here.
 *
 * @since 15.0.0
 */
abstract class BaseEndpoint
{
    /**
     * AJAX action name (without wp_statistics_ prefix).
     * e.g. 'settings', 'tools', 'import_export'
     */
    abstract protected function getActionName(): string;

    /**
     * Map of sub_action => handler method name.
     * e.g. ['get_config' => 'getSettingsConfig', 'save_tab' => 'saveTabSettings']
     */
    abstract protected function getSubActions(): array;

    /**
     * Error code for JSON error responses.
     * e.g. 'settings_error', 'tools_error'
     */
    abstract protected function getErrorCode(): string;

    /**
     * Register the single AJAX endpoint.
     */
    public function register(): void
    {
        Ajax::register($this->getActionName(), [$this, 'handleRequest'], false);
    }

    /**
     * Route incoming request to the appropriate sub-action handler.
     */
    public function handleRequest(): void
    {
        try {
            $this->verifyRequest();

            $subAction = sanitize_key(Request::get('sub_action', ''));

            if (empty($subAction)) {
                throw new Exception(__('Sub-action is required.', 'wp-statistics'));
            }

            $subActions = $this->getSubActions();

            if (!isset($subActions[$subAction])) {
                throw new Exception(
                    sprintf(__('Invalid sub-action: %s', 'wp-statistics'), $subAction)
                );
            }

            $method = $subActions[$subAction];
            $this->$method();
        } catch (Exception $e) {
            wp_send_json_error([
                'code'    => $this->getErrorCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Verify AJAX request (nonce + capability).
     */
    protected function verifyRequest(): void
    {
        if (!Request::isFrom('ajax')) {
            throw new Exception(__('Invalid request.', 'wp-statistics'));
        }

        if (!User::hasAccess('manage')) {
            throw new Exception(__('You do not have permission to perform this action.', 'wp-statistics'));
        }

        if (!check_ajax_referer('wp_statistics_dashboard_nonce', 'wps_nonce', false)) {
            throw new Exception(__('Security check failed. Please refresh the page and try again.', 'wp-statistics'));
        }
    }
}
