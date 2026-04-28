<?php

namespace WP_Statistics\Tests\Multilang;

use WP_UnitTestCase;
use WP_Statistics\Service\Multilang\Adapters\WpmlAdapter;

require_once dirname(__DIR__) . '/multilang-plugin-stubs.php';

/**
 * Tests for the WPML adapter.
 *
 * WPML exposes most data through `apply_filters(...)` calls. We stub those
 * filters in setUp() so the adapter's filter calls return controlled values.
 */
class Test_WpmlAdapter extends WP_UnitTestCase
{
    /** @var WpmlAdapter */
    private $adapter;

    /** @var array<int, string> */
    private $postLanguages = [];

    /** @var string */
    private $currentLanguage = 'en';

    /** @var string */
    private $defaultLanguage = 'en';

    /** @var array */
    private $activeLanguages = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->postLanguages   = [];
        $this->currentLanguage = 'en';
        $this->defaultLanguage = 'en';
        $this->activeLanguages = [
            'en' => ['code' => 'en', 'translated_name' => 'English'],
            'fr' => ['code' => 'fr', 'translated_name' => 'French'],
            'es' => ['code' => 'es', 'translated_name' => 'Spanish'],
        ];

        // Wire filters to return our test fixtures.
        add_filter('wpml_post_language_details', function ($value, $postId) {
            return isset($this->postLanguages[$postId])
                ? ['language_code' => $this->postLanguages[$postId]]
                : $value;
        }, 10, 2);

        add_filter('wpml_current_language', function () {
            return $this->currentLanguage;
        });

        add_filter('wpml_default_language', function () {
            return $this->defaultLanguage;
        });

        add_filter('wpml_active_languages', function () {
            return $this->activeLanguages;
        });

        $this->adapter = new WpmlAdapter();
    }

    public function tearDown(): void
    {
        remove_all_filters('wpml_post_language_details');
        remove_all_filters('wpml_current_language');
        remove_all_filters('wpml_default_language');
        remove_all_filters('wpml_active_languages');
        parent::tearDown();
    }

    public function test_slug_is_wpml(): void
    {
        $this->assertSame('wpml', $this->adapter->getSlug());
    }

    public function test_name(): void
    {
        $this->assertSame('WPML', $this->adapter->getName());
    }

    public function test_mode_is_per_post(): void
    {
        $this->assertSame('per-post', $this->adapter->getMode());
    }

    public function test_detect_language_uses_wpml_post_language_details(): void
    {
        $this->postLanguages = [55 => 'fr'];

        $this->assertSame('fr', $this->adapter->detectLanguage('post', 55, '/bonjour'));
    }

    public function test_detect_language_falls_back_to_current_for_home(): void
    {
        $this->currentLanguage = 'es';

        $this->assertSame('es', $this->adapter->detectLanguage('home', 0, '/'));
    }

    public function test_detect_language_falls_back_to_current_when_post_unknown(): void
    {
        $this->postLanguages   = [];
        $this->currentLanguage = 'fr';

        $this->assertSame('fr', $this->adapter->detectLanguage('post', 999, '/missing'));
    }

    public function test_detect_language_returns_null_when_nothing_known(): void
    {
        $this->postLanguages   = [];
        $this->currentLanguage = '';

        $this->assertNull($this->adapter->detectLanguage('post', 999, '/missing'));
    }

    public function test_default_language(): void
    {
        $this->defaultLanguage = 'es';

        $this->assertSame('es', $this->adapter->getDefaultLanguage());
    }

    public function test_available_languages_uses_wpml_active_languages_filter(): void
    {
        $available = $this->adapter->getAvailableLanguages();

        $this->assertArrayHasKey('en', $available);
        $this->assertArrayHasKey('fr', $available);
        $this->assertArrayHasKey('es', $available);

        $this->assertSame('English', $available['en']);
        $this->assertSame('French', $available['fr']);
    }
}
