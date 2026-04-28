<?php

namespace WP_Statistics\Tests\Multilang;

use WP_UnitTestCase;
use WP_Statistics\Service\Multilang\Adapters\WeglotAdapter;

require_once dirname(__DIR__) . '/multilang-plugin-stubs.php';

/**
 * Tests for the WeGlot adapter.
 *
 * WeGlot is per-request: server can read the URL prefix (or filtered value) via
 * weglot_get_current_language(). Original-language posts get the original code.
 */
class Test_WeglotAdapter extends WP_UnitTestCase
{
    /** @var WeglotAdapter */
    private $adapter;

    public function setUp(): void
    {
        parent::setUp();
        $GLOBALS['_wpstats_test_multilang'] = [
            'weglot' => [
                'current'      => 'en',
                'original'     => 'en',
                'destinations' => ['fr', 'es', 'de'],
            ],
        ];
        $this->adapter = new WeglotAdapter();
    }

    public function tearDown(): void
    {
        $GLOBALS['_wpstats_test_multilang'] = [];
        parent::tearDown();
    }

    public function test_slug(): void
    {
        $this->assertSame('weglot', $this->adapter->getSlug());
    }

    public function test_name(): void
    {
        $this->assertSame('Weglot', $this->adapter->getName());
    }

    public function test_mode_is_per_request(): void
    {
        $this->assertSame('per-request', $this->adapter->getMode());
    }

    public function test_detect_language(): void
    {
        $GLOBALS['_wpstats_test_multilang']['weglot']['current'] = 'fr';

        $this->assertSame('fr', $this->adapter->detectLanguage('post', 1, '/fr/about'));
    }

    public function test_detect_language_returns_null_when_empty(): void
    {
        $GLOBALS['_wpstats_test_multilang']['weglot']['current'] = '';

        $this->assertNull($this->adapter->detectLanguage('post', 1, '/'));
    }

    public function test_default_language_uses_original(): void
    {
        $GLOBALS['_wpstats_test_multilang']['weglot']['original'] = 'es';

        $this->assertSame('es', $this->adapter->getDefaultLanguage());
    }

    public function test_available_languages_includes_original_and_destinations(): void
    {
        $GLOBALS['_wpstats_test_multilang']['weglot']['original']     = 'en';
        $GLOBALS['_wpstats_test_multilang']['weglot']['destinations'] = ['fr', 'es'];

        $available = $this->adapter->getAvailableLanguages();

        $this->assertArrayHasKey('en', $available);
        $this->assertArrayHasKey('fr', $available);
        $this->assertArrayHasKey('es', $available);
    }
}
