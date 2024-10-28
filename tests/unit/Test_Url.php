<?php

namespace WP_Statistics\Tests\Utils;

use WP_Statistics\Utils\Url;
use WP_UnitTestCase;

class Test_Url extends WP_UnitTestCase
{
    /**
     * Test the getProtocol method with various URLs.
     */
    public function test_getProtocol()
    {
        // Test a URL with https
        $url = 'https://example.com';
        $this->assertEquals('https', Url::getProtocol($url));

        // Test a URL with http
        $url = 'http://example.com';
        $this->assertEquals('http', Url::getProtocol($url));

        // Test an invalid URL (no scheme)
        $url = 'example.com';
        $this->assertEquals('', Url::getProtocol($url));

        // Test an invalid URL
        $url = 'not a valid url';
        $this->assertEquals('', Url::getProtocol($url));
    }

    /**
     * Test the getDomain method with and without protocol.
     */
    public function test_getDomain()
    {
        // Test domain extraction without protocol
        $url = 'https://www.example.com';
        $this->assertEquals('example.com', Url::getDomain($url));

        // Test domain extraction with protocol
        $url = 'https://www.example.com';
        $this->assertEquals('https://example.com', Url::getDomain($url, true));

        // Test an invalid URL
        $url = '';
        $this->assertEquals('', Url::getDomain($url));

        // Test domain extraction without www
        $url = 'https://www.test.com';
        $this->assertEquals('test.com', Url::getDomain($url));
    }

    /**
     * Test the formatUrl method.
     */
    public function test_formatUrl()
    {
        // Test URL formatting with no protocol
        $url = 'example.com/';
        $this->assertEquals('https://example.com', Url::formatUrl($url));

        // Test URL formatting with http protocol
        $url = 'http://example.com/';
        $this->assertEquals('http://example.com', Url::formatUrl($url));

        // Test URL formatting with https protocol
        $url = 'https://example.com/';
        $this->assertEquals('https://example.com', Url::formatUrl($url));

        // Test an already formatted URL
        $url = 'https://example.com';
        $this->assertEquals('https://example.com', Url::formatUrl($url));
    }

    /**
     * Test the isInternal method.
     */
    public function test_isInternal()
    {
        // Set home URL to example.com for testing
        add_filter('home_url', function () {
            return 'https://example.com';
        });

        // Test internal URL
        $url = 'https://example.com';
        $this->assertTrue(Url::isInternal($url));

        // Test external URL
        $url = 'https://another-domain.com';
        $this->assertFalse(Url::isInternal($url));

        // Test URL with www
        $url = 'https://www.example.com';
        $this->assertTrue(Url::isInternal($url));
    }
}
