<?php

namespace WP_Statistics\Tests\Multilang;

use WP_UnitTestCase;
use WP_Statistics\Service\Multilang\Adapters\QTranslateAdapter;

require_once dirname(__DIR__) . '/multilang-plugin-stubs.php';

/**
 * Tests for the qTranslate-X / qTranslate-XT adapter.
 *
 * qTranslate is per-request: language toggle within the same post via shortcodes
 * like [:en]…[:fr]…
 */
class Test_QTranslateAdapter extends WP_UnitTestCase
{
    /** @var QTranslateAdapter */
    private $adapter;

    public function setUp(): void
    {
        parent::setUp();
        $GLOBALS['_wpstats_test_multilang'] = [
            'qtranslate' => [
                'current' => 'en',
                'default' => 'en',
                'enabled' => ['en', 'fr', 'es'],
                'names'   => [
                    'en' => 'English',
                    'fr' => 'Français',
                    'es' => 'Español',
                ],
            ],
        ];
        $this->adapter = new QTranslateAdapter();
    }

    public function tearDown(): void
    {
        $GLOBALS['_wpstats_test_multilang'] = [];
        parent::tearDown();
    }

    public function test_slug(): void
    {
        $this->assertSame('qtranslate', $this->adapter->getSlug());
    }

    public function test_name(): void
    {
        $this->assertSame('qTranslate-X', $this->adapter->getName());
    }

    public function test_mode_is_per_request(): void
    {
        $this->assertSame('per-request', $this->adapter->getMode());
    }

    public function test_detect_language_uses_qtranxf_get_language(): void
    {
        $GLOBALS['_wpstats_test_multilang']['qtranslate']['current'] = 'fr';

        $this->assertSame('fr', $this->adapter->detectLanguage('post', 5, '/quelque-chose'));
    }

    public function test_detect_language_returns_null_when_empty(): void
    {
        $GLOBALS['_wpstats_test_multilang']['qtranslate']['current'] = '';

        $this->assertNull($this->adapter->detectLanguage('post', 5, '/'));
    }

    public function test_default_language(): void
    {
        $GLOBALS['_wpstats_test_multilang']['qtranslate']['default'] = 'es';

        $this->assertSame('es', $this->adapter->getDefaultLanguage());
    }

    public function test_available_languages_uses_enabled_list_with_names(): void
    {
        $available = $this->adapter->getAvailableLanguages();

        $this->assertArrayHasKey('en', $available);
        $this->assertArrayHasKey('fr', $available);
        $this->assertSame('English', $available['en']);
        $this->assertSame('Français', $available['fr']);
    }
}
