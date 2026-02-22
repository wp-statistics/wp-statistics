<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Abstracts\BaseEntity;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Utils\Query;
use WP_Statistics\Utils\Request;

/**
 * Entity for detecting and recording visitor's locale information.
 *
 * This includes browser language and timezone based on geolocation.
 *
 * @since 15.0.0
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

        $region = $this->profile->getRegionCode();
        $record = RecordFactory::language()->get(['code' => $language, 'region' => $region]);

        if (!empty($record) && isset($record->ID)) {
            $this->profile->setLanguageId((int)$record->ID);
            return $this;
        }

        $languageId = (int)RecordFactory::language()->insert([
            'code'   => $language,
            'name'   => $fullName,
            'region' => $region,
        ]);

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
        $record      = RecordFactory::timezone()->get(['name' => $tzNameFinal]);

        if (!empty($record) && isset($record->ID)) {
            // Update offset/DST if changed (e.g., DST transition)
            if ($record->offset !== $offset || (int)$record->is_dst !== ($isDst ? 1 : 0)) {
                Query::update('timezones')
                    ->set([
                        'offset' => $offset,
                        'is_dst' => $isDst ? 1 : 0,
                    ])
                    ->where('ID', '=', $record->ID)
                    ->execute();
            }

            $this->profile->setTimezoneId((int)$record->ID);
            return $this;
        }

        $timezoneId = (int)RecordFactory::timezone()->insert([
            'name'   => $tzNameFinal,
            'offset' => $offset,
            'is_dst' => $isDst ? 1 : 0,
        ]);

        $this->profile->setTimezoneId($timezoneId);
        return $this;
    }

}
