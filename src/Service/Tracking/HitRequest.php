<?php

namespace WP_Statistics\Service\Tracking;

use Exception;
use ErrorException;
use WP_Statistics\Components\Ip;
use WP_Statistics\Utils\Request;
use WP_Statistics\Utils\Signature;
use WP_Statistics\Utils\Validator;

/**
 * Immutable container for all hit parameters sent by the JS tracker.
 *
 * Created once per request via create(). Validates, sanitizes, and decodes
 * all values at construction time. Entities access these values through
 * VisitorProfile proxy getters — never from $_REQUEST directly.
 *
 * Supports backward-compatible fallbacks for legacy param names.
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
     * Parse and validate all hit parameters from the current HTTP request.
     *
     * @throws ErrorException If required params are missing or invalid.
     */
    public static function create(): self
    {
        $instance = new self();

        $instance->referrer      = $instance->parseReferrer();
        $instance->resourceId    = $instance->parseResourceId();
        $instance->resourceUri   = $instance->parseResourceUri();
        $instance->resourceUriId = $instance->parseResourceUriId();
        $instance->resourceType  = $instance->parseResourceType();
        $instance->timezone      = $instance->requireString('timezone');
        $instance->languageCode  = $instance->requireString('language_code');
        $instance->languageName  = $instance->requireString('language_name');
        $instance->screenWidth   = $instance->requireString('screen_width');
        $instance->screenHeight  = $instance->requireString('screen_height');
        $instance->userId        = (int) Request::get('user_id', 0, 'number');

        $instance->verifySignature();

        return $instance;
    }

    // ─── Parsers ──────────────────────────────────────────────────────

    private function parseResourceUriId(): int
    {
        $value = (int) self::getWithFallback('resource_uri_id', 'resourceUriId', 0, 'number');

        if ($value < 1) {
            self::fail('resource_uri_id');
        }

        return $value;
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

    private function parseReferrer(): string
    {
        $raw = self::getWithFallback('referrer', 'referred', '', 'raw');

        if (empty($raw)) {
            return '';
        }

        return urldecode(base64_decode($raw));
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
            throw new Exception(__('Invalid signature', 'wp-statistics'), 403);
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
            esc_html(sprintf(__('Invalid hit request: %s', 'wp-statistics'), $param)),
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
}
