<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Abstracts\BaseEntity;
use WP_Statistics\Records\RecordFactory;


/**
 * Entity for detecting and recording visitor device information.
 *
 * This includes device type (desktop, mobile, etc.), operating system,
 * browser name and version, and screen resolution.
 *
 * @since 15.0.0
 */
class Device extends BaseEntity
{
    /**
     * Record all device-related information and return their IDs.
     *
     * @return array{type_id: int, os_id: int, browser_id: int, browser_version_id: int, resolution_id: int}
     */
    public function record(): array
    {
        $browserId = $this->isActive('device_browsers') ? $this->recordBrowser() : 0;

        return [
            'type_id'            => $this->isActive('device_types') ? $this->recordType() : 0,
            'os_id'              => $this->isActive('device_oss') ? $this->recordOs() : 0,
            'browser_id'         => $browserId,
            'browser_version_id' => $this->isActive('device_browser_versions') ? $this->recordBrowserVersion($browserId) : 0,
            'resolution_id'      => $this->isActive('device_resolutions') ? $this->recordResolution() : 0,
        ];
    }

    /**
     * Detect and record visitor device type (e.g., desktop, mobile, tablet).
     *
     * @return int The device type ID, or 0 if user agent is unavailable.
     */
    private function recordType(): int
    {
        if (!$this->userAgent) {
            return 0;
        }

        $deviceType = ucwords($this->userAgent->getDevice());
        $record     = RecordFactory::deviceType()->get(['name' => $deviceType]);

        return !empty($record) && isset($record->ID)
            ? (int)$record->ID
            : (int)RecordFactory::deviceType()->insert(['name' => $deviceType]);
    }

    /**
     * Detect and record visitor operating system (e.g., Windows, iOS, Android).
     *
     * @return int The OS ID, or 0 if user agent is unavailable.
     */
    private function recordOs(): int
    {
        if (!$this->userAgent) {
            return 0;
        }

        $os     = $this->userAgent->getPlatform();
        $record = RecordFactory::deviceOs()->get(['name' => $os]);

        return !empty($record) && isset($record->ID)
            ? (int)$record->ID
            : (int)RecordFactory::deviceOs()->insert(['name' => $os]);
    }

    /**
     * Detect and record visitor browser name (e.g., Chrome, Firefox, Safari).
     *
     * @return int The browser ID, or 0 if user agent is unavailable.
     */
    private function recordBrowser(): int
    {
        if (!$this->userAgent) {
            return 0;
        }

        $browser = $this->userAgent->getBrowser();
        $record  = RecordFactory::deviceBrowser()->get(['name' => $browser]);

        return !empty($record) && isset($record->ID)
            ? (int)$record->ID
            : (int)RecordFactory::deviceBrowser()->insert(['name' => $browser]);
    }

    /**
     * Detect and record visitor browser version (e.g., 117.0.0).
     *
     * @param int $browserId The browser ID to associate the version with.
     * @return int The browser version ID, or 0 if user agent is unavailable or browser ID is missing.
     */
    private function recordBrowserVersion(int $browserId): int
    {
        if (!$this->userAgent || !$browserId) {
            return 0;
        }

        $version = $this->userAgent->getVersion();
        $record  = RecordFactory::deviceBrowserVersion()->get([
            'browser_id' => $browserId,
            'version'    => $version,
        ]);

        return !empty($record) && isset($record->ID)
            ? (int)$record->ID
            : (int)RecordFactory::deviceBrowserVersion()->insert([
                'browser_id' => $browserId,
                'version'    => $version,
            ]);
    }

    /**
     * Detect and record visitor screen resolution (e.g., 1920x1080).
     *
     * For privacy protection, the height is rounded down to the nearest ten
     * and the last digit is set to zero (e.g., 1920x1087 -> 1920x1080).
     *
     * @return int The resolution ID.
     */
    private function recordResolution(): int
    {
        $width  = (int)$this->visitor->getRequest()->getScreenWidth();
        $height = (int)$this->visitor->getRequest()->getScreenHeight();

        if ($height > 0) {
            $height = (int)(floor($height / 10) * 10);
        }

        $record = RecordFactory::resolution()->get([
            'width'  => $width,
            'height' => $height,
        ]);

        return !empty($record) && isset($record->ID)
            ? (int)$record->ID
            : (int)RecordFactory::resolution()->insert([
                'width'  => $width,
                'height' => $height,
            ]);
    }
}
