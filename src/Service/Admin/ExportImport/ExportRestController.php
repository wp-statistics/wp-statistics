<?php

namespace WP_Statistics\Service\Admin\ExportImport;

use WP_Statistics\Abstracts\BaseRestAPI;
use WP_STATISTICS\User;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class ExportRestController extends BaseRestAPI
{
    /**
     * ExportRestController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->endpoint = 'export/(?P<driver>[a-zA-Z0-9-_]+)';
    }

    /**
     * Get validation arguments for the REST API endpoint
     *
     * @return array
     */
    protected function getArgs()
    {
        return [
            'driver' => [
                'validate_callback' => function ($param) {
                    return is_string($param) && !empty($param) && preg_match('/^[a-zA-Z0-9-_]+$/', $param);
                },
                'required'          => true,
                'description'       => esc_html__('The export driver to use', 'wp-statistics'),
                'type'              => 'string'
            ]
        ];
    }

    /**
     * Checks export permissions.
     *
     * @param WP_REST_Request $request
     * @return bool|WP_Error
     */
    public function permissionCallback(WP_REST_Request $request)
    {
        if (!User::Access('manage')) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('Sorry, you are not authorized to export settings.', 'wp-statistics'),
                ['status' => rest_authorization_required_code()]
            );
        }

        return true;
    }

    /**
     * Handle export request.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function handle(WP_REST_Request $request)
    {
        try {
            $driver              = sanitize_text_field($request->get_param('driver'));
            $exportImportHandler = new ExportImportHandler($driver);
            $result              = $exportImportHandler->export($request);

            return new WP_REST_Response($result);
        } catch (\Exception $e) {
            return new WP_Error(
                'export_failed',
                $e->getMessage(),
                ['status' => 400]
            );
        }
    }
}