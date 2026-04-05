<?php

namespace WP_Statistics\Service\Tracking\Core;

use Exception;
use ErrorException;
use WP_Statistics\Components\Ip;
use WP_Statistics\Components\Option;
use WP_Statistics\Service\Consent\TrackingLevel;
use WP_Statistics\Service\Resources\ResourceResolver;
use WP_Statistics\Utils\Request;
use WP_Statistics\Utils\Signature;
use WP_Statistics\Utils\Validator;

/**
 * Immutable container for all hit parameters sent by the JS tracker or headless clients.
 *
 * Created once per request via parse(). Validates, sanitizes, and decodes
 * all values at construction time. Entities access these values through
 * Visitor — never from $_REQUEST directly.
 *
 * Supports backward-compatible fallbacks for legacy param names.
 * When resource_uri_id is not provided, it is auto-resolved from
 * resource_uri + resource_type + resource_id.
 */
final class Payload
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
    private string $trackingLevel;

    private function __construct()
    {
    }

    /**
     * Parse and validate all hit parameters from the current HTTP request.
     *
     * @throws ErrorException If required params are missing or invalid.
     */
    public static function parse(): self
    {
        $instance = new self();

        $instance->referrer      = $instance->parseReferrer();
        $instance->resourceId    = $instance->parseResourceId();
        $instance->resourceUri   = $instance->parseResourceUri();
        $instance->resourceType  = $instance->parseResourceType();
        $instance->resourceUriId = $instance->parseResourceUriId();
        $instance->timezone      = $instance->requireString('timezone');
        $instance->languageCode  = $instance->requireString('language_code');
        $instance->languageName  = $instance->requireString('language_name');
        $instance->screenWidth   = $instance->requireString('screen_width');
        $instance->screenHeight  = $instance->requireString('screen_height');
        $instance->userId        = (int) Request::get('user_id', 0, 'number');
        $instance->trackingLevel = $instance->parseTrackingLevel();

        $instance->verifySignature();

        return $instance;
    }

    // ─── Parsers ──────────────────────────────────────────────────────

    private function parseResourceUriId(): int
    {
        $value = (int) self::getWithFallback('resource_uri_id', 'resourceUriId', 0, 'number');

        if ($value >= 1) {
            return $value;
        }

        return $this->resolveResourceUriId();
    }

    /**
     * Resolve resource_uri_id from request data when not explicitly provided.
     *
     * @throws ErrorException If resource_uri is empty (nothing to resolve from).
     */
    private function resolveResourceUriId(): int
    {
        if (empty($this->resourceUri)) {
            self::fail('resource_uri');
        }

        $id = ResourceResolver::resolveUriId($this->resourceId, $this->resourceType, $this->resourceUri);

        if ($id < 1) {
            self::fail('resource_uri_id');
        }

        return $id;
    }

    private function parseResourceId(): ?int
    {
        $raw = self::getWithFallback('resource_id', 'source_id', null, 'number');

        if ($raw === null) {
            self::fail('resource_id');
        }

        return (int) $raw;
    }

    private function parseResourceUri(): string
    {
        $raw = self::getWithFallback('resource_uri', 'page_uri', '');

        if (empty($raw)) {
            return '';
        }

        $decoded = base64_decode($raw);

        if ($this->containsThreats($decoded)) {
            self::fail('resource_uri');
        }

        return $decoded;
    }

    private function parseResourceType(): string
    {
        return self::getWithFallback('resource_type', 'source_type', '');
    }

    /**
     * Parse the tracking level from the request.
     *
     * The JS tracker sends the consent-derived tracking level as a request param.
     * Falls back to FULL when no consent provider is configured, or NONE
     * (fail-closed) when a provider is active but no valid level was sent.
     */
    private function parseTrackingLevel(): string
    {
        if (!Option::getValue('consent_integration', false)) {
            return TrackingLevel::FULL;
        }

        $value = Request::get('tracking_level', '');

        if (in_array($value, TrackingLevel::all(), true)) {
            return $value;
        }

        return TrackingLevel::NONE;
    }

    private function parseReferrer(): string
    {
        $raw = self::getWithFallback('referrer', 'referred', '', 'raw');

        if (empty($raw)) {
            return '';
        }

        $decoded = urldecode(base64_decode($raw));

        if ($this->containsThreats($decoded)) {
            return '';
        }

        return $decoded;
    }

    /**
     * Read a required string param, failing if empty.
     */
    private function requireString(string $param): string
    {
        $value = Request::get($param, '');

        if (empty($value) || !is_string($value)) {
            self::fail($param);
        }

        return $value;
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    /**
     * Try the new param name first, fall back to the old name.
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

    /**
     * Verify the request signature against the parsed params.
     *
     * @throws Exception 403 if signature is missing or doesn't match.
     */
    private function verifySignature(): void
    {
        $signature = Request::get('signature', '');
        $payload   = [
            $this->resourceType,
            (int) $this->resourceId,
            (int) $this->userId,
        ];

        if (!Signature::check($payload, $signature)) {
            throw new Exception('Invalid signature', 403);
        }
    }

    private function containsThreats(string $value): bool
    {
        foreach (Validator::getThreatPatterns() as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fire the invalid hit action and throw.
     */
    private static function fail(string $param): void
    {
        do_action('wp_statistics_invalid_hit_request', false, Ip::getCurrent());

        throw new ErrorException(
            'Invalid hit request: ' . $param,
            400
        );
    }

    // ─── Getters ──────────────────────────────────────────────────────

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

    public function getTrackingLevel(): string
    {
        return $this->trackingLevel;
    }

}
