<?php


use WP_Statistics\Service\Analytics\DeviceDetection\UserAgentService;

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
        $this->assertEquals('Apple', $userAgentService->getModel(), 'Failed to detect iPhone device');
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
}
