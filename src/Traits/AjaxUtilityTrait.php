<?php
namespace WP_Statistics\Traits;

use Exception;
use WP_STATISTICS\User;
use WP_Statistics\Utils\Request;

trait AjaxUtilityTrait
{
    /**
     * Verifies if the current request is an AJAX request.
     *
     * If the request is not an AJAX request, it will exit the script.
     *
     * @return void
     */
    protected function verifyAjaxRequest()
    {
        if (!Request::isFrom('ajax')) {
            die(esc_html__('Request is not a valid AJAX request. Please try again!', 'wp-statistics'));
        }
    }

    /**
     * Verify nonce in the request.
     *
     * This function will check if the given nonce is valid for the given action.
     * If the nonce is invalid or expired, it will return a 403 error response with a message.
     *
     * @param int|string $action The action name to check the nonce against. Defaults to -1.
     * @param string $field The field name to check for the nonce. Defaults to '_wpnonce'.
     *
     * @throws Exception If the nonce is invalid or expired.
     */
    protected function verifyNonce($action = -1, $field = '_wpnonce')
    {
        $nonce = Request::get($field);

        if (!wp_verify_nonce($nonce, $action)) {
            throw new Exception(esc_html__('The request does not contain a valid nonce. Please try again.', 'wp-statistics'), 403);
        }
    }

    /**
     * Checks if the current user has the specified capability.
     *
     * If the user does not have the capability, it will return a 403 error response with a message.
     *
     * @param string $cap The capability to check.
     */
    protected function checkCapability($cap)
    {
        if (!User::Access($cap)) {
            throw new Exception(esc_html__('You do not have permission to perform this action. Please contact an administrator.', 'wp-statistics'), 403);
        }
    }

    /**
     * Checks if the AJAX request is valid and comes from the admin dashboard.
     *
     * @param string $action The action name to check.
     * @param string $field The field name to check.
     *
     * @throws Exception If the request is invalid or expired nonce.
     */
    protected function checkAdminReferrer($action = -1, $field = '_wpnonce')
    {
        $nonce    = Request::get($field);
        $adminUrl = strtolower(admin_url());
		$referer  = strtolower(wp_get_referer());

        if (!wp_verify_nonce($nonce, $action) || strpos($referer, $adminUrl) !== 0) {
            throw new Exception(esc_html__('The request does not come from the admin dashboard or is invalid.', 'wp-statistics'), 403);
        }
    }
}
