<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Abstracts\BaseEntity;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Utils\Query;


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
     * Record all locale information and return their IDs.
     *
     * @return array{language_id: int, timezone_id: int}
     */
    public function record(): array
    {
        return [
            'language_id' => $this->isActive('languages') ? $this->recordLanguage() : 0,
            'timezone_id' => $this->isActive('timezones') ? $this->recordTimezone() : 0,
        ];
    }

    /**
     * Detect and record visitor's language based on user agent or browser headers.
     *
     * @return int The language ID, or 0 if language code is empty.
     */
    private function recordLanguage(): int
    {
        $language = $this->context->getRequest()->getLanguageCode();
        $fullName = $this->context->getRequest()->getLanguageName();

        if (empty($language) || !is_string($language)) {
            return 0;
        }

        $region = $this->context->getRegionCode();
        $record = RecordFactory::language()->get(['code' => $language, 'region' => $region]);

        if (!empty($record) && isset($record->ID)) {
            return (int)$record->ID;
        }

        return (int)RecordFactory::language()->insert([
            'code'   => $language,
            'name'   => $fullName,
            'region' => $region,
        ]);
    }

    /**
     * Detect and record visitor's timezone.
     *
     * Tries client-sent IANA timezone first; if missing or invalid, falls back
     * to UTC+0 as a standard reference point.
     *
     * @return int The timezone ID.
     */
    private function recordTimezone(): int
    {
        $tzName = $this->context->getRequest()->getTimezone();

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

            return (int)$record->ID;
        }

        return (int)RecordFactory::timezone()->insert([
            'name'   => $tzNameFinal,
            'offset' => $offset,
            'is_dst' => $isDst ? 1 : 0,
        ]);
    }

}
