<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Abstracts\BaseEntity;
use WP_Statistics\Records\DeviceTypeRecord;
use WP_Statistics\Records\DeviceOsRecord;
use WP_Statistics\Records\DeviceBrowserRecord;
use WP_Statistics\Records\DeviceBrowserVersionRecord;
use WP_Statistics\Records\ResolutionRecord;
use WP_Statistics\Utils\Request;

/**
 * Entity for detecting and recording visitor device information.
 *
 * This includes device type (desktop, mobile, etc.), operating system,
 * browser name and version, and screen resolution.
 */
class Device extends BaseEntity
{
    /**
     * Detect and record visitor device type (e.g., desktop, mobile, tablet).
     *
     * @return $this
     */
    public function recordType()
    {
        if (! $this->isActive('device_types')) {
            return $this;
        }

        if (!$this->userAgent) {
            return $this;
        }

        $deviceType = $this->userAgent->getDevice();
        $cacheKey   = 'device_type_' . md5($deviceType);

        $id = $this->getCachedData($cacheKey, function () use ($deviceType) {
            $model  = new DeviceTypeRecord();
            $record = $model->get(['name' => $deviceType]);

            return !empty($record) && isset($record->ID)
                ? (int)$record->ID
                : $model->insert(['name' => $deviceType]);
        });

        $this->profile->setDeviceTypeId($id);
        return $this;
    }

    /**
     * Detect and record visitor operating system (e.g., Windows, iOS, Android).
     *
     * @return $this
     */
    public function recordOs()
    {
        if (! $this->isActive('device_oss')) {
            return $this;
        }

        if (!$this->userAgent) {
            return $this;
        }

        $os       = $this->userAgent->getPlatform();
        $cacheKey = 'device_os_' . md5($os);

        $id = $this->getCachedData($cacheKey, function () use ($os) {
            $model  = new DeviceOsRecord();
            $record = $model->get(['name' => $os]);

            return !empty($record) && isset($record->ID)
                ? (int)$record->ID
                : $model->insert(['name' => $os]);
        });

        $this->profile->setDeviceOsId($id);
        return $this;
    }

    /**
     * Detect and record visitor browser name (e.g., Chrome, Firefox, Safari).
     *
     * @return $this
     */
    public function recordBrowser()
    {
        if (! $this->isActive('device_browsers')) {
            return $this;
        }

        if (!$this->userAgent) {
            return $this;
        }

        $browser  = $this->userAgent->getBrowser();
        $cacheKey = 'device_browser_' . md5($browser);

        $id = $this->getCachedData($cacheKey, function () use ($browser) {
            $model  = new DeviceBrowserRecord();
            $record = $model->get(['name' => $browser]);

            return !empty($record) && isset($record->ID)
                ? (int)$record->ID
                : $model->insert(['name' => $browser]);
        });

        $this->profile->setDeviceBrowserId($id);
        return $this;
    }

    /**
     * Detect and record visitor browser version (e.g., 117.0.0).
     *
     * @return $this
     */
    public function recordBrowserVersion()
    {
        if (! $this->isActive('device_browser_versions')) {
            return $this;
        }

        if (!$this->userAgent) {
            return $this;
        }

        $browserId = $this->profile->getDeviceBrowserId();

        if (!$browserId) {
            $this->recordBrowser();
            $browserId = $this->profile->getDeviceBrowserId();
        }

        if (!$browserId) {
            return $this;
        }

        $version = $this->userAgent->getVersion();

        $cacheKey = 'device_browser_version_' . $browserId . '_' . md5($version);

        $id = $this->getCachedData($cacheKey, function () use ($browserId, $version) {
            $model  = new DeviceBrowserVersionRecord();
            $record = $model->get([
                'browser_id' => $browserId,
                'version'    => $version,
            ]);

            return !empty($record) && isset($record->ID)
                ? (int)$record->ID
                : $model->insert([
                    'browser_id' => $browserId,
                    'version'    => $version,
                ]);
        });

        $this->profile->setDeviceBrowserVersionId($id);
        return $this;
    }

    /**
     * Record visitor screen resolution.
     *
     * @return $this
     */
    public function recordResolution()
    {
        if (! $this->isActive('device_resolutions')) {
            return $this;
        }

        $width  = (int) Request::get('screenWidth', 0);
        $height = (int) Request::get('screenHeight', 0);

        $cacheKey = 'resolution_' . $width . 'x' . $height;

        $id = $this->getCachedData($cacheKey, function () use ($width, $height) {
            $model  = new ResolutionRecord();
            $record = $model->get([
                'width'  => $width,
                'height' => $height,
            ]);

            return !empty($record) && isset($record->ID)
                ? (int)$record->ID
                : $model->insert([
                    'width'  => $width,
                    'height' => $height,
                ]);
        });

        $this->profile->setResolutionId($id);
        return $this;
    }
}