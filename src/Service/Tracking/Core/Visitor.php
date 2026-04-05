<?php

namespace WP_Statistics\Service\Tracking\Core;

use WP_Statistics\Components\Ip;
use WP_Statistics\Components\Option;
use WP_Statistics\Service\Analytics\DeviceDetection\UserAgent;
use WP_Statistics\Service\Analytics\DeviceDetection\UserAgentService;
use WP_Statistics\Service\Analytics\Referrals\SourceDetector;
use WP_Statistics\Service\Consent\TrackingLevel;
use WP_Statistics\Service\Geolocation\GeolocationFactory;
use WP_Statistics\Utils\Url;

/**
 * Read-only visitor data for the current request.
 *
 * Combines:
 * - Server-resolved data (IP, geolocation, user agent) — always available
 * - Client-side data (via Payload) — only available in the hit pipeline
 *
 * Can be constructed without a Payload for server-only contexts (e.g. batch
 * engagement updates). Client-side methods return null in that case.
 *
 * Must work in WordPress SHORTINIT mode when Payload is present.
 *
 * @since 15.1.0
 */
final class Visitor
{
    private ?Payload $payload;
    private array $cache = [];

    public function __construct(?Payload $payload = null)
    {
        $this->payload = $payload;
    }

    // ── Browser input ───────────────────────────────────────────────

    public function getRequest(): ?Payload
    {
        return $this->payload;
    }

    // ── Server-resolved data (lazy, cached) ─────────────────────────

    public function getIp(): string
    {
        return $this->cached('ip', fn() => Ip::getCurrent());
    }

    public function getHashedIp(): string
    {
        return $this->cached('hashedIp', fn() => Ip::hash());
    }

    public function getStorableIp(): ?string
    {
        return $this->cached('storableIp', function () {
            if (!$this->payload || $this->payload->getTrackingLevel() !== TrackingLevel::FULL) {
                return null;
            }
            return Ip::getStorableIp();
        });
    }

    public function getLocation(): array
    {
        return $this->cached('location', fn() => GeolocationFactory::getLocation($this->getIp()));
    }

    public function getCountry(): string
    {
        return $this->getLocation()['country_code'] ?? '';
    }

    public function getCity(): string
    {
        return $this->getLocation()['city'] ?? '';
    }

    public function getRegion(): string
    {
        return $this->getLocation()['region'] ?? '';
    }

    public function getRegionCode(): string
    {
        return $this->getLocation()['region_code'] ?? '';
    }

    public function getContinent(): string
    {
        return $this->getLocation()['continent'] ?? '';
    }

    public function getUserAgent(): ?UserAgentService
    {
        return $this->cached('userAgent', fn() => UserAgent::getUserAgent());
    }

    public function getHttpUserAgent(): string
    {
        return $this->cached('httpUserAgent', fn() => UserAgent::getHttpUserAgent());
    }

    public function getReferrer(): ?string
    {
        return $this->cached('referrer', function () {
            $raw = $this->payload ? $this->payload->getReferrer() : '';
            if (empty($raw) || Url::isInternal($raw)) {
                return null;
            }
            $referrer = sanitize_url($raw);
            $protocol = Url::getProtocol($referrer);
            if (in_array($protocol, ['https', 'http'])) {
                return Url::getDomain($referrer);
            }
            return $referrer;
        });
    }

    public function isReferred(): bool
    {
        return !empty($this->getReferrer());
    }

    public function getSource(): SourceDetector
    {
        return $this->cached('source', function () {
            $raw      = $this->payload ? $this->payload->getReferrer() : '';
            $uri      = $this->payload ? $this->payload->getResourceUri() : '';
            $referrer = (empty($raw) || Url::isInternal($raw)) ? '' : $raw;
            return new SourceDetector($referrer, $uri);
        });
    }

    public function getUserId(): ?int
    {
        return $this->cached('userId', function () {
            if (!Option::getValue('visitors_log')) {
                return null;
            }
            if (!$this->payload || $this->payload->getTrackingLevel() !== TrackingLevel::FULL) {
                return null;
            }
            return $this->payload->getUserId();
        });
    }

    // ── Internal ────────────────────────────────────────────────────

    private function cached(string $key, callable $resolver)
    {
        if (!array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $resolver();
        }
        return $this->cache[$key];
    }
}
