<?php

namespace WP_Statistics\Service\Admin\Dashboard\Endpoints;

use WP_Statistics\Service\Admin\ReactApp\Contracts\PageActionInterface;
use WP_Statistics\Service\Admin\UserPreferences\UserPreferencesManager;
use WP_Statistics\Utils\Request as RequestUtil;

/**
 * User Preferences endpoint handler.
 *
 * Handles save and reset operations for user dashboard preferences.
 * Note: Get operation is not needed - preferences are returned via analytics query response.
 *
 * Registered globally in ReactAppManager::initAjax() as
 * 'wp_statistics_user_preferences' AJAX action.
 *
 * @since 15.0.0
 */
class UserPreferences implements PageActionInterface
{
    /**
     * WordPress plugin prefix for AJAX actions.
     */
    private const PREFIX = 'wp_statistics';

    /**
     * Preferences manager instance.
     *
     * @var UserPreferencesManager
     */
    private $manager;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->manager = new UserPreferencesManager();
    }

    /**
     * Get the endpoint identifier.
     *
     * @return string The endpoint identifier
     */
    public function getEndpointName()
    {
        return 'user_preferences';
    }

    /**
     * Get the full AJAX action name.
     *
     * @return string The full AJAX action name (e.g., 'wp_statistics_user_preferences')
     */
    public static function getActionName()
    {
        $instance = new static();
        return self::PREFIX . '_' . $instance->getEndpointName();
    }

    /**
     * Handle user preferences request.
     *
     * Routes to appropriate handler based on action_type parameter:
     * - save: Save preferences for a context
     * - reset: Reset preferences for a context
     *
     * @return array Response data
     */
    public function handleQuery()
    {
        $request = $this->getRequestData();

        if ($request === null) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'invalid_request',
                    'message' => __('Invalid or missing request data.', 'wp-statistics'),
                ],
            ];
        }

        $actionType = $request['action_type'] ?? '';

        switch ($actionType) {
            case 'save':
                return $this->handleSave($request);

            case 'reset':
                return $this->handleReset($request);

            default:
                return [
                    'success' => false,
                    'error'   => [
                        'code'    => 'invalid_action_type',
                        'message' => __('Invalid action type. Use "save" or "reset".', 'wp-statistics'),
                    ],
                ];
        }
    }

    /**
     * Handle save preferences request.
     *
     * @param array $request Request data.
     * @return array Response data.
     */
    private function handleSave(array $request): array
    {
        $context = $request['context'] ?? '';
        $data    = $request['data'] ?? [];

        if (empty($context)) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'missing_context',
                    'message' => __('Context parameter is required.', 'wp-statistics'),
                ],
            ];
        }

        if (empty($data) || !is_array($data)) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'missing_data',
                    'message' => __('Data parameter is required.', 'wp-statistics'),
                ],
            ];
        }

        $success = $this->manager->save($context, $data);

        if (!$success) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'save_failed',
                    'message' => __('Failed to save preferences.', 'wp-statistics'),
                ],
            ];
        }

        return [
            'success' => true,
            'message' => __('Preferences saved successfully.', 'wp-statistics'),
        ];
    }

    /**
     * Handle reset preferences request.
     *
     * @param array $request Request data.
     * @return array Response data.
     */
    private function handleReset(array $request): array
    {
        $context = $request['context'] ?? '';

        if (empty($context)) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'missing_context',
                    'message' => __('Context parameter is required.', 'wp-statistics'),
                ],
            ];
        }

        $success = $this->manager->reset($context);

        if (!$success) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'reset_failed',
                    'message' => __('Failed to reset preferences.', 'wp-statistics'),
                ],
            ];
        }

        return [
            'success' => true,
            'message' => __('Preferences reset successfully.', 'wp-statistics'),
        ];
    }

    /**
     * Get request data from the AJAX request.
     *
     * @return array|null Request data or null if invalid.
     */
    private function getRequestData(): ?array
    {
        $requestData = RequestUtil::getRequestData();

        if (empty($requestData)) {
            return null;
        }

        // Build normalized request data
        $data = [
            'action_type' => isset($requestData['action_type']) ? sanitize_text_field(wp_unslash($requestData['action_type'])) : '',
            'context'     => isset($requestData['context']) ? sanitize_text_field(wp_unslash($requestData['context'])) : '',
        ];

        // Handle 'data' parameter - can be JSON string or array
        if (isset($requestData['data'])) {
            $postData = wp_unslash($requestData['data']);
            if (is_string($postData)) {
                $decoded = json_decode($postData, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $data['data'] = $decoded;
                }
            } elseif (is_array($postData)) {
                $data['data'] = $postData;
            }
        }

        return $data;
    }
}
