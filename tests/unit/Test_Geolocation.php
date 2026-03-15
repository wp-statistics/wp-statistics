<?php

namespace WP_Statistics\Tests\Geolocation;

use WP_Statistics\Service\Geolocation\GeolocationFactory;
use WP_Statistics\Service\Geolocation\GeolocationService;
use WP_Statistics\Service\Geolocation\Provider\MaxmindGeoIPProvider;
use WP_Statistics\Service\Geolocation\Provider\DbIpProvider;
use WP_Statistics\Components\Ip;
use WP_UnitTestCase;

/**
 * Test cases for Geolocation functionality.
 *
 * Tests country and city lookup, locale handling, and edge cases.
 *
 * @group geolocation
 */
class Test_Geolocation extends WP_UnitTestCase
{
    /**
     * Check if any GeoIP database is available for testing.
     *
     * @return bool
     */
    private function isGeoIpDatabaseAvailable(): bool
    {
        $maxmind = new MaxmindGeoIPProvider();
        $dbip    = new DbIpProvider();

        return $maxmind->isDatabaseExist() || $dbip->isDatabaseExist();
    }

    /**
     * Skip test if no GeoIP database is available.
     */
    private function skipIfNoDatabaseAvailable(): void
    {
        if (!$this->isGeoIpDatabaseAvailable()) {
            $this->markTestSkipped('No GeoIP database available for testing');
        }
    }

    /**
     * Test that GeolocationFactory returns location data with country code.
     *
     * @dataProvider publicIpProvider
     */
    public function test_getLocation_returns_country_code($ip, $expectedCountryCode)
    {
        $this->skipIfNoDatabaseAvailable();

        $location = GeolocationFactory::getLocation($ip);

        $this->assertIsArray($location);
        $this->assertArrayHasKey('country_code', $location);
        $this->assertEquals($expectedCountryCode, $location['country_code']);
    }

    /**
     * Test that GeolocationFactory returns country name (not empty).
     *
     * This tests the locale fix - without proper locale, country name was NULL.
     *
     * @dataProvider publicIpWithCountryNameProvider
     */
    public function test_getLocation_returns_country_name($ip, $expectedCountryName)
    {
        $this->skipIfNoDatabaseAvailable();

        $location = GeolocationFactory::getLocation($ip);

        $this->assertIsArray($location);
        $this->assertArrayHasKey('country', $location);
        $this->assertNotEmpty($location['country'], 'Country name should not be empty');
        $this->assertEquals($expectedCountryName, $location['country']);
    }

    /**
     * Test that city name is returned when available.
     *
     * Note: City data availability depends on the GeoIP database.
     * These IPs are known to have city data in MaxMind GeoLite2-City.
     *
     * @dataProvider ipWithCityProvider
     */
    public function test_getLocation_returns_city_when_available($ip, $expectedCity)
    {
        $this->skipIfNoDatabaseAvailable();

        $location = GeolocationFactory::getLocation($ip);

        $this->assertIsArray($location);
        $this->assertArrayHasKey('city', $location);

        if ($expectedCity !== null) {
            $this->assertEquals($expectedCity, $location['city']);
        }
    }

    /**
     * Test that region/subdivision is returned when available.
     *
     * @dataProvider ipWithRegionProvider
     */
    public function test_getLocation_returns_region_when_available($ip, $expectedRegion)
    {
        $this->skipIfNoDatabaseAvailable();

        $location = GeolocationFactory::getLocation($ip);

        $this->assertIsArray($location);
        $this->assertArrayHasKey('region', $location);

        if ($expectedRegion !== null) {
            $this->assertEquals($expectedRegion, $location['region']);
        }
    }

    /**
     * Test that private IPs return default/private location.
     *
     * @dataProvider privateIpProvider
     */
    public function test_getLocation_handles_private_ips($ip)
    {
        $location = GeolocationFactory::getLocation($ip);

        $this->assertIsArray($location);
        $this->assertArrayHasKey('country_code', $location);
        // Private IPs should return 'Private' or empty country
        $this->assertTrue(
            empty($location['country_code']) || $location['country_code'] === 'Private',
            'Private IPs should return empty or "Private" country code'
        );
    }

    /**
     * Test that invalid IPs return default location.
     *
     * @dataProvider invalidIpProvider
     */
    public function test_getLocation_handles_invalid_ips($ip)
    {
        $location = GeolocationFactory::getLocation($ip);

        $this->assertIsArray($location);
        // Should return default location structure
        $this->assertArrayHasKey('country_code', $location);
        $this->assertArrayHasKey('city', $location);
    }

    /**
     * Test that location array has all expected keys.
     */
    public function test_getLocation_returns_complete_structure()
    {
        $this->skipIfNoDatabaseAvailable();

        $location = GeolocationFactory::getLocation('8.8.8.8');

        $expectedKeys = [
            'country',
            'country_code',
            'continent',
            'continent_code',
            'region',
            'region_code',
            'city',
            'latitude',
            'longitude',
            'postal_code',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $location, "Location should have key: {$key}");
        }
    }

    /**
     * Test that latitude and longitude are numeric.
     */
    public function test_getLocation_returns_valid_coordinates()
    {
        $this->skipIfNoDatabaseAvailable();

        $location = GeolocationFactory::getLocation('8.8.8.8');

        $this->assertArrayHasKey('latitude', $location);
        $this->assertArrayHasKey('longitude', $location);

        if (!empty($location['latitude'])) {
            $this->assertIsNumeric($location['latitude']);
            $this->assertGreaterThanOrEqual(-90, $location['latitude']);
            $this->assertLessThanOrEqual(90, $location['latitude']);
        }

        if (!empty($location['longitude'])) {
            $this->assertIsNumeric($location['longitude']);
            $this->assertGreaterThanOrEqual(-180, $location['longitude']);
            $this->assertLessThanOrEqual(180, $location['longitude']);
        }
    }

    /**
     * Test continent code is valid.
     */
    public function test_getLocation_returns_valid_continent_code()
    {
        $this->skipIfNoDatabaseAvailable();

        $location = GeolocationFactory::getLocation('8.8.8.8');

        $this->assertArrayHasKey('continent_code', $location);

        $validContinents = ['AF', 'AN', 'AS', 'EU', 'NA', 'OC', 'SA', ''];

        $this->assertContains(
            $location['continent_code'],
            $validContinents,
            'Continent code should be valid ISO code'
        );
    }

    /**
     * Test MaxmindGeoIPProvider locale handling.
     *
     * This specifically tests that the Reader is initialized with locale
     * so that ->name property returns English names.
     */
    public function test_maxmind_provider_returns_localized_names()
    {
        $provider = new MaxmindGeoIPProvider();

        // Skip if database doesn't exist
        if (!$provider->isDatabaseExist()) {
            $this->markTestSkipped('MaxMind database not available');
        }

        $location = $provider->fetchGeolocationData('8.8.8.8');

        // Country name should be in English (the locale fix)
        $this->assertArrayHasKey('country', $location);
        // US should return "United States" not NULL
        if ($location['country_code'] === 'US') {
            $this->assertEquals('United States', $location['country']);
        }
    }

    /**
     * Test DbIpProvider locale handling.
     */
    public function test_dbip_provider_returns_localized_names()
    {
        $provider = new DbIpProvider();

        // Skip if database doesn't exist
        if (!$provider->isDatabaseExist()) {
            $this->markTestSkipped('DB-IP database not available');
        }

        $location = $provider->fetchGeolocationData('8.8.8.8');

        $this->assertArrayHasKey('country', $location);
        // Should return localized name, not NULL
        if ($location['country_code'] === 'US') {
            $this->assertNotEmpty($location['country']);
        }
    }

    /**
     * Test GeolocationService with private IP ranges.
     */
    public function test_geolocation_service_detects_private_ip()
    {
        $provider = new MaxmindGeoIPProvider();

        // Skip if database doesn't exist
        if (!$provider->isDatabaseExist()) {
            $this->markTestSkipped('MaxMind database not available');
        }

        $service = new GeolocationService($provider);

        // Test private IP
        $location = $service->getGeolocation('192.168.1.1');

        $this->assertIsArray($location);
        // Should return default/private location
        $this->assertTrue(
            empty($location['country_code']) || $location['country_code'] === 'Private',
            'Private IP should be detected'
        );
    }

    /**
     * Test that hashed IPs are handled gracefully.
     */
    public function test_getLocation_handles_hashed_ips()
    {
        // Hashed IPs look like: a1b2c3d4e5f6...
        $hashedIp = 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0';

        $location = GeolocationFactory::getLocation($hashedIp);

        $this->assertIsArray($location);
        // Should return default location, not crash
        $this->assertArrayHasKey('country_code', $location);
    }

    /**
     * Data provider for public IPs with expected country codes.
     */
    public function publicIpProvider()
    {
        return [
            'Google DNS (US)' => ['8.8.8.8', 'US'],
            'Cloudflare DNS (US)' => ['1.1.1.1', 'AU'], // Cloudflare often shows AU
        ];
    }

    /**
     * Data provider for public IPs with expected country names.
     */
    public function publicIpWithCountryNameProvider()
    {
        return [
            'Google DNS' => ['8.8.8.8', 'United States'],
        ];
    }

    /**
     * Data provider for IPs with known city data.
     */
    public function ipWithCityProvider()
    {
        return [
            'IP with city data' => ['68.7.15.72', 'San Diego'],
            'IP with Paris' => ['62.210.16.6', 'Paris'],
            'Google DNS (no city)' => ['8.8.8.8', null], // May not have city
        ];
    }

    /**
     * Data provider for IPs with known region data.
     */
    public function ipWithRegionProvider()
    {
        return [
            'California IP' => ['68.7.15.72', 'California'],
            'Paris IP' => ['62.210.16.6', 'Paris Department'],
            'Google DNS (no region)' => ['8.8.8.8', null],
        ];
    }

    /**
     * Data provider for private IP addresses.
     */
    public function privateIpProvider()
    {
        return [
            'Private 10.x.x.x' => ['10.0.0.1'],
            'Private 172.16.x.x' => ['172.16.0.1'],
            'Private 192.168.x.x' => ['192.168.1.1'],
            'Localhost IPv4' => ['127.0.0.1'],
            'Localhost IPv6' => ['::1'],
        ];
    }

    /**
     * Data provider for invalid IP addresses.
     */
    public function invalidIpProvider()
    {
        return [
            'Empty string' => [''],
            'Invalid format' => ['not-an-ip'],
            'Partial IP' => ['192.168'],
            'Too many octets' => ['192.168.1.1.1'],
            'Hash value' => ['abc123def456'],
        ];
    }
}
