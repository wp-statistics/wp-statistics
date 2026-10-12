<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Service\Analytics\Referrals\SourceDetector;

class Test_SourceDetector extends WP_UnitTestCase
{
    protected function setUp(): void
    {
        // Set up necessary preconditions, if any.
        parent::setUp();

        // Set home URL to example.com for testing
        add_filter('home_url', function () {
            return 'http://example.com';
        });
    }

    /**
     * Test that the constructor correctly parses direct referrals.
     */
    public function test_detects_direct_referrals()
    {
        $referrerUrl    = '';
        $pageUrl        = 'http://example.com/';

        // Inject the mock parser into the SourceDetector.
        $sourceDetector = new SourceDetector($referrerUrl, $pageUrl);

        // Test if the referral was parsed correctly.
        $this->assertEquals('Direct', $sourceDetector->getName());
        $this->assertEquals('direct', $sourceDetector->getIdentifier());
        $this->assertEquals('direct', $sourceDetector->getChannel());
    }

    /**
     * Test that the constructor correctly parses the referral from search engines.
     */
    public function test_detects_search_engine_referrals()
    {
        $referrerUrl    = 'https://google.com';
        $pageUrl        = 'http://example.com/?utm_source=test';

        // Inject the mock parser into the SourceDetector.
        $sourceDetector = new SourceDetector($referrerUrl, $pageUrl);

        // Test if the referral was parsed correctly.
        $this->assertEquals('Google', $sourceDetector->getName());
        $this->assertEquals('google', $sourceDetector->getIdentifier());
        $this->assertEquals('search', $sourceDetector->getChannel());
    }

    /**
     * Test that AI assistant referrers are assigned to their own channel.
     *
     * @dataProvider ai_assistant_referrer_provider
     */
    public function test_detects_ai_assistant_referrals($referrerUrl, $expectedName, $expectedIdentifier)
    {
        $sourceDetector = new SourceDetector($referrerUrl, 'http://example.com/');

        $this->assertEquals($expectedName, $sourceDetector->getName());
        $this->assertEquals($expectedIdentifier, $sourceDetector->getIdentifier());
        $this->assertEquals('ai_assistants', $sourceDetector->getChannel());
        $this->assertEquals('AI Assistants', $sourceDetector->getChannelName());
    }

    public function ai_assistant_referrer_provider()
    {
        return [
            'ChatGPT'           => ['https://chatgpt.com/share/example', 'ChatGPT', 'chatgpt'],
            'ChatGPT legacy'    => ['https://chat.openai.com/share/example', 'ChatGPT', 'chatgpt'],
            'Perplexity'        => ['https://perplexity.ai/search/example', 'Perplexity', 'perplexity'],
            'Perplexity www'    => ['https://www.perplexity.ai/search/example', 'Perplexity', 'perplexity'],
            'Gemini'            => ['https://gemini.google.com/app/example', 'Gemini', 'gemini'],
            'Claude'            => ['https://claude.ai/chat/example', 'Claude', 'claude'],
            'Microsoft Copilot' => ['https://copilot.microsoft.com/chats/example', 'Microsoft Copilot', 'copilot'],
            'DeepSeek'          => ['https://chat.deepseek.com/a/chat/s/example', 'DeepSeek', 'deepseek'],
            'Grok'              => ['https://grok.com/c/example', 'Grok', 'grok'],
            'Mistral Le Chat'   => ['https://chat.mistral.ai/chat/example', 'Mistral Le Chat', 'mistral'],
            'Meta AI'           => ['https://meta.ai/c/example', 'Meta AI', 'meta_ai'],
            'You.com'           => ['https://you.com/search?q=example', 'You.com', 'you'],
            'Phind'             => ['https://phind.com/search?q=example', 'Phind', 'phind']
        ];
    }

    /**
     * Test that the constructor correctly parses the referral from search engines.
     */
    public function test_detects_paid_search_engine_referrals()
    {
        $referrerUrl    = 'https://google.com';
        $pageUrl        = 'http://example.com/?gad_source=test';

        // Inject the mock parser into the SourceDetector.
        $sourceDetector = new SourceDetector($referrerUrl, $pageUrl);

        // Test if the referral was parsed correctly.
        $this->assertEquals('Google Ads', $sourceDetector->getName());
        $this->assertEquals('google_ads', $sourceDetector->getIdentifier());
        $this->assertEquals('paid_search', $sourceDetector->getChannel());
    }

    /**
     * Test if null is returned for unknown referrals.
     */
    public function test_returns_null_for_unknown_referrals()
    {
        $referrerUrl    = 'https://unknown.com';
        $pageUrl        = 'http://example.com';

        // Inject the mock parser into the SourceDetector.
        $sourceDetector = new SourceDetector($referrerUrl, $pageUrl);

        // Test if null is returned when data is missing.
        $this->assertNull($sourceDetector->getName());
        $this->assertNull($sourceDetector->getIdentifier());
        $this->assertNull($sourceDetector->getChannel());
    }

    /**
     * Test if null is returned self referrals.
     */
    public function test_returns_null_for_self_referrals()
    {
        $referrerUrl    = 'http://example.com/hello-world';
        $pageUrl        = 'http://example.com?gad_source=test';

        // Inject the mock parser into the SourceDetector.
        $sourceDetector = new SourceDetector($referrerUrl, $pageUrl);

        // Test if null is returned when data is missing.
        $this->assertNull($sourceDetector->getName());
        $this->assertNull($sourceDetector->getIdentifier());
        $this->assertNull($sourceDetector->getChannel());
    }
}
