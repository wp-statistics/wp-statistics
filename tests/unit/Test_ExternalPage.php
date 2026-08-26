<?php

namespace WP_Statistics\Tests\Api\v2;

use WP_STATISTICS\Api\v2\ExternalPage;
use WP_UnitTestCase;

class Test_ExternalPage extends WP_UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \WP_Statistics\Core\CoreFactory::activator(false);
    }

    public function test_accepts_requests_from_the_wordpress_origin()
    {
        $request = new \WP_REST_Request('POST', '/wp-statistics/v2/external-page');
        $request->set_header('Origin', home_url());

        $this->assertTrue((new ExternalPage())->check_origin($request));
    }

    public function test_accepts_origin_without_an_explicit_default_port()
    {
        $origins = array(
            array('http://example.org:80', 'http://example.org'),
            array('https://example.org:443', 'https://example.org'),
        );

        foreach ($origins as $originsPair) {
            $homeUrlFilter = function () use ($originsPair) {
                return $originsPair[0];
            };
            add_filter('home_url', $homeUrlFilter);

            $request = new \WP_REST_Request('POST', '/wp-statistics/v2/external-page');
            $request->set_header('Origin', $originsPair[1]);
            $result = (new ExternalPage())->check_origin($request);

            remove_filter('home_url', $homeUrlFilter);

            $this->assertTrue($result, $originsPair[0]);
        }
    }

    public function test_rejects_requests_without_a_matching_origin()
    {
        $tracker = new ExternalPage();

        $missingOrigin = $tracker->check_origin(new \WP_REST_Request('POST', '/wp-statistics/v2/external-page'));
        $this->assertWPError($missingOrigin);
        $this->assertSame(403, $missingOrigin->get_error_data()['status']);

        $crossOriginRequest = new \WP_REST_Request('POST', '/wp-statistics/v2/external-page');
        $crossOriginRequest->set_header('Origin', 'https://attacker.example');
        $crossOrigin = $tracker->check_origin($crossOriginRequest);

        $this->assertWPError($crossOrigin);
        $this->assertSame(403, $crossOrigin->get_error_data()['status']);
    }

    public function test_sanitizes_same_domain_page_paths()
    {
        $this->assertSame(
            '/tools/meshtastic-lab?mode=map',
            ExternalPage::sanitize_page_uri('/tools/meshtastic-lab?mode=map#status')
        );

        $this->assertSame(
            '/tools/meshtastic-lab?mode=map',
            ExternalPage::sanitize_page_uri(home_url('/tools/meshtastic-lab?mode=map'))
        );
    }

    public function test_rejects_external_page_urls()
    {
        $this->assertSame('', ExternalPage::sanitize_page_uri('https://attacker.example/tool'));
    }

    public function test_external_pages_have_a_readable_report_label()
    {
        $page = \WP_STATISTICS\Pages::get_page_info(0, 'external', '/tools/meshtastic-lab');

        $this->assertSame('External Page: /tools/meshtastic-lab', $page['title']);
        $this->assertSame(home_url('/tools/meshtastic-lab'), $page['link']);
    }

    public function test_registers_the_external_page_endpoint()
    {
        do_action('rest_api_init');
        $routes = rest_get_server()->get_routes();

        $this->assertArrayHasKey('/wp-statistics/v2/external-page', $routes);
        $this->assertTrue($routes['/wp-statistics/v2/external-page'][0]['methods']['POST']);
        $this->assertTrue($routes['/wp-statistics/v2/external-page'][0]['args']['page_uri']['required']);
    }

    public function test_records_an_external_page_as_a_page_view()
    {
        global $wpdb;

        $request = new \WP_REST_Request('POST', '/wp-statistics/v2/external-page');
        $request->set_header('Origin', home_url());
        $request->set_param('page_uri', '/tools/meshtastic-lab');
        $request->set_param('referred', '');
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 Chrome/120 Safari/537.36';
        do_action('rest_api_init');

        $response = rest_do_request($request);
        $responseData = $response->get_data();

        $this->assertTrue($responseData['status'], wp_json_encode($responseData));
        $this->assertSame(
            '1',
            $wpdb->get_var(
                $wpdb->prepare(
                    'SELECT COUNT(*) FROM ' . \WP_STATISTICS\DB::table('pages') . ' WHERE `type` = %s AND `uri` = %s',
                    'external',
                    '/tools/meshtastic-lab'
                )
            )
        );
    }
}
