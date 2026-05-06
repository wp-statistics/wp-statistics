<?php

namespace WP_Statistics\Tests\Multilang;

use WP_UnitTestCase;
use WP_Statistics\Service\Multilang\AdapterRegistry;
use WP_Statistics\Service\Multilang\MultilangService;
use WP_Statistics\Service\Multilang\Adapters\AbstractAdapter;

/**
 * Tests for MultilangService — the public entry point used by the rest of the
 * plugin. Wraps an AdapterRegistry, memoizes per-request, and exposes
 * label/mode/availability helpers.
 */
class Test_MultilangService extends WP_UnitTestCase
{
    public function tearDown(): void
    {
        MultilangService::reset();
        parent::tearDown();
    }

    public function test_returns_null_language_when_no_adapter_active(): void
    {
        $service = new MultilangService(new AdapterRegistry([]));

        $this->assertNull($service->detectLanguage('post', 1, '/'));
        $this->assertNull($service->getActiveAdapter());
        $this->assertSame([], $service->getAvailableLanguages());
        $this->assertNull($service->getMode());
    }

    public function test_delegates_detect_language_to_active_adapter(): void
    {
        $adapter = new ServiceFakeAdapter('fake', 'per-post', 'fr');
        $service = new MultilangService(new AdapterRegistry([$adapter]));

        $this->assertSame('fr', $service->detectLanguage('post', 42, '/about'));
    }

    public function test_memoizes_detect_language_per_request(): void
    {
        $adapter = new ServiceFakeAdapter('fake', 'per-post', 'en');
        $service = new MultilangService(new AdapterRegistry([$adapter]));

        $service->detectLanguage('post', 1, '/');
        $service->detectLanguage('post', 1, '/');
        $service->detectLanguage('post', 1, '/');

        $this->assertSame(1, $adapter->detectCallCount, 'Adapter should only be called once for the same key');
    }

    public function test_memoization_keys_by_resource_identity(): void
    {
        $adapter = new ServiceFakeAdapter('fake', 'per-post', 'en');
        $service = new MultilangService(new AdapterRegistry([$adapter]));

        $service->detectLanguage('post', 1, '/a');
        $service->detectLanguage('post', 2, '/b');
        $service->detectLanguage('page', 1, '/a');

        $this->assertSame(3, $adapter->detectCallCount);
    }

    public function test_get_mode_returns_active_adapter_mode(): void
    {
        $adapter = new ServiceFakeAdapter('fake', 'per-request', null);
        $service = new MultilangService(new AdapterRegistry([$adapter]));

        $this->assertSame('per-request', $service->getMode());
    }

    public function test_get_language_label_uses_adapter_available_languages(): void
    {
        $adapter = new ServiceFakeAdapter('fake', 'per-post', null);
        $adapter->available = ['fr' => 'French', 'en' => 'English'];
        $service = new MultilangService(new AdapterRegistry([$adapter]));

        $this->assertSame('French', $service->getLanguageLabel('fr'));
    }

    public function test_get_language_label_falls_back_to_code_when_no_adapter(): void
    {
        $service = new MultilangService(new AdapterRegistry([]));

        // Without an adapter we still want a sensible label for stored codes.
        $this->assertSame('English', $service->getLanguageLabel('en'));
        $this->assertSame('xx', $service->getLanguageLabel('xx'));
    }

    public function test_singleton_get_instance_returns_same_object(): void
    {
        $a = MultilangService::getInstance();
        $b = MultilangService::getInstance();

        $this->assertSame($a, $b);
    }

    public function test_set_instance_overrides_singleton(): void
    {
        $custom = new MultilangService(new AdapterRegistry([]));
        MultilangService::setInstance($custom);

        $this->assertSame($custom, MultilangService::getInstance());
    }

    public function test_language_is_identity_false_when_no_adapter(): void
    {
        $service = new MultilangService(new AdapterRegistry([]));

        $this->assertFalse($service->languageIsIdentity(0));
        $this->assertFalse($service->languageIsIdentity(42));
    }

    public function test_language_is_identity_true_for_per_request_mode(): void
    {
        $adapter = new ServiceFakeAdapter('trp', 'per-request', 'fr');
        $service = new MultilangService(new AdapterRegistry([$adapter]));

        // Per-request always buckets by language, regardless of resource_id
        $this->assertTrue($service->languageIsIdentity(0));
        $this->assertTrue($service->languageIsIdentity(42));
    }

    public function test_language_is_identity_true_for_per_post_when_resource_id_zero(): void
    {
        $adapter = new ServiceFakeAdapter('polylang', 'per-post', 'en');
        $service = new MultilangService(new AdapterRegistry([$adapter]));

        // Home/search/archive (resource_id=0) has no underlying post —
        // language must be part of identity or all-language hits collapse.
        $this->assertTrue($service->languageIsIdentity(0));

        // Real posts: language is a post-attribute, not identity
        $this->assertFalse($service->languageIsIdentity(42));
    }

    public function test_filterable_languages_returns_adapter_languages_when_active(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'statistics_resources';
        $wpdb->query("DROP TABLE IF EXISTS {$table}");
        $wpdb->query("CREATE TABLE {$table} (
            ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            resource_type varchar(64) NOT NULL,
            resource_id bigint(20) UNSIGNED NOT NULL,
            language varchar(32) NOT NULL DEFAULT '',
            is_deleted tinyint(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (ID)
        )");

        try {
            $adapter           = new ServiceFakeAdapter('fake', 'per-post', null);
            $adapter->available = [
                'en' => 'English',
                'fr' => 'Français',
            ];
            MultilangService::setInstance(new MultilangService(new AdapterRegistry([$adapter])));

            $filterable = MultilangService::getInstance()->getFilterableLanguages();

            $this->assertArrayHasKey('en', $filterable);
            $this->assertArrayHasKey('fr', $filterable);
            $this->assertSame('English', $filterable['en']);
            $this->assertSame('Français', $filterable['fr']);
        } finally {
            $wpdb->query("DROP TABLE IF EXISTS {$table}");
        }
    }

    public function test_filterable_languages_unions_adapter_with_db_codes(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'statistics_resources';
        $wpdb->query("DROP TABLE IF EXISTS {$table}");
        $wpdb->query("CREATE TABLE {$table} (
            ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            resource_type varchar(64) NOT NULL,
            resource_id bigint(20) UNSIGNED NOT NULL,
            language varchar(32) NOT NULL DEFAULT '',
            is_deleted tinyint(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (ID)
        )");

        try {
            // Adapter knows en and fr; DB has historical 'es' and 'de' codes
            // (e.g. from a previously-installed plugin or deleted languages).
            $wpdb->insert($table, ['resource_type' => 'post', 'resource_id' => 1, 'language' => 'es']);
            $wpdb->insert($table, ['resource_type' => 'post', 'resource_id' => 2, 'language' => 'de']);
            $wpdb->insert($table, ['resource_type' => 'post', 'resource_id' => 3, 'language' => 'en']);

            $adapter           = new ServiceFakeAdapter('fake', 'per-post', null);
            $adapter->available = [
                'en' => 'English',
                'fr' => 'Français',
            ];
            MultilangService::setInstance(new MultilangService(new AdapterRegistry([$adapter])));

            $filterable = MultilangService::getInstance()->getFilterableLanguages();

            $this->assertArrayHasKey('en', $filterable);
            $this->assertArrayHasKey('fr', $filterable);
            $this->assertArrayHasKey('es', $filterable, 'DB-only code is included');
            $this->assertArrayHasKey('de', $filterable, 'DB-only code is included');

            // Adapter labels take priority for adapter-known codes
            $this->assertSame('English', $filterable['en']);

            // DB-only codes get LanguageNames lookup labels (proper Spanish/German names)
            $this->assertNotSame('es', $filterable['es'], 'DB-only code gets a real label, not the code itself');
            $this->assertNotSame('de', $filterable['de']);
        } finally {
            $wpdb->query("DROP TABLE IF EXISTS {$table}");
        }
    }

    public function test_filterable_languages_falls_back_to_db_when_no_adapter(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'statistics_resources';
        $wpdb->query("DROP TABLE IF EXISTS {$table}");
        $wpdb->query("CREATE TABLE {$table} (
            ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            resource_type varchar(64) NOT NULL,
            resource_id bigint(20) UNSIGNED NOT NULL,
            language varchar(32) NOT NULL DEFAULT '',
            is_deleted tinyint(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (ID)
        )");

        try {
            // Site previously had a multilang plugin (or never did but data is mixed)
            $wpdb->insert($table, ['resource_type' => 'post', 'resource_id' => 1, 'language' => 'fr']);
            $wpdb->insert($table, ['resource_type' => 'post', 'resource_id' => 2, 'language' => 'en']);
            $wpdb->insert($table, ['resource_type' => 'post', 'resource_id' => 3, 'language' => '']);  // no-language row, must be excluded
            $wpdb->insert($table, ['resource_type' => 'post', 'resource_id' => 4, 'language' => 'fr']); // duplicate code

            // No adapter active — empty registry
            MultilangService::setInstance(new MultilangService(new AdapterRegistry([])));

            $filterable = MultilangService::getInstance()->getFilterableLanguages();

            $this->assertCount(2, $filterable, 'Distinct codes from DB, empty excluded');
            $this->assertArrayHasKey('fr', $filterable);
            $this->assertArrayHasKey('en', $filterable);
            $this->assertArrayNotHasKey('', $filterable, 'Empty language excluded');
        } finally {
            $wpdb->query("DROP TABLE IF EXISTS {$table}");
        }
    }

    public function test_filterable_languages_excludes_soft_deleted_rows(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'statistics_resources';
        $wpdb->query("DROP TABLE IF EXISTS {$table}");
        $wpdb->query("CREATE TABLE {$table} (
            ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            resource_type varchar(64) NOT NULL,
            resource_id bigint(20) UNSIGNED NOT NULL,
            language varchar(32) NOT NULL DEFAULT '',
            is_deleted tinyint(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (ID)
        )");

        try {
            $wpdb->insert($table, ['resource_type' => 'post', 'resource_id' => 1, 'language' => 'fr', 'is_deleted' => 0]);
            $wpdb->insert($table, ['resource_type' => 'post', 'resource_id' => 2, 'language' => 'de', 'is_deleted' => 1]);  // soft-deleted

            MultilangService::setInstance(new MultilangService(new AdapterRegistry([])));

            $filterable = MultilangService::getInstance()->getFilterableLanguages();

            $this->assertArrayHasKey('fr', $filterable);
            $this->assertArrayNotHasKey('de', $filterable, 'Soft-deleted rows are not surfaced as filter options');
        } finally {
            $wpdb->query("DROP TABLE IF EXISTS {$table}");
        }
    }
}

/**
 * Test-double adapter that records call counts and returns a fixed language.
 */
class ServiceFakeAdapter extends AbstractAdapter
{
    /** @var string */
    private $slug;

    /** @var string */
    private $mode;

    /** @var string|null */
    private $detected;

    /** @var int */
    public $detectCallCount = 0;

    /** @var array<string,string> */
    public $available = [];

    public function __construct(string $slug, string $mode, ?string $detected)
    {
        $this->slug     = $slug;
        $this->mode     = $mode;
        $this->detected = $detected;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getName(): string
    {
        return $this->slug;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function isActive(): bool
    {
        return true;
    }

    public function detectLanguage(string $resourceType, int $resourceId, string $uri): ?string
    {
        $this->detectCallCount++;
        return $this->detected;
    }

    public function getAvailableLanguages(): array
    {
        return $this->available;
    }
}
