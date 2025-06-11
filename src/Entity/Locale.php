<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Abstracts\BaseEntity;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Utils\Request;

/**
 * Entity for detecting and recording visitor's locale information.
 *
 * This includes browser language and timezone based on geolocation.
 */
class Locale extends BaseEntity
{
    /**
     * Detect and record visitor's language based on user agent or browser headers.
     *
     * @return $this
     */
    public function recordLanguage()
    {
        if (!$this->isActive('languages')) {
            return $this;
        }

        $language = Request::get('language', '');
        $fullName = Request::get('languageFullName', '');

        if (empty($language) || !is_string($language)) {
            return $this;
        }

        $region = $this->profile->getRegion();

        $cacheKey = 'language_' . md5("{$language}-{$region}");

        $languageId = $this->getCachedData($cacheKey, function () use ($language, $fullName, $region) {
            $record = RecordFactory::language()->get(['code' => $language, 'region' => $region]);

            if (!empty($record) && isset($record->ID)) {
                return (int)$record->ID;
            }

            return (int)RecordFactory::language()->insert([
                'code'   => $language,
                'name'   => $fullName,
                'region' => $region,
            ]);
        });

        $this->profile->setLanguageId($languageId);
        return $this;
    }

    /**
     * Detect and record visitor's timezone.
     *
     * Tries client‐sent IANA timezone first; if missing or invalid, falls back
     * to UTC+0 as a standard reference point.
     *
     * @return $this
     */
    public function recordTimezone()
    {
        if (!$this->isActive('timezones')) {
            return $this;
        }

        $tzName = Request::get('timezone', '');

        if (empty($tzName) || !is_string($tzName)) {
            $tz = new \DateTimeZone('UTC');
        } else {
            try {
                $tz = new \DateTimeZone(trim($tzName));
            } catch (\Exception $e) {
                $tz = new \DateTimeZone('UTC');
            }
        }

        // Compute offset & DST.
        $dt            = new \DateTime('now', $tz);
        $offsetSeconds = $tz->getOffset($dt);
        $hours         = intdiv($offsetSeconds, 3600);
        $minutes       = abs(($offsetSeconds % 3600) / 60);
        $offset        = sprintf('%+03d:%02d', $hours, $minutes);
        $isDst         = (bool)$dt->format('I');

        $tzNameFinal = $tz->getName();
        $cacheKey    = 'timezone_' . md5("{$tzNameFinal}|{$offset}|{$isDst}");

        $timezoneId = $this->getCachedData($cacheKey, function () use ($tzNameFinal, $offset, $isDst) {
            $record = RecordFactory::timezone()->get(['name' => $tzNameFinal]);

            if (!empty($record) && isset($record->ID)) {
                return (int)$record->ID;
            }

            return (int)RecordFactory::timezone()->insert([
                'name'   => $tzNameFinal,
                'offset' => $offset,
                'is_dst' => $isDst ? 1 : 0,
            ]);
        });

        $this->profile->setTimezoneId($timezoneId);
        return $this;
    }

}
