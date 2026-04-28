<?php

namespace WP_Statistics\Tests\Multilang;

use WP_UnitTestCase;
use WP_Statistics\Service\Multilang\Adapters\PolylangAdapter;

require_once dirname(__DIR__) . '/multilang-plugin-stubs.php';

/**
 * Tests for the Polylang adapter.
 *
 * Polylang is a per-post plugin: each translation is its own post_id.
 */
class Test_PolylangAdapter extends WP_UnitTestCase
{
    /** @var PolylangAdapter */
    private $adapter;

    public function setUp(): void
    {
        parent::setUp();
        $GLOBALS['_wpstats_test_multilang'] = [
            'polylang' => [
                'posts'     => [],
                'terms'     => [],
                'current'   => 'en',
                'default'   => 'en',
                'languages' => ['en', 'fr', 'es'],
            ],
        ];
        $this->adapter = new PolylangAdapter();
    }

    public function tearDown(): void
    {
        $GLOBALS['_wpstats_test_multilang'] = [];
        parent::tearDown();
    }

    public function test_slug_is_polylang(): void
    {
        $this->assertSame('polylang', $this->adapter->getSlug());
    }

    public function test_name_is_human_readable(): void
    {
        $this->assertSame('Polylang', $this->adapter->getName());
    }

    public function test_mode_is_per_post(): void
    {
        $this->assertSame('per-post', $this->adapter->getMode());
    }

    public function test_is_active_when_polylang_function_exists(): void
    {
        // Stubs are loaded → pll_get_post_language exists → adapter sees Polylang as active
        $this->assertTrue($this->adapter->isActive());
    }

    public function test_detect_language_for_known_post(): void
    {
        $GLOBALS['_wpstats_test_multilang']['polylang']['posts'] = [42 => 'fr'];

        $this->assertSame('fr', $this->adapter->detectLanguage('post', 42, '/bonjour'));
    }

    public function test_detect_language_for_known_page(): void
    {
        $GLOBALS['_wpstats_test_multilang']['polylang']['posts'] = [10 => 'es'];

        $this->assertSame('es', $this->adapter->detectLanguage('page', 10, '/contacto'));
    }

    public function test_detect_language_for_taxonomy_term(): void
    {
        $GLOBALS['_wpstats_test_multilang']['polylang']['terms'] = [7 => 'fr'];

        $this->assertSame('fr', $this->adapter->detectLanguage('category', 7, '/categorie/bonjour'));
    }

    public function test_detect_language_falls_back_to_current_for_home(): void
    {
        $GLOBALS['_wpstats_test_multilang']['polylang']['current'] = 'fr';

        // resource_id 0 (home/archive) → use request-context language
        $this->assertSame('fr', $this->adapter->detectLanguage('home', 0, '/'));
    }

    public function test_detect_language_returns_null_when_post_unknown_and_no_current(): void
    {
        $GLOBALS['_wpstats_test_multilang']['polylang']['current'] = '';

        // Unknown post (no entry in stash) and no current request language → null
        $this->assertNull($this->adapter->detectLanguage('post', 999, '/missing'));
    }

    public function test_default_language_uses_pll_default_language(): void
    {
        $GLOBALS['_wpstats_test_multilang']['polylang']['default'] = 'es';

        $this->assertSame('es', $this->adapter->getDefaultLanguage());
    }

    public function test_available_languages_keys_are_codes(): void
    {
        $GLOBALS['_wpstats_test_multilang']['polylang']['languages'] = ['en', 'fr', 'es'];

        $available = $this->adapter->getAvailableLanguages();

        $this->assertIsArray($available);
        $this->assertArrayHasKey('en', $available);
        $this->assertArrayHasKey('fr', $available);
        $this->assertArrayHasKey('es', $available);
    }

    public function test_available_languages_have_human_labels(): void
    {
        $GLOBALS['_wpstats_test_multilang']['polylang']['languages'] = ['en', 'fr'];

        $available = $this->adapter->getAvailableLanguages();

        // Labels should be non-empty strings (we map from a known codes table)
        $this->assertNotEmpty($available['en']);
        $this->assertNotEmpty($available['fr']);
        $this->assertIsString($available['en']);
    }
}
