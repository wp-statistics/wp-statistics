<?php

namespace WP_Statistics\Tests\Service\Geolocation\Provider;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Error;
use WP_Statistics\Service\Geolocation\Provider\CloudflareGeolocationProvider;
use WP_Statistics\Service\Geolocation\Provider\MaxmindGeoIPProvider;
use WP_STATISTICS\Option;
use WP_UnitTestCase;

class Test_MaxmindGeoIPProvider extends WP_UnitTestCase
{
    private $provider;
    private $databasePath;
    private $originalDatabase = 'existing-invalid-database';

    public function setUp(): void
    {
        parent::setUp();

        Option::update('geoip_location_detection_method', 'maxmind');
        Option::update('geoip_license_type', 'js-deliver');
        Option::update('last_geoip_dl', 12345);

        $uploadDir          = wp_upload_dir();
        $this->databasePath = $uploadDir['basedir'] . '/' . WP_STATISTICS_UPLOADS_DIR . '/GeoLite2-City.mmdb';

        wp_mkdir_p(dirname($this->databasePath));
        file_put_contents($this->databasePath, $this->originalDatabase);

        $this->provider = new MaxmindGeoIPProvider();
    }

    public function tearDown(): void
    {
        remove_all_filters('pre_http_request');
        wp_delete_file($this->databasePath);
        wp_delete_file($this->databasePath . '.tmp');
        wp_delete_file($this->databasePath . '.backup');
        wp_delete_file(dirname($this->databasePath) . '/GeoLite2-City.mmdb.gz');

        parent::tearDown();
    }

    public function test_corrupt_download_keeps_existing_database_and_returns_recovery_steps()
    {
        add_filter('pre_http_request', function ($response, $args) {
            file_put_contents($args['filename'], gzencode('not-a-valid-mmdb'));

            return [
                'headers'  => [],
                'body'     => '',
                'response' => [
                    'code'    => 200,
                    'message' => 'OK',
                ],
                'cookies'  => [],
            ];
        }, 10, 2);

        $result = $this->provider->downloadDatabase();

        $this->assertWPError($result);
        $this->assertStringContainsString('downloaded MaxMind GeoIP database is invalid', $result->get_error_message());
        $this->assertStringContainsString('existing database was left unchanged', $result->get_error_message());
        $this->assertStringContainsString('delete the MaxMind database and retry', $result->get_error_message());
        $this->assertStringContainsString('switch to DB-IP', $result->get_error_message());
        $this->assertSame($this->originalDatabase, file_get_contents($this->databasePath));
        $this->assertFileDoesNotExist($this->databasePath . '.tmp');
        $this->assertFileDoesNotExist(dirname($this->databasePath) . '/GeoLite2-City.mmdb.gz');
        $this->assertSame(12345, Option::get('last_geoip_dl'));
    }

    public function test_malformed_archive_keeps_existing_database_and_cleans_up_files()
    {
        Option::update('geoip_license_type', 'user-license');
        Option::update('geoip_license_key', 'test-license-key');

        add_filter('pre_http_request', function ($response, $args) {
            file_put_contents($args['filename'], "\x1f\x8b\x08");

            return [
                'headers'  => [],
                'body'     => '',
                'response' => [
                    'code'    => 200,
                    'message' => 'OK',
                ],
                'cookies'  => [],
            ];
        }, 10, 2);

        $result = $this->provider->downloadDatabase();

        $this->assertWPError($result);
        $this->assertStringContainsString('Failed to extract the database file', $result->get_error_message());
        $this->assertSame($this->originalDatabase, file_get_contents($this->databasePath));
        $this->assertFileDoesNotExist($this->databasePath . '.tmp');
        $this->assertFileDoesNotExist(dirname($this->databasePath) . '/GeoLite2-City.mmdb.gz');
        $this->assertSame(12345, Option::get('last_geoip_dl'));
    }

    public function test_download_error_does_not_expose_maxmind_license_key()
    {
        $licenseKey = 'secret-license-key';

        Option::update('geoip_license_type', 'user-license');
        Option::update('geoip_license_key', $licenseKey);

        add_filter('pre_http_request', function () {
            return new WP_Error('http_request_failed', 'Connection failed.');
        });

        $result = $this->provider->downloadDatabase();

        $this->assertWPError($result);
        $this->assertStringContainsString('Connection failed.', $result->get_error_message());
        $this->assertStringNotContainsString($licenseKey, $result->get_error_message());
        $this->assertSame($this->originalDatabase, file_get_contents($this->databasePath));
        $this->assertSame(12345, Option::get('last_geoip_dl'));

        remove_all_filters('pre_http_request');
        add_filter('pre_http_request', function () {
            return [
                'headers'  => [],
                'body'     => '',
                'response' => [
                    'code'    => 403,
                    'message' => 'Forbidden',
                ],
                'cookies'  => [],
            ];
        });

        $result = $this->provider->downloadDatabase();

        $this->assertWPError($result);
        $this->assertStringContainsString('HTTP status code 403', $result->get_error_message());
        $this->assertStringNotContainsString($licenseKey, $result->get_error_message());
        $this->assertSame($this->originalDatabase, file_get_contents($this->databasePath));
        $this->assertSame(12345, Option::get('last_geoip_dl'));
    }

    public function test_cloudflare_download_is_a_successful_no_op()
    {
        $provider = new CloudflareGeolocationProvider();

        $this->assertTrue($provider->downloadDatabase());
    }
}
