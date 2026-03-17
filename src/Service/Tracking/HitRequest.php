<?php

namespace WP_Statistics\Service\Tracking;

use WP_Statistics\Utils\Request;

/**
 * Immutable container for all hit parameters sent by the JS tracker.
 *
 * Created once per request via create(). All values are sanitized and decoded
 * at construction time. Entities access these values through VisitorProfile
 * proxy getters — they should never read hit params from $_REQUEST directly.
 *
 * Supports backward-compatible fallbacks: when the new snake_case param name
 * is not present, falls back to the old camelCase/legacy name.
 */
final class HitRequest
{
    private int $resourceUriId;
    private ?int $resourceId;
    private string $resourceUri;
    private string $resourceType;
    private string $referrer;
    private string $timezone;
    private string $languageCode;
    private string $languageName;
    private string $screenWidth;
    private string $screenHeight;
    private int $userId;

    private function __construct()
    {
    }

    /**
     * Parse all hit parameters from the current HTTP request.
     *
     * For each param, tries the new snake_case name first, then falls back
     * to the old name for backward compatibility with headless integrations.
     */
    public static function create(): self
    {
        $instance = new self();

        // resource_uri_id (new) -> resourceUriId (old)
        $instance->resourceUriId = (int) self::getWithFallback('resource_uri_id', 'resourceUriId', 0, 'number');

        // resource_type (kept) -> source_type (legacy)
        $instance->resourceType = self::getWithFallback('resource_type', 'source_type', '');

        // timezone — unchanged
        $instance->timezone = Request::get('timezone', '');

        $instance->languageCode = Request::get('language_code', '');
        $instance->languageName = Request::get('language_name', '');
        $instance->screenWidth = Request::get('screen_width', '');
        $instance->screenHeight = Request::get('screen_height', '');

        // user_id — unchanged
        $instance->userId = (int) Request::get('user_id', 0, 'number');

        // resource_id (kept) -> source_id (legacy); null = not sent, 0 = valid
        $raw = self::getWithFallback('resource_id', 'source_id', null, 'number');
        $instance->resourceId = $raw !== null ? (int) $raw : null;

        // resource_uri -> page_uri (legacy); base64 encoded
        $rawUri = self::getWithFallback('resource_uri', 'page_uri', '');
        $instance->resourceUri = !empty($rawUri) ? base64_decode($rawUri) : '';

        // referrer (new) -> referred (old); base64 + URL encoded
        $rawReferrer = self::getWithFallback('referrer', 'referred', '', 'raw');
        $instance->referrer = !empty($rawReferrer) ? urldecode(base64_decode($rawReferrer)) : '';

        return $instance;
    }

    /**
     * Try the new param name first, fall back to the old name.
     *
     * @param string $newName    New snake_case param name.
     * @param string $oldName    Old camelCase/legacy param name.
     * @param mixed  $default    Default if neither is present.
     * @param string $sanitize   Sanitization type for Request::get().
     * @return mixed
     */
    private static function getWithFallback(string $newName, string $oldName, $default, string $sanitize = 'string')
    {
        // Use isset() instead of Request::has() because has() uses !empty()
        // which treats "0" as absent — breaking resource_id=0 and similar.
        if (isset($_REQUEST[$newName])) {
            return Request::get($newName, $default, $sanitize);
        }

        return Request::get($oldName, $default, $sanitize);
    }

    public function getResourceUriId(): int
    {
        return $this->resourceUriId;
    }

    public function getResourceId(): ?int
    {
        return $this->resourceId;
    }

    public function getResourceUri(): string
    {
        return $this->resourceUri;
    }

    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function getReferrer(): string
    {
        return $this->referrer;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    public function getLanguageName(): string
    {
        return $this->languageName;
    }

    public function getScreenWidth(): string
    {
        return $this->screenWidth;
    }

    public function getScreenHeight(): string
    {
        return $this->screenHeight;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
