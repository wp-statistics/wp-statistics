<?php

use WP_Statistics\Service\Admin\LicenseManagement\ApiCommunicator;

/**
 * Covers how a refused licence is remembered, which is what issue #1123 is about:
 * a refused install used to ask the licence API every five minutes, per add-on, per
 * subsite, forever.
 */
class Test_ApiCommunicator extends WP_UnitTestCase
{
    private $licenseKey = '12345678901234567890123456789012';
    private $pluginSlug = 'wp-statistics-advanced-reporting';

    public function tearDown(): void
    {
        delete_transient($this->getProductInfoCacheKey());
        $this->deleteEntry($this->getRefusalKey());
        $this->deleteEntry($this->getRefusalKey() . '_attempts');
        $this->deleteEntry($this->getRefusalKey() . '_backoff');
        $this->deleteEntry($this->getRefusalGenerationKey());
        remove_all_filters('pre_http_request');
        remove_all_filters('home_url');

        parent::tearDown();
    }

    public function test_an_authoritative_refusal_is_remembered_for_twelve_hours()
    {
        $requests     = $this->answerWith(400);
        $communicator = new ApiCommunicator();

        $this->assertDownloadFails($communicator);
        $this->assertSame(1, $requests->count);
        $this->assertRefusalLasts(12 * HOUR_IN_SECONDS);

        // The remembered refusal answers on its own; no second request goes out.
        $this->assertNull($communicator->getDownloadUrl($this->licenseKey, $this->pluginSlug));
        $this->assertSame(1, $requests->count);
    }

    /**
     * @dataProvider undecidedResponses
     */
    public function test_a_failure_that_decides_nothing_keeps_the_short_wait($response)
    {
        add_filter('pre_http_request', function () use ($response) {
            return is_int($response)
                ? ['response' => ['code' => $response], 'body' => wp_json_encode(['status' => 'error'])]
                : new WP_Error('http_request_failed', 'Connection timed out');
        });

        $this->assertDownloadFails(new ApiCommunicator());
        $this->assertRefusalLasts(5 * MINUTE_IN_SECONDS);
    }

    public function undecidedResponses()
    {
        return [
            'transport failure' => ['wp_error'],
            'forbidden by an edge rule' => [403],
            'route not found' => [404],
            'request timeout' => [408],
            'rate limited' => [429],
            'server error' => [500],
        ];
    }

    public function test_repeated_refusals_lengthen_the_wait()
    {
        $requests = $this->answerWith(400);
        $communicator = new ApiCommunicator();

        foreach ([12 * HOUR_IN_SECONDS, DAY_IN_SECONDS, 2 * DAY_IN_SECONDS, 2 * DAY_IN_SECONDS] as $expected) {
            $this->assertDownloadFails($communicator);
            $this->assertRefusalLasts($expected);

            // Let the refusal expire while the backoff state survives, as it does in life.
            $this->deleteEntry($this->getRefusalKey());
        }

        $this->assertSame(4, $requests->count);
    }

    public function test_repeated_outages_lengthen_the_wait()
    {
        add_filter('pre_http_request', function () {
            return new WP_Error('http_request_failed', 'Connection timed out');
        });

        $communicator = new ApiCommunicator();

        foreach ([5 * MINUTE_IN_SECONDS, 10 * MINUTE_IN_SECONDS, 20 * MINUTE_IN_SECONDS] as $expected) {
            $this->assertDownloadFails($communicator);
            $this->assertRefusalLasts($expected);

            $this->deleteEntry($this->getRefusalKey());
        }
    }

    public function test_an_answer_forgets_the_outage_history()
    {
        add_filter('pre_http_request', function () {
            return new WP_Error('http_request_failed', 'Connection timed out');
        });

        $communicator = new ApiCommunicator();

        // Three outages in a row would put the next wait at twenty minutes.
        for ($i = 0; $i < 3; $i++) {
            $this->assertDownloadFails($communicator);
            $this->deleteEntry($this->getRefusalKey());
        }

        // An answer, even a refusal, proves the server is reachable.
        remove_all_filters('pre_http_request');
        $this->answerWith(400);
        $this->assertDownloadFails($communicator);
        $this->deleteEntry($this->getRefusalKey());

        remove_all_filters('pre_http_request');
        add_filter('pre_http_request', function () {
            return new WP_Error('http_request_failed', 'Connection timed out');
        });

        $this->assertDownloadFails($communicator);
        $this->assertRefusalLasts(5 * MINUTE_IN_SECONDS);
    }

    public function test_a_success_clears_the_refusal_and_its_backoff()
    {
        $this->answerWith(400);
        $communicator = new ApiCommunicator();
        $this->assertDownloadFails($communicator);

        $this->deleteEntry($this->getRefusalKey());
        remove_all_filters('pre_http_request');
        add_filter('pre_http_request', function () {
            return [
                'response' => ['code' => 200],
                'body'     => wp_json_encode(['download_url' => 'https://example.com/add-on.zip']),
            ];
        });

        $this->assertIsObject($communicator->getDownloadUrl($this->licenseKey, $this->pluginSlug));
        $this->assertFalse($this->readEntry($this->getRefusalKey()));
        $this->assertFalse($this->readEntry($this->getRefusalKey() . '_backoff'));
        $this->assertFalse($this->readEntry($this->getRefusalKey() . '_attempts'));
    }

    public function test_validating_a_licence_clears_refusals_recorded_elsewhere()
    {
        $this->answerWith(400);
        $this->assertDownloadFails(new ApiCommunicator());

        // Refusals recorded by other subsites of the same network. There is no shared
        // list to find them by — a list is a read-modify-write that concurrent refusals
        // can race, losing a key — so validation replaces a generation token instead,
        // and every refusal carrying the old token is void. A customer renewing on one
        // subsite must free all of them, however many were written at once.
        $generation    = (string) $this->readEntry($this->getRefusalGenerationKey());
        $otherBlogKeys = [];
        foreach ($this->otherBlogIds() as $blogId) {
            $otherBlogKeys[$blogId] = $this->getRefusalKey($blogId);
            $this->writeEntry($otherBlogKeys[$blogId], ['code' => 400, 'generation' => $generation], 12 * HOUR_IN_SECONDS);
        }

        (new ApiCommunicator())->clearProductInfoCache($this->licenseKey);

        $this->assertFalse($this->readEntry($this->getRefusalKey()));

        foreach ($otherBlogKeys as $blogId => $otherBlogKey) {
            $this->assertRefusalIsVoid($otherBlogKey, $blogId);
            $this->deleteEntry($otherBlogKey);
        }
    }

    /**
     * The void is decided by the token alone — no clock, so two web nodes whose clocks
     * disagree cannot disagree about it either. And the stale row is deleted on sight:
     * were the token later evicted from a persistent object cache, an undeleted row
     * would come back to life.
     */
    public function test_a_refusal_from_before_validation_is_void_and_removed()
    {
        (new ApiCommunicator())->clearProductInfoCache($this->licenseKey);

        $this->writeEntry($this->getRefusalKey(), ['code' => 400, 'generation' => 'before-renewal'], 12 * HOUR_IN_SECONDS);
        $requests = $this->answerWith(200, ['download_url' => 'https://example.com/add-on.zip']);

        $this->assertIsObject((new ApiCommunicator())->getDownloadUrl($this->licenseKey, $this->pluginSlug));
        $this->assertSame(1, $requests->count);
        $this->assertFalse($this->readEntry($this->getRefusalKey()));
    }

    public function test_a_refusal_after_validation_still_counts()
    {
        (new ApiCommunicator())->clearProductInfoCache($this->licenseKey);

        $requests = $this->answerWith(400);
        $communicator = new ApiCommunicator();

        $this->assertDownloadFails($communicator);
        $this->assertNull($communicator->getDownloadUrl($this->licenseKey, $this->pluginSlug));
        $this->assertSame(1, $requests->count);
    }

    /**
     * A second language must not produce a second request. PR #451 removed home_url()
     * from the licence cache key because a multilingual site multiplied one install into
     * one request per language; the refusal must not put it back.
     */
    public function test_a_second_language_does_not_ask_again()
    {
        $requests     = $this->answerWith(400);
        $communicator = new ApiCommunicator();

        $this->assertDownloadFails($communicator);
        $this->assertSame(1, $requests->count);

        add_filter('home_url', function () {
            return 'https://example.org/fr';
        });

        $this->assertNull($communicator->getDownloadUrl($this->licenseKey, $this->pluginSlug));
        $this->assertSame(1, $requests->count);
    }

    /**
     * The previous release wrote its marker into the success cache, which RemoteRequest
     * reads and returns. An upgraded site must not be served that marker as product info.
     */
    public function test_the_previous_releases_marker_is_discarded()
    {
        set_transient($this->getProductInfoCacheKey(), (object)['_negative_cache' => true], 5 * MINUTE_IN_SECONDS);

        $requests = $this->answerWith(200, ['download_url' => 'https://example.com/add-on.zip']);

        $productInfo = (new ApiCommunicator())->getDownloadUrl($this->licenseKey, $this->pluginSlug);

        $this->assertSame(1, $requests->count);
        $this->assertSame('https://example.com/add-on.zip', $productInfo->download_url);
    }

    private function answerWith($code, $body = ['status' => 'suspended'])
    {
        $requests = (object)['count' => 0];

        add_filter('pre_http_request', function () use (&$requests, $code, $body) {
            $requests->count++;

            return [
                'response' => ['code' => $code],
                'body'     => wp_json_encode($body),
            ];
        });

        return $requests;
    }

    private function assertDownloadFails($communicator)
    {
        try {
            $communicator->getDownloadUrl($this->licenseKey, $this->pluginSlug);
        } catch (Exception $e) {
            // PHPUnit converts notices and deprecations into exceptions too; a bug in the
            // code under test must not pass as the refusal it was meant to produce.
            $this->assertNotInstanceOf(\PHPUnit\Framework\Exception::class, $e);
            $this->assertNotEmpty($e->getMessage());

            return;
        }

        // Outside the try: fail() throws an Exception subclass, which the catch above
        // would otherwise swallow and pass.
        $this->fail('Expected the failed request to throw an exception.');
    }

    /**
     * Two other subsites of the network. Real ones on multisite, so that they can be
     * switched to; on a single site the IDs only shape the key.
     *
     * @return int[]
     */
    private function otherBlogIds()
    {
        if (!is_multisite()) {
            return [98, 99];
        }

        return [self::factory()->blog->create(), self::factory()->blog->create()];
    }

    /**
     * The row is still there — nothing deletes it — but the blog it belongs to no longer
     * honours it: its next request goes to the server.
     */
    private function assertRefusalIsVoid($refusalKey, $blogId)
    {
        $refusal = $this->readEntry($refusalKey);

        $this->assertIsArray($refusal);
        $this->assertNotSame($this->readEntry($this->getRefusalGenerationKey()), $refusal['generation']);

        if (!is_multisite()) {
            return;
        }

        remove_all_filters('pre_http_request');
        $requests = $this->answerWith(200, ['download_url' => 'https://example.com/add-on.zip']);

        switch_to_blog($blogId);
        try {
            (new ApiCommunicator())->getDownloadUrl($this->licenseKey, $this->pluginSlug);
            delete_transient($this->getProductInfoCacheKey());
        } finally {
            restore_current_blog();
        }

        $this->assertSame(1, $requests->count);
    }

    private function assertRefusalLasts($expectedDuration)
    {
        $this->assertIsArray($this->readEntry($this->getRefusalKey()));

        // There is no public API for a transient's remaining life, so the timeout option
        // is read directly. A persistent object cache keeps transients out of the options
        // table entirely, so the length cannot be asserted there.
        if (wp_using_ext_object_cache()) {
            return;
        }

        $timeout = $this->getRefusalTimeout();

        $this->assertGreaterThanOrEqual(time() + $expectedDuration - MINUTE_IN_SECONDS, $timeout);
        $this->assertLessThanOrEqual(time() + $expectedDuration + MINUTE_IN_SECONDS, $timeout);
    }

    private function getRefusalTimeout()
    {
        $optionName = (is_multisite() ? '_site_transient_timeout_' : '_transient_timeout_') . $this->getRefusalKey();

        return (int) (is_multisite() ? get_site_option($optionName) : get_option($optionName));
    }

    private function getProductInfoCacheKey()
    {
        return 'wp_statistics_product_info_' . md5($this->pluginSlug . '_' . $this->licenseKey . '_' . get_current_blog_id());
    }

    private function getRefusalKey($blogId = null)
    {
        $blogId = $blogId === null ? get_current_blog_id() : $blogId;

        return 'wp_statistics_license_refusal_' . md5($this->pluginSlug . '_' . $this->licenseKey . '_' . $blogId);
    }

    private function getRefusalGenerationKey()
    {
        return 'wp_statistics_license_refusal_generation_' . md5($this->licenseKey);
    }

    private function readEntry($key)
    {
        return is_multisite() ? get_site_transient($key) : get_transient($key);
    }

    private function writeEntry($key, $value, $duration)
    {
        is_multisite() ? set_site_transient($key, $value, $duration) : set_transient($key, $value, $duration);
    }

    private function deleteEntry($key)
    {
        is_multisite() ? delete_site_transient($key) : delete_transient($key);
    }
}
