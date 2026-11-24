<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Service\Analytics\Referrals\SourceDetector;

/**
 * Paid ad clicks get their own source rows: OpenAI Ads (oppref), Microsoft Ads (msclkid)
 * and Facebook Ads (facebook.com referrer + utm_source=facebook + a paid utm_medium).
 * Everything else keeps its old classification. All URLs here are made up.
 */
class Test_PaidAdSources extends WP_UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        add_filter('home_url', function () {
            return 'http://example.com';
        });
    }

    private function assertSource($referrerUrl, $pageUrl, $expectedName, $expectedIdentifier, $expectedChannel)
    {
        $sourceDetector = new SourceDetector($referrerUrl, $pageUrl);

        $this->assertSame($expectedName, $sourceDetector->getName(), "name for {$referrerUrl} {$pageUrl}");
        $this->assertSame($expectedIdentifier, $sourceDetector->getIdentifier(), "identifier for {$referrerUrl} {$pageUrl}");
        $this->assertSame($expectedChannel, $sourceDetector->getChannel(), "channel for {$referrerUrl} {$pageUrl}");
    }

    /**
     * @dataProvider openai_ads_provider
     */
    public function test_oppref_is_openai_ads($referrerUrl, $pageUrl)
    {
        $this->assertSource($referrerUrl, $pageUrl, 'OpenAI Ads', 'openai_ads', 'paid_other');
    }

    public function openai_ads_provider()
    {
        return [
            'no referrer'            => ['', 'http://example.com/pricing/?oppref=test123'],
            'chatgpt referrer'       => ['https://chatgpt.com/', 'http://example.com/?oppref=test123'],
            'with utm parameters'    => ['', 'http://example.com/?utm_source=chatgpt.com&oppref=test123'],
            'upper case parameter'   => ['', 'http://example.com/?OPPREF=test123'],
        ];
    }

    public function test_chatgpt_without_oppref_stays_an_ai_assistant()
    {
        $this->assertSource('https://chatgpt.com/', 'http://example.com/', 'ChatGPT', 'chatgpt', 'ai_assistants');
    }

    /**
     * @dataProvider microsoft_ads_provider
     */
    public function test_msclkid_is_microsoft_ads($referrerUrl, $pageUrl)
    {
        $this->assertSource($referrerUrl, $pageUrl, 'Microsoft Ads', 'microsoft_ads', 'paid_search');
    }

    public function microsoft_ads_provider()
    {
        return [
            'no referrer'          => ['', 'http://example.com/?msclkid=test123'],
            'bing referrer'        => ['https://www.bing.com/search?q=example', 'http://example.com/?msclkid=test123'],
            'bing utm parameters'  => ['https://www.bing.com/', 'http://example.com/?utm_source=bing&utm_medium=cpc&msclkid=test123'],
            'partner search site'  => ['https://duckduckgo.com/', 'http://example.com/?msclkid=test123'],
        ];
    }

    public function test_bing_without_msclkid_is_unchanged()
    {
        $this->assertSource('https://www.bing.com/search?q=example', 'http://example.com/', 'Bing', 'bing', 'search');
        $this->assertSource('https://www.bing.com/', 'http://example.com/?utm_source=bing&utm_medium=cpc', 'Bing', 'bing', 'paid_search');
    }

    public function test_gclid_stays_google_ads()
    {
        $this->assertSource('https://www.google.com/', 'http://example.com/?gclid=test123', 'Google Ads', 'google_ads', 'paid_search');
    }

    /**
     * @dataProvider facebook_ads_provider
     */
    public function test_paid_facebook_clicks_are_facebook_ads($referrerUrl, $pageUrl)
    {
        $this->assertSource($referrerUrl, $pageUrl, 'Facebook Ads', 'facebook_ads', 'paid_social');
    }

    public function facebook_ads_provider()
    {
        return [
            'l.facebook.com, paid'            => ['https://l.facebook.com/l.php?u=example', 'http://example.com/?utm_source=facebook&utm_medium=paid'],
            'm.facebook.com, cpc'             => ['https://m.facebook.com/', 'http://example.com/?utm_source=facebook&utm_medium=cpc'],
            'facebook.com, mixed case source' => ['https://www.facebook.com/', 'http://example.com/?utm_source=Facebook&utm_medium=paid_social'],
            'fb source, with fbclid'          => ['https://l.facebook.com/', 'http://example.com/?utm_source=fb&utm_medium=paid&fbclid=test123'],
        ];
    }

    /**
     * @dataProvider facebook_not_ads_provider
     */
    public function test_other_facebook_clicks_are_not_facebook_ads($referrerUrl, $pageUrl, $expectedName, $expectedChannel)
    {
        $sourceDetector = new SourceDetector($referrerUrl, $pageUrl);

        $this->assertSame($expectedName, $sourceDetector->getName());
        $this->assertSame($expectedChannel, $sourceDetector->getChannel());
    }

    public function facebook_not_ads_provider()
    {
        return [
            'fbclid alone'                    => ['https://l.facebook.com/', 'http://example.com/?fbclid=test123', 'Facebook', 'social'],
            'fbclid alone, no referrer'       => ['', 'http://example.com/?fbclid=test123', 'Facebook', 'social'],
            'fbclid, paid medium, no referrer' => ['', 'http://example.com/?fbclid=test123&utm_medium=paid', 'Paid Other', 'paid_other'],
            'fbclid with paid medium only'    => ['https://l.facebook.com/', 'http://example.com/?fbclid=test123&utm_medium=paid', 'Facebook', 'paid_social'],
            'organic utm_medium'              => ['https://l.facebook.com/', 'http://example.com/?utm_source=facebook&utm_medium=social', 'Facebook', 'social'],
            'paid tags but no referrer'       => ['', 'http://example.com/?utm_source=facebook&utm_medium=paid', 'Facebook', 'paid_social'],
            'look-alike referrer domain'      => ['https://notfacebook.com/', 'http://example.com/?utm_source=facebook&utm_medium=paid', 'Facebook', 'paid_social'],
            'instagram is not facebook'       => ['https://l.instagram.com/', 'http://example.com/?utm_source=instagram&utm_medium=paid', 'Instagram', 'paid_social'],
        ];
    }
}
