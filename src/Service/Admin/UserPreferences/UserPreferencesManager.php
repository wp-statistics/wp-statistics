<?php

namespace WP_Statistics\Service\Admin\UserPreferences;

use WP_Statistics\Utils\User;

/**
 * Manages user preferences for dashboard customizations.
 *
 * Stores and retrieves user preferences (column selections, widget order)
 * in WordPress user meta. All preferences are stored in a single meta entry
 * keyed by context identifiers.
 *
 * @since 15.0.0
 */
class UserPreferencesManager
{
    /**
     * Meta key for storing all user preferences.
     */
    public const META_KEY = 'wp_statistics_dashboard_preferences';

    /**
     * User ID for preference operations.
     *
     * @var int
     */
    private $userId;

    /**
     * Constructor.
     *
     * @param int $userId Optional. User ID. Defaults to current user.
     */
    public function __construct(int $userId = 0)
    {
        $this->userId = $userId ?: User::getId();
    }

    /**
     * Save preferences for a specific context.
     *
     * @param string $context Context identifier (e.g., 'visitors_overview', 'top_pages_table').
     * @param array  $data    Preference data to save.
     * @return bool True on success, false on failure.
     */
    public function save(string $context, array $data): bool
    {
        if (!$this->isValidContext($context)) {
            return false;
        }

        $allPreferences = $this->getAll();

        // Sanitize the data
        $sanitizedData = $this->sanitizeData($data);

        // Check if data has actually changed (excluding timestamp)
        $existingData = $allPreferences[$context] ?? [];
        unset($existingData['updated_at']);

        if ($existingData === $sanitizedData) {
            // Data unchanged, return success without saving
            return true;
        }

        // Add timestamp only when data changes
        $sanitizedData['updated_at'] = current_time('mysql');

        // Update the specific context
        $allPreferences[$context] = $sanitizedData;

        return (bool) User::saveMeta(self::META_KEY, $allPreferences, $this->userId);
    }

    /**
     * Get preferences for a specific context.
     *
     * @param string $context Context identifier.
     * @return array|null Preferences array or null if not found.
     */
    public function get(string $context): ?array
    {
        if (!$this->isValidContext($context)) {
            return null;
        }

        $allPreferences = $this->getAll();

        return $allPreferences[$context] ?? null;
    }

    /**
     * Reset preferences for a specific context.
     *
     * @param string $context Context identifier.
     * @return bool True on success, false on failure.
     */
    public function reset(string $context): bool
    {
        if (!$this->isValidContext($context)) {
            return false;
        }

        $allPreferences = $this->getAll();

        // Remove the specific context
        if (isset($allPreferences[$context])) {
            unset($allPreferences[$context]);

            // If no more preferences, delete the meta entirely
            if (empty($allPreferences)) {
                return delete_user_meta($this->userId, self::META_KEY);
            }

            return (bool) User::saveMeta(self::META_KEY, $allPreferences, $this->userId);
        }

        return true; // Already doesn't exist
    }

    /**
     * Check if preferences exist for a specific context.
     *
     * @param string $context Context identifier.
     * @return bool True if preferences exist.
     */
    public function exists(string $context): bool
    {
        if (!$this->isValidContext($context)) {
            return false;
        }

        $allPreferences = $this->getAll();

        return isset($allPreferences[$context]);
    }

    /**
     * Get all saved preferences for all contexts.
     *
     * @return array All preferences keyed by context.
     */
    public function getAll(): array
    {
        $preferences = User::getMeta(self::META_KEY, true, $this->userId);

        if (!is_array($preferences)) {
            return [];
        }

        return $preferences;
    }

    /**
     * Reset all preferences for all contexts.
     *
     * @return bool True on success, false on failure.
     */
    public function resetAll(): bool
    {
        return delete_user_meta($this->userId, self::META_KEY);
    }

    /**
     * Validate context name.
     *
     * Context names must be alphanumeric with underscores and hyphens only.
     *
     * @param string $context Context name to validate.
     * @return bool True if valid.
     */
    private function isValidContext(string $context): bool
    {
        if (empty($context)) {
            return false;
        }

        // Only allow alphanumeric characters, underscores, and hyphens
        return (bool) preg_match('/^[a-zA-Z0-9_-]+$/', $context);
    }

    /**
     * Recursively sanitize preference data.
     *
     * @param mixed $data Data to sanitize.
     * @return mixed Sanitized data.
     */
    private function sanitizeData($data)
    {
        if (is_array($data)) {
            $sanitized = [];
            foreach ($data as $key => $value) {
                // Sanitize the key but preserve casing (sanitize_key() lowercases everything)
                // Only allow alphanumeric, dashes, and underscores
                $sanitizedKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
                // Recursively sanitize the value
                $sanitized[$sanitizedKey] = $this->sanitizeData($value);
            }
            return $sanitized;
        }

        if (is_string($data)) {
            return sanitize_text_field($data);
        }

        if (is_int($data) || is_float($data)) {
            return $data;
        }

        if (is_bool($data)) {
            return $data;
        }

        return '';
    }
}
