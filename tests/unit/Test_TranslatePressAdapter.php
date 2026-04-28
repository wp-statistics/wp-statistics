<?php

namespace WP_Statistics\Tests\Multilang;

use WP_UnitTestCase;
use WP_Statistics\Service\Multilang\Adapters\TranslatePressAdapter;

require_once dirname(__DIR__) . '/multilang-plugin-stubs.php';

/**
 * Tests for the TranslatePress adapter.
 *
 * TRP is per-request: language is determined by the URL slug at view time.
 * Detection ignores resource_id and reads from the current request.
 */
class Test_TranslatePressAdapter extends WP_UnitTestCase
{
    /** @var TranslatePressAdapter */
    private $adapter;

    /** @var string */
    private $currentLanguage = 'en_US';

    /** @var string */
    private $defaultLanguage = 'en_US';

    /** @var array */
    private $publishedLanguages = ['en_US', 'fr_FR', 'es_ES'];

    /** @var array<string,string> */
    private $publishedLabels = [
        'en_US' => 'English (United States)',
        'fr_FR' => 'Français',
        'es_ES' => 'Español',
    ];

    public function setUp(): void
    {
        parent::setUp();

        // TRP exposes the request's language via these well-known filters.
        add_filter('trp_current_language', function () {
            return $this->currentLanguage;
        });

        add_filter('trp_default_language', function () {
            return $this->defaultLanguage;
        });

        add_filter('trp_published_languages', function () {
            return $this->publishedLanguages;
        });

        add_filter('trp_published_languages_labels', function () {
            return $this->publishedLabels;
        });

        $this->adapter = new TranslatePressAdapter();
    }

    public function tearDown(): void
    {
        remove_all_filters('trp_current_language');
        remove_all_filters('trp_default_language');
        remove_all_filters('trp_published_languages');
        remove_all_filters('trp_published_languages_labels');
        parent::tearDown();
    }

    public function test_slug_is_translatepress(): void
    {
        $this->assertSame('translatepress', $this->adapter->getSlug());
    }

    public function test_name(): void
    {
        $this->assertSame('TranslatePress', $this->adapter->getName());
    }

    public function test_mode_is_per_request(): void
    {
        $this->assertSame('per-request', $this->adapter->getMode());
    }

    public function test_detect_language_normalizes_locale_to_hyphenated(): void
    {
        $this->currentLanguage = 'fr_FR';

        // 'fr_FR' → 'fr-fr' (lowercase, hyphen)
        $this->assertSame('fr-fr', $this->adapter->detectLanguage('post', 42, '/fr/bonjour'));
    }

    public function test_detect_language_ignores_resource_id_per_request_mode(): void
    {
        $this->currentLanguage = 'es_ES';

        // Same post viewed in two languages → adapter returns request language regardless.
        $this->assertSame('es-es', $this->adapter->detectLanguage('post', 100, '/es/about'));
        $this->assertSame('es-es', $this->adapter->detectLanguage('post', 100, '/about'));
    }

    public function test_detect_language_returns_null_when_unset(): void
    {
        $this->currentLanguage = '';

        $this->assertNull($this->adapter->detectLanguage('post', 1, '/'));
    }

    public function test_default_language(): void
    {
        $this->defaultLanguage = 'en_US';

        $this->assertSame('en-us', $this->adapter->getDefaultLanguage());
    }

    public function test_available_languages_uses_published_labels(): void
    {
        $available = $this->adapter->getAvailableLanguages();

        // Codes are normalized; labels come from trp_published_languages_labels
        $this->assertArrayHasKey('en-us', $available);
        $this->assertArrayHasKey('fr-fr', $available);
        $this->assertSame('English (United States)', $available['en-us']);
        $this->assertSame('Français', $available['fr-fr']);
    }
}
