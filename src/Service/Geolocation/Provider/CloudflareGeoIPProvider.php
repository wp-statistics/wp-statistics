<?php

namespace WP_Statistics\Service\Geolocation\Provider;

use WP_STATISTICS\Country;
use WP_Statistics\Service\Geolocation\AbstractGeoIPProvider;

class CloudflareGeoIPProvider extends AbstractGeoIPProvider
{
    /**
     * Fetch geolocation data.
     *
     * @param string $ipAddress
     * @return array
     */
    public function fetchGeolocationData(string $ipAddress)
    {
        $rawData       = $this->getCloudflareHeaders();
        $sanitizedData = $this->sanitizeHeaderData($rawData);

        return [
            'country' => Country::getName($sanitizedData['country_code']),
            'country_code' => $sanitizedData['country_code'],
            'continent' => $this->getContinentName($sanitizedData['continent']),
            'region' => $sanitizedData['region'],
            'city' => $sanitizedData['city'],
            'latitude' => $sanitizedData['latitude'],
            'longitude' => $sanitizedData['longitude'],
            'postal_code' => $sanitizedData['postal_code']
        ];
    }

   /**
     * Get all Cloudflare headers using filter_input.
     *
     * @return array Raw header data
     */
    private function getCloudflareHeaders(): array
    {
        return [
            'country_code' => filter_input(INPUT_SERVER, 'HTTP_CF_IPCOUNTRY', FILTER_DEFAULT),
            'continent' => filter_input(INPUT_SERVER, 'HTTP_CF_IPCONTINENT', FILTER_DEFAULT),
            'region' => filter_input(INPUT_SERVER, 'HTTP_CF_REGION', FILTER_DEFAULT),
            'city' => filter_input(INPUT_SERVER, 'HTTP_CF_IPCITY', FILTER_DEFAULT),
            'latitude' => filter_input(INPUT_SERVER, 'HTTP_CF_IPLATITUDE', FILTER_VALIDATE_FLOAT),
            'longitude' => filter_input(INPUT_SERVER, 'HTTP_CF_IPLONGITUDE', FILTER_VALIDATE_FLOAT),
            'postal_code' => filter_input(INPUT_SERVER, 'HTTP_CF_POSTAL_CODE', FILTER_DEFAULT)
        ];
    }

    /**
     * Sanitize header string data.
     *
     * @param array $data Raw header data
     * @return array Sanitized header data
     */
    private function sanitizeHeaderData(array $data): array
    {
        $stringFields = ['country_code', 'continent', 'region', 'city', 'postal_code'];
        
        foreach ($stringFields as $field) {
            $data[$field] = $data[$field] ? 
                htmlspecialchars($data[$field], ENT_QUOTES, 'UTF-8') : 
                null;
        }

        return $data;
    }

    /**
     * Get continent full name from code.
     *
     * @param string|null $code Continent code.
     * 
     * @return string|null Continent name or null.
     */
    protected function getContinentName( $code ) {
        if ( empty( $code ) ) {
            return null;
        }
    
        $continents = [
            'AF' => 'Africa',
            'AN' => 'Antarctica',
            'AS' => 'Asia',
            'EU' => 'Europe',
            'NA' => 'North America',
            'OC' => 'Oceania',
            'SA' => 'South America'
        ];

        return $continents[$code] ?? null;
    }

    public function getDownloadUrl(){
        return '';
    }

    public function downloadDatabase()
    {
        return [];
    }

    public function getDatabaseType()
    {
        return '';
    }

    public function validateDatabaseFile() {}
}
