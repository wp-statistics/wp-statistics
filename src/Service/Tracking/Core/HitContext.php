<?php

namespace WP_Statistics\Service\Tracking\Core;

use WP_Statistics\Components\Ip;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Components\Option;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Service\Analytics\DeviceDetection\UserAgent;
use WP_Statistics\Service\Analytics\DeviceDetection\UserAgentService;
use WP_Statistics\Service\Analytics\Referrals\SourceDetector;
use WP_Statistics\Service\Consent\TrackingLevel;
use WP_Statistics\Service\Geolocation\GeolocationFactory;
use WP_Statistics\Utils\Url;

/**
 * Read-only context for the hit pipeline.
 *
 * Combines:
 * - Browser input (via HitRequest — immutable)
 * - Server-resolved data (IP, geolocation, user agent, referrer — lazy/cached)
 *
 * Does NOT store record IDs — those flow as explicit return values
 * through the pipeline orchestrated by Tracker::record().
 *
 * @since 15.1.0
 */
final class HitContext
{
    private HitRequest $request;
    private array $cache = [];

    public function __construct(HitRequest $request)
    {
        $this->request = $request;
    }

    // ── Browser input ───────────────────────────────────────────────

    public function getRequest(): HitRequest
    {
        return $this->request;
    }

    // ── Server-resolved data (lazy, cached) ─────────────────────────

    public function getIp(): string
    {
        return $this->cached('ip', fn() => Ip::getCurrent());
    }

    public function getIpHash(): string
    {
        return $this->cached('ipHash', fn() => Ip::hash());
    }

    public function getStorableIp(): ?string
    {
        return $this->cached('storableIp', function () {
            if ($this->request->getTrackingLevel() !== TrackingLevel::FULL) {
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
            $raw = $this->request->getReferrer();
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
            $raw      = $this->request->getReferrer();
            $referrer = (empty($raw) || Url::isInternal($raw)) ? '' : $raw;
            return new SourceDetector($referrer, $this->request->getResourceUri());
        });
    }

    public function getUserId(): ?int
    {
        return $this->cached('userId', function () {
            if (!Option::getValue('visitors_log')) {
                return null;
            }
            if ($this->request->getTrackingLevel() !== TrackingLevel::FULL) {
                return null;
            }
            return $this->request->getUserId();
        });
    }

    public function isIpActiveToday()
    {
        return $this->cached('isIpActiveToday', function () {
            return RecordFactory::visitor()->get([
                'hash'             => $this->getIpHash(),
                'DATE(created_at)' => DateTime::get(),
            ]);
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
