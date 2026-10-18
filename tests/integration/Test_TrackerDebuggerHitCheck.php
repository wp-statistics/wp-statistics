<?php

namespace WP_Statistics\Tests\Service\Debugger;

use WP_STATISTICS\Api\v2\Hit;
use WP_STATISTICS\Hits;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Debugger\Provider\TrackerProvider;
use WP_Statistics\Utils\Signature;
use WP_UnitTestCase;

/**
 * The Tracker Debugger's "Hit Endpoint" check must only pass when the
 * endpoint accepts a visit, not whenever it answers with JSON (issue #1172).
 */
class Test_TrackerDebuggerHitCheck extends WP_UnitTestCase
{
    /**
     * @var array
     */
    private $previousOptions = [];

    /**
     * @var array
     */
    private $previousRequest = [];

    /**
     * Requests sent to the hit endpoint during a test.
     *
     * @var array
     */
    private $hitRequests = [];

    public function set_up()
    {
        parent::set_up();

        $this->previousOptions = Option::getOptions();
        $this->previousRequest = $_REQUEST;
        $this->hitRequests     = [];
    }

    public function tear_down()
    {
        // Filters added in a test are restored by WP_UnitTestCase.
        Option::save_options($this->previousOptions);
        $_REQUEST = $this->previousRequest;

        parent::tear_down();
    }

    /**
     * Runs the debugger with a fake reply from the hit endpoint.
     *
     * @param array|\WP_Error $hitReply
     * @param bool $bypassAdBlockers
     * @return array The tracker status.
     */
    private function runDebugger($hitReply, $bypassAdBlockers = false)
    {
        Option::update('bypass_ad_blockers', $bypassAdBlockers);

        add_filter('pre_http_request', function ($preempt, $args, $url) use ($hitReply) {
            // RemoteRequest encodes the query string, so compare the decoded URL.
            $url          = urldecode($url);
            $isHitRequest = in_array($url, [admin_url('admin-ajax.php'), get_rest_url(null, 'wp-statistics/v2/hit')], true);

            if (!$isHitRequest) {
                return [
                    'headers'  => [],
                    'body'     => '',
                    'response' => ['code' => 200, 'message' => 'OK'],
                    'cookies'  => [],
                    'filename' => null,
                ];
            }

            $this->hitRequests[] = ['url' => $url, 'args' => $args];

            return $hitReply;
        }, 10, 3);

        return (new TrackerProvider())->getTrackerStatus();
    }

    /**
     * Builds a fake HTTP reply.
     *
     * @param int $code
     * @param array $body
     * @return array
     */
    private function reply($code, $body)
    {
        return [
            'headers'  => [],
            'body'     => wp_json_encode($body),
            'response' => ['code' => $code, 'message' => ''],
            'cookies'  => [],
            'filename' => null,
        ];
    }

    public function test_rest_error_reply_is_a_failure()
    {
        $status = $this->runDebugger($this->reply(400, [
            'code'    => 'rest_missing_callback_param',
            'message' => 'Missing parameter(s): page_uri',
            'data'    => ['status' => 400, 'params' => ['page_uri']],
        ]));

        $this->assertFalse($status['hitRecordingStatus']);
        $this->assertSame(400, $status['hitRecordingDetails']['responseCode']);
        $this->assertSame('Missing parameter(s): page_uri', $status['hitRecordingDetails']['message']);
    }

    public function test_invalid_signature_reply_is_a_failure()
    {
        $status = $this->runDebugger($this->reply(403, [
            'code'    => 'rest_forbidden',
            'message' => 'Invalid signature',
            'data'    => ['status' => 403],
        ]));

        $this->assertFalse($status['hitRecordingStatus']);
        $this->assertSame(403, $status['hitRecordingDetails']['responseCode']);
        $this->assertSame('Invalid signature', $status['hitRecordingDetails']['message']);
    }

    public function test_status_false_with_http_200_is_a_failure()
    {
        // The AJAX endpoint answers a rejected hit with HTTP 200 and "status": false.
        $status = $this->runDebugger($this->reply(200, [
            'status' => false,
            'data'   => 'Invalid hit/online request.',
        ]), true);

        $this->assertFalse($status['hitRecordingStatus']);
        $this->assertSame(200, $status['hitRecordingDetails']['responseCode']);
        $this->assertSame('Invalid hit/online request.', $status['hitRecordingDetails']['message']);
    }

    public function test_non_json_reply_is_a_failure()
    {
        $status = $this->runDebugger([
            'headers'  => [],
            'body'     => '<html>Access denied</html>',
            'response' => ['code' => 200, 'message' => 'OK'],
            'cookies'  => [],
            'filename' => null,
        ]);

        $this->assertFalse($status['hitRecordingStatus']);
        $this->assertNotEmpty($status['hitRecordingDetails']['message']);
    }

    public function test_connection_error_is_a_failure()
    {
        $status = $this->runDebugger(new \WP_Error('http_request_failed', 'Connection timed out.'));

        $this->assertFalse($status['hitRecordingStatus']);
        $this->assertNull($status['hitRecordingDetails']['responseCode']);
        $this->assertSame('Connection timed out.', $status['hitRecordingDetails']['message']);
    }

    public function test_accepted_hit_is_a_success()
    {
        $status = $this->runDebugger($this->reply(200, ['status' => true, 'test' => true]));

        $this->assertTrue($status['hitRecordingStatus']);
        $this->assertSame(200, $status['hitRecordingDetails']['responseCode']);
        $this->assertSame('', $status['hitRecordingDetails']['message']);
    }

    public function test_cloudflare_challenge_is_still_not_a_failure()
    {
        $headers = new \WpOrg\Requests\Utility\CaseInsensitiveDictionary([
            'server'       => 'cloudflare',
            'cf-mitigated' => 'challenge',
        ]);

        $status = $this->runDebugger([
            'headers'  => $headers,
            'body'     => '<html>Just a moment...</html>',
            'response' => ['code' => 403, 'message' => 'Forbidden'],
            'cookies'  => [],
            'filename' => null,
        ]);

        $this->assertTrue($status['hitRecordingStatus']);
    }

    public function test_rest_check_sends_a_signed_test_hit_like_the_tracker()
    {
        add_filter('wp_statistics_request_signature_enabled', '__return_true');

        $this->runDebugger($this->reply(200, ['status' => true]));

        $this->assertCount(1, $this->hitRequests);

        $request = $this->hitRequests[0];
        $body    = $request['args']['body'];

        $this->assertSame(get_rest_url(null, 'wp-statistics/v2/hit'), $request['url']);
        $this->assertSame('POST', $request['args']['method']);
        $this->assertSame(1, $body[Hits::$rest_hits_key]);
        $this->assertSame(1, $body[Hits::$debugger_test_key]);
        $this->assertSame('/', base64_decode($body['page_uri']));
        $this->assertSame(Signature::generate([$body['source_type'], (int)$body['source_id']]), $body['signature']);
    }

    public function test_ajax_check_sends_the_ajax_action()
    {
        $this->runDebugger($this->reply(200, ['status' => true]), true);

        $this->assertCount(1, $this->hitRequests);
        $this->assertSame(admin_url('admin-ajax.php'), $this->hitRequests[0]['url']);
        $this->assertSame('wp_statistics_hit_record', $this->hitRequests[0]['args']['body']['action']);
        $this->assertSame(1, $this->hitRequests[0]['args']['body'][Hits::$debugger_test_key]);
    }

    /**
     * Sets the request parameters of a valid hit.
     *
     * @param bool $isDebuggerTest
     */
    private function setHitRequest($isDebuggerTest)
    {
        $_REQUEST = [
            Hits::$rest_hits_key => 1,
            'source_type'        => 'home',
            'source_id'          => 0,
            'search_query'       => '',
            'page_uri'           => base64_encode('/'),
            'referred'           => '',
        ];

        if ($isDebuggerTest) {
            $_REQUEST[Hits::$debugger_test_key] = 1;
        }
    }

    public function test_debugger_test_hit_is_accepted_but_not_recorded()
    {
        $recordCalls = 0;
        $spy         = function ($list) use (&$recordCalls) {
            $recordCalls++;
            return $list;
        };
        add_filter('wp_statistics_exclusion_list', $spy);

        $this->setHitRequest(true);
        $response = (new Hit())->hit_callback();

        remove_filter('wp_statistics_exclusion_list', $spy);

        $this->assertSame(200, $response->get_status());
        $this->assertTrue($response->get_data()['status']);
        $this->assertTrue($response->get_data()['test']);
        $this->assertSame(0, $recordCalls, 'A debugger test hit must not be recorded.');
    }

    public function test_real_hit_is_still_recorded()
    {
        $recordCalls = 0;
        $spy         = function ($list) use (&$recordCalls) {
            $recordCalls++;
            return $list;
        };
        add_filter('wp_statistics_exclusion_list', $spy);

        $this->setHitRequest(false);
        $response = (new Hit())->hit_callback();

        remove_filter('wp_statistics_exclusion_list', $spy);

        $this->assertArrayNotHasKey('test', $response->get_data());
        $this->assertSame(1, $recordCalls, 'A real hit must go through recording.');
    }
}
