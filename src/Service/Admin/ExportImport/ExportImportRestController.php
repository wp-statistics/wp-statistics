<?php

namespace WP_Statistics\Service\Admin\ExportImport;

use WP_STATISTICS\RestAPI;
use WP_STATISTICS\User;
use WP_REST_Server;
use WP_Error;

class ExportImportRestController extends RestAPI
{
    /**
     * Registers REST API routes for export and import settings.
     *
     * This method hooks into the WordPress REST API initialization
     * to register the endpoints:
     * - POST /export-settings
     * - POST /import-settings
     *
     * Both routes require appropriate permissions.
     *
     * @return void
     */
    public function registerRoutes()
    {
        register_rest_route(
            self::$namespace,
            'export-settings',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'export'],
                'permission_callback' => [$this, 'hasExportImportPermission']
            ]
        );

        register_rest_route(
            self::$namespace,
            'import-settings',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'import'],
                'permission_callback' => [$this, 'hasExportImportPermission']
            ]
        );
    }

    /**
     * Checks whether the current user has permission to access export/import settings.
     *
     * This method is used as the permission_callback for import-settings and export-settings routes.
     *
     * @return bool|WP_Error True if user has permission, otherwise WP_Error object.
     */
    public function hasExportImportPermission()
    {
        if (!User::Access('manage')) {
            return new WP_Error(
                'rest_cannot_access',
                __('Sorry, you are not authorized to Import/Export the settings.', 'wp-statistics'),
                ['status' => rest_authorization_required_code()]
            );
        }

        return true;
    }

    /**
     *
     */
    public function export()
    {
    }

    /**
     *
     */
    public function import()
    {
    }
}