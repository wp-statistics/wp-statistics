<?php

namespace WP_Statistics\Tests;

use WP_Statistics\Service\Analytics\DeviceDetection\UserAgentService;
use WP_UnitTestCase;

class Test_UserAgentService extends WP_UnitTestCase
{
    /**
     * Test that UserAgentService can correctly identify a Chrome browser.
     */
    public function test_detects_chrome_browser()
    {
        // Mock a Chrome user agent string
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';

        $userAgentService = new UserAgentService();

        $this->assertEquals('Chrome', $userAgentService->getBrowser(), 'Failed to detect Chrome browser');
        $this->assertEquals('91.0', $userAgentService->getVersion(), 'Failed to detect correct Chrome version');
        $this->assertEquals('Windows', $userAgentService->getPlatform(), 'Failed to detect Windows platform');
    }

    /**
     * Test that UserAgentService correctly identifies an iPhone device.
     */
    public function test_detects_iphone_device()
    {
        // Mock an iPhone user agent string
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1';

        $userAgentService = new UserAgentService();

        $this->assertEquals('Mobile Safari', $userAgentService->getBrowser(), 'Failed to detect Safari browser on iPhone');
        $this->assertEquals('14.0', $userAgentService->getVersion(), 'Failed to detect correct Safari version');
        $this->assertEquals('iOS', $userAgentService->getPlatform(), 'Failed to detect iOS platform');
        $this->assertEquals('smartphone', $userAgentService->getDevice(), 'Failed to detect iPhone device');
        $this->assertEquals('Apple iPhone', $userAgentService->getModel(), 'Failed to detect iPhone device');
    }

    /**
     * Test that UserAgentService correctly identifies a Googlebot.
     */
    public function test_detects_google_bot()
    {
        // Mock a Googlebot user agent string
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

        $userAgentService = new UserAgentService();
        $deviceDetector   = $userAgentService->getDeviceDetector();

        $this->assertTrue($deviceDetector->isBot(), 'Failed to detect Googlebot');
        $this->assertEquals('Googlebot', $deviceDetector->getBot()['name'], 'Failed to identify Googlebot');
    }

    /**
     * Test that UserAgentService correctly identifies a Bingbot.
     */
    public function test_detects_bing_bot()
    {
        // Mock a Bingbot user agent string
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)';

        $userAgentService = new UserAgentService();
        $deviceDetector   = $userAgentService->getDeviceDetector();

        $this->assertTrue($deviceDetector->isBot(), 'Failed to detect Bingbot');
        $this->assertEquals('BingBot', $deviceDetector->getBot()['name'], 'Failed to identify Bingbot');
    }

    /**
     * Test that UserAgentService correctly identifies a Baidu spider.
     */
    public function test_detects_baidu_spider()
    {
        // Mock a Baidu spider user agent string
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)';

        $userAgentService = new UserAgentService();
        $deviceDetector   = $userAgentService->getDeviceDetector();

        $this->assertTrue($deviceDetector->isBot(), 'Failed to detect Baiduspider');
        $this->assertEquals('Baidu Spider', $deviceDetector->getBot()['name'], 'Failed to identify Baiduspider');
    }

    /**
     * Test that UserAgentService correctly identifies a Yandex bot.
     */
    public function test_detects_yandex_bot()
    {
        // Mock a Yandex bot user agent string
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)';

        $userAgentService = new UserAgentService();
        $deviceDetector   = $userAgentService->getDeviceDetector();

        $this->assertTrue($deviceDetector->isBot(), 'Failed to detect YandexBot');
        $this->assertEquals('Yandex Bot', $deviceDetector->getBot()['name'], 'Failed to identify YandexBot');
    }

    /**
     * Test that UserAgentService handles unknown user agents gracefully.
     */
    public function test_handles_unknown_user_agent()
    {
        // Mock an unknown user agent string
        $_SERVER['HTTP_USER_AGENT'] = 'UnknownUserAgent/1.0';

        $userAgentService = new UserAgentService();

        $this->assertEquals('UNK', $userAgentService->getBrowser());
        $this->assertEquals('.NK', $userAgentService->getVersion());
        $this->assertEquals('UNK', $userAgentService->getPlatform());
        $this->assertEquals('', $userAgentService->getDevice());
    }

    /**
     * Test that the DeviceDetector object is returned correctly.
     */
    public function test_get_device_detector()
    {
        // Mock a standard user agent string
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';

        $userAgentService = new UserAgentService();
        $deviceDetector   = $userAgentService->getDeviceDetector();

        $this->assertInstanceOf('WP_Statistics\Dependencies\DeviceDetector\DeviceDetector', $deviceDetector, 'Failed to return a DeviceDetector instance');
    }

    /**
     * Test that no errors occur if the user agent is missing.
     */
    public function test_handles_missing_user_agent()
    {
        // Clear the user agent string
        unset($_SERVER['HTTP_USER_AGENT']);

        $userAgentService = new UserAgentService();

        $this->assertEquals('UNK', $userAgentService->getBrowser());
    }

    public function test_sanitize_preserves_null_and_empty()
    {
        $this->assertNull(UserAgentService::sanitizeDetectorValue(null));
        $this->assertSame('', UserAgentService::sanitizeDetectorValue(''));
    }

    public function test_sanitize_keeps_common_browser_names()
    {
        $this->assertSame('Chrome', UserAgentService::sanitizeDetectorValue('Chrome'));
        $this->assertSame('Microsoft Edge', UserAgentService::sanitizeDetectorValue('Microsoft Edge'));
        $this->assertSame('Samsung Browser', UserAgentService::sanitizeDetectorValue('Samsung Browser'));
    }

    public function test_sanitize_keeps_allowed_punctuation()
    {
        $this->assertSame('Opera Mini/Beta', UserAgentService::sanitizeDetectorValue('Opera Mini/Beta'));
        $this->assertSame('UC Browser (HD)', UserAgentService::sanitizeDetectorValue('UC Browser (HD)'));
        $this->assertSame('Chrome-Mobile_v2.1', UserAgentService::sanitizeDetectorValue('Chrome-Mobile_v2.1'));
    }

    public function test_sanitize_keeps_unicode_letters()
    {
        $this->assertSame('Yandex Браузер', UserAgentService::sanitizeDetectorValue('Yandex Браузер'));
    }

    public function test_sanitize_strips_html_tags()
    {
        $this->assertSame('Chrome', UserAgentService::sanitizeDetectorValue('<script>alert(1)</script>Chrome'));
        $this->assertSame('Firefox', UserAgentService::sanitizeDetectorValue('<img src=x onerror=alert(1)>Firefox'));
    }

    public function test_sanitize_neutralizes_attribute_breakers()
    {
        $clean = UserAgentService::sanitizeDetectorValue('evil" onload="alert(document.domain)');

        $this->assertStringNotContainsString('"', $clean);
        $this->assertStringNotContainsString('<', $clean);
        $this->assertStringNotContainsString('>', $clean);
        $this->assertStringNotContainsString('=', $clean);

        $this->assertSame("abc'", UserAgentService::sanitizeDetectorValue("abc'\"<>`"));
        $this->assertSame('abc1', UserAgentService::sanitizeDetectorValue("abc=1;"));
    }

    public function test_sanitize_strips_control_characters_and_collapses_whitespace()
    {
        $this->assertSame('Chrome', UserAgentService::sanitizeDetectorValue("Chrome\x00\x01\x1f"));
        $this->assertSame('Chrome Mobile', UserAgentService::sanitizeDetectorValue("  Chrome    Mobile  "));
        $this->assertSame('Chrome Mobile', UserAgentService::sanitizeDetectorValue("Chrome\n\nMobile"));
    }

    /**
     * End-to-end: the exact payload from the reported vulnerability must
     * not produce a stored browser name containing attribute-breaking
     * characters, so admin templates can't render an injected handler.
     */
    public function test_crafted_user_agent_is_sanitized_before_storage()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'evil" onload="alert(document.domain)/1.2 (iPhone; iOS 16.0; Scale/3.00)';

        $userAgentService = new UserAgentService();
        $browser          = (string) $userAgentService->getBrowser();

        $this->assertStringNotContainsString('"', $browser);
        $this->assertStringNotContainsString('<', $browser);
        $this->assertStringNotContainsString('>', $browser);
        $this->assertStringNotContainsString('=', $browser);
    }

    /**
     * Sanitizer must be a no-op for the browser/OS names that show up in
     * real traffic — regression guard so future tightening doesn't quietly
     * mangle mainstream labels.
     *
     * @dataProvider realWorldUserAgentProvider
     */
    public function test_sanitize_is_noop_for_real_user_agents($userAgent, $expectedBrowser, $expectedPlatform)
    {
        $_SERVER['HTTP_USER_AGENT'] = $userAgent;

        $service = new UserAgentService();

        $this->assertSame($expectedBrowser, $service->getBrowser());
        $this->assertSame($expectedPlatform, $service->getPlatform());
    }

    public function realWorldUserAgentProvider()
    {
        return [
            'Chrome on Windows' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Chrome',
                'Windows',
            ],
            'Firefox on macOS' => [
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:121.0) Gecko/20100101 Firefox/121.0',
                'Firefox',
                'Mac',
            ],
            'Safari on iPhone' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1',
                'Mobile Safari',
                'iOS',
            ],
            'Edge on Windows' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0',
                'Microsoft Edge',
                'Windows',
            ],
            'Chrome on Android' => [
                'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
                'Chrome Mobile',
                'Android',
            ],
            'Samsung Internet' => [
                'Mozilla/5.0 (Linux; Android 13; SAMSUNG SM-G998B) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/23.0 Chrome/115.0.0.0 Mobile Safari/537.36',
                'Samsung Browser',
                'Android',
            ],
            'Opera on Windows' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36 OPR/102.0.0.0',
                'Opera',
                'Windows',
            ],
            'Yandex on Android' => [
                'Mozilla/5.0 (Linux; arm_64; Android 11; SM-A515F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.0.0 YaBrowser/22.11.5.93.00 SA/3 Mobile Safari/537.36',
                'Yandex Browser',
                'Android',
            ],
            'IE 11' => [
                'Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko',
                'Internet Explorer',
                'Windows',
            ],
        ];
    }

    /**
     * Brand/OS labels that contain `&` or `'` must pass through unchanged
     * so stats aren't split across multiple buckets (e.g. AT&T vs ATT).
     * Both characters are safe inside double-quoted HTML attributes, and
     * the output sinks already apply esc_attr/esc_html.
     */
    public function test_sanitize_preserves_ampersand_and_apostrophe_in_labels()
    {
        $this->assertSame('AT&T', UserAgentService::sanitizeDetectorValue('AT&T'));
        $this->assertSame('Barnes & Noble', UserAgentService::sanitizeDetectorValue('Barnes & Noble'));
        $this->assertSame("BYJU'S", UserAgentService::sanitizeDetectorValue("BYJU'S"));
        $this->assertSame('Krüger&Matz', UserAgentService::sanitizeDetectorValue('Krüger&Matz'));
    }

    /**
     * Extremely niche labels with `!` / `^` still get normalized. Pinned
     * here so we notice if the character class ever changes.
     */
    public function test_sanitize_normalizes_exclamation_and_caret()
    {
        $this->assertSame('Yahoo Japan Browser', UserAgentService::sanitizeDetectorValue('Yahoo! Japan Browser'));
        $this->assertSame('FRITZOS', UserAgentService::sanitizeDetectorValue('FRITZ!OS'));
        $this->assertSame('Symbian3', UserAgentService::sanitizeDetectorValue('Symbian^3'));
    }

    public function test_sanitize_preserves_unicode_letters_in_labels()
    {
        $this->assertSame('Caixa Mágica', UserAgentService::sanitizeDetectorValue('Caixa Mágica'));
        $this->assertSame('Arçelik', UserAgentService::sanitizeDetectorValue('Arçelik'));
        $this->assertSame('ALDI SÜD', UserAgentService::sanitizeDetectorValue('ALDI SÜD'));
        $this->assertSame('Türk Telekom', UserAgentService::sanitizeDetectorValue('Türk Telekom'));
    }
}
