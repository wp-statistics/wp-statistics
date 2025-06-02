<?php

namespace WP_Statistics\Service\Admin\ExportImportRest;

use WP_Statistics\Abstracts\BaseRestAPI;
use WP_STATISTICS\User;
use WP_Error;
use WP_REST_Request;

class ImportRestController extends BaseRestAPI
{
    /**
     * ImportRestController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->endpoint = 'import-settings';
    }


    /**
     * Checks whether the current user has permission to access import settings.
     *
     * This method is used as the permission_callback for import-settings routes.
     *
     * @return bool|WP_Error True if user has permission, otherwise WP_Error object.
     */
    public function permissionCallback(WP_REST_Request $request)
    {
        if (!User::Access('manage')) {
            return new WP_Error(
                'rest_cannot_access',
                __('Sorry, you are not authorized to Import the settings.', 'wp-statistics'),
                ['status' => rest_authorization_required_code()]
            );
        }

        return true;
    }

    /**
     * Handle the import-settings REST API request.
     *
     * This method will process the actual import of settings.
     * The current implementation is a placeholder and should be
     * implemented to return plugin settings in a structured format.
     *
     * @param WP_REST_Request $request The incoming REST request (optional for future use).
     * @return \WP_REST_Response|WP_Error The REST response or error.
     */
    public function handle()
    {
    }
}