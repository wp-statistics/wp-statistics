<?php

namespace WP_Statistics\Service\Admin;

use WP_Statistics\Components\Ajax;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Option;
use Exception;
use WP_STATISTICS\User;

/**
 * @todo Send responses in a standard format using Ajax::success() or Ajax::error()
 */
class AjaxOptionUpdater
{
    /**
     * Initializes registering the AJAX handler for the admin area.
     *
     * @return void
     */
    public function init()
    {
        if (!is_admin()) {
            return;
        }

        Ajax::register('option_updater', [$this, 'optionUpdater'], false);
    }

    /**
     * Options this endpoint is allowed to change. Capability, email and other
     * sensitive settings must go through the settings page instead.
     *
     * @return array
     */
    public static function getAllowedOptions()
    {
        return ['share_anonymous_data'];
    }

    /**
     * Handles the AJAX request for updating a specified option in the system.
     *
     * @return void JSON response will be sent with either success or error message.
     * @throws Exception If the nonce is invalid, the option is missing, or any other error occurs.
     *
     */
    public function optionUpdater()
    {
        try {
            if (Request::isFrom('ajax') && User::Access('manage')) {
                check_ajax_referer('wp_statistics_option_updater', 'wps_nonce');

                $option = Request::get('option');
                $value  = Request::get('value');

                if (!in_array($option, self::getAllowedOptions(), true)) {
                    wp_send_json_error(__('This option cannot be changed here.', 'wp-statistics'), 403);
                }

                if ($value === 'true') {
                    $value = true;
                } elseif ($value === 'false') {
                    $value = false;
                }

                Option::update($option, $value);

                wp_send_json_success(['message' => __('Update option success.', 'wp-statistics')]);
            }
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }

        exit();
    }
}