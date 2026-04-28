<?php

namespace WP_Statistics\Tests\Multilang;

use WP_UnitTestCase;
use WP_Statistics\Service\Multilang\AdapterRegistry;
use WP_Statistics\Service\Multilang\MultilangService;
use WP_Statistics\Service\Multilang\Adapters\AbstractAdapter;
use WP_Statistics\Service\Resources\ResourceResolver;

/**
 * Integration tests for ResourceResolver's multi-language behavior.
 *
 * Creates the real `wp_statistics_resources` and `wp_statistics_resource_uris`
 * tables in the test database and exercises the resolver with controlled
 * MultilangService instances (per-post, per-request, no adapter).
 */
class Test_ResourceResolverMultilang extends WP_UnitTestCase
{
    /** @var string */
    private $resourcesTable;

    /** @var string */
    private $resourceUrisTable;

    public function setUp(): void
    {
        parent::setUp();
        global $wpdb;

        $this->resourcesTable    = $wpdb->prefix . 'statistics_resources';
        $this->resourceUrisTable = $wpdb->prefix . 'statistics_resource_uris';

        $wpdb->query("DROP TABLE IF EXISTS {$this->resourcesTable}");
        $wpdb->query("DROP TABLE IF EXISTS {$this->resourceUrisTable}");

        $wpdb->query("CREATE TABLE {$this->resourcesTable} (
            ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            resource_type varchar(64) NOT NULL,
            resource_id bigint(20) UNSIGNED NOT NULL,
            cached_title text,
            cached_terms text,
            cached_author_id bigint(20) UNSIGNED DEFAULT NULL,
            cached_date datetime,
            resource_meta text,
            language varchar(32) DEFAULT NULL,
            is_deleted tinyint(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (ID),
            KEY resource_id (resource_id)
        )");

        $wpdb->query("CREATE TABLE {$this->resourceUrisTable} (
            ID bigint(20) NOT NULL AUTO_INCREMENT,
            resource_id bigint(20) UNSIGNED NOT NULL,
            uri varchar(255) NOT NULL,
            PRIMARY KEY (ID)
        )");
    }

    public function tearDown(): void
    {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$this->resourcesTable}");
        $wpdb->query("DROP TABLE IF EXISTS {$this->resourceUrisTable}");

        MultilangService::reset();
        parent::tearDown();
    }

    /**
     * Install a controlled MultilangService for the resolver.
     */
    private function useAdapter(?string $mode, ?string $language): void
    {
        if ($mode === null) {
            MultilangService::setInstance(new MultilangService(new AdapterRegistry([])));
            return;
        }

        $adapter = new ResolverFakeAdapter($mode, $language);
        MultilangService::setInstance(new MultilangService(new AdapterRegistry([$adapter])));
    }

    /**
     * Read all resource rows for a (resource_id, resource_type) pair.
     */
    private function readResources(int $resourceId, string $resourceType): array
    {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->resourcesTable} WHERE resource_id = %d AND resource_type = %s ORDER BY ID",
                $resourceId,
                $resourceType
            )
        );
    }

    // ---------- baseline / no-adapter regression ----------

    public function test_no_adapter_inserts_resource_with_null_language(): void
    {
        $this->useAdapter(null, null);

        $uriId = ResourceResolver::resolveUriId(42, 'post', '/about');
        $this->assertGreaterThan(0, $uriId);

        $rows = $this->readResources(42, 'post');
        $this->assertCount(1, $rows);
        $this->assertNull($rows[0]->language);
    }

    public function test_no_adapter_does_not_modify_existing_row(): void
    {
        global $wpdb;
        $wpdb->insert($this->resourcesTable, [
            'resource_type' => 'post',
            'resource_id'   => 42,
            'language'      => null,
        ]);

        $this->useAdapter(null, null);
        ResourceResolver::resolveUriId(42, 'post', '/about');

        $rows = $this->readResources(42, 'post');
        $this->assertCount(1, $rows);
        $this->assertNull($rows[0]->language);
    }

    // ---------- per-post mode ----------

    public function test_per_post_mode_inserts_with_language(): void
    {
        $this->useAdapter('per-post', 'fr');

        ResourceResolver::resolveUriId(42, 'post', '/bonjour');

        $rows = $this->readResources(42, 'post');
        $this->assertCount(1, $rows);
        $this->assertSame('fr', $rows[0]->language);
    }

    public function test_per_post_mode_forward_fills_null_language(): void
    {
        global $wpdb;
        $wpdb->insert($this->resourcesTable, [
            'resource_type' => 'post',
            'resource_id'   => 42,
            'language'      => null,
        ]);

        $this->useAdapter('per-post', 'fr');
        ResourceResolver::resolveUriId(42, 'post', '/bonjour');

        $rows = $this->readResources(42, 'post');
        $this->assertCount(1, $rows);
        $this->assertSame('fr', $rows[0]->language, 'Existing NULL language should be filled');
    }

    public function test_per_post_mode_does_not_overwrite_existing_language(): void
    {
        global $wpdb;
        $wpdb->insert($this->resourcesTable, [
            'resource_type' => 'post',
            'resource_id'   => 42,
            'language'      => 'en',
        ]);

        // Adapter now reports 'fr' for the same post — but per the plan, each
        // post has a stable language and we must not overwrite the stored value.
        $this->useAdapter('per-post', 'fr');
        ResourceResolver::resolveUriId(42, 'post', '/anything');

        $rows = $this->readResources(42, 'post');
        $this->assertCount(1, $rows);
        $this->assertSame('en', $rows[0]->language, 'Existing language must not be overwritten');
    }

    public function test_per_post_mode_does_not_set_language_when_adapter_returns_null(): void
    {
        global $wpdb;
        $wpdb->insert($this->resourcesTable, [
            'resource_type' => 'post',
            'resource_id'   => 42,
            'language'      => null,
        ]);

        $this->useAdapter('per-post', null);
        ResourceResolver::resolveUriId(42, 'post', '/');

        $rows = $this->readResources(42, 'post');
        $this->assertNull($rows[0]->language);
    }

    public function test_per_post_mode_inserts_with_null_language_when_adapter_returns_null(): void
    {
        // Adapter active, but couldn't resolve (e.g. Polylang on a non-translated archive).
        $this->useAdapter('per-post', null);
        ResourceResolver::resolveUriId(99, 'post', '/missing');

        $rows = $this->readResources(99, 'post');
        $this->assertCount(1, $rows);
        $this->assertNull($rows[0]->language);
    }

    // ---------- per-request mode ----------

    public function test_per_request_mode_creates_separate_rows_per_language(): void
    {
        // Same post viewed twice with different request languages.
        $this->useAdapter('per-request', 'fr');
        $idA = ResourceResolver::resolveUriId(100, 'post', '/about');

        $this->useAdapter('per-request', 'es');
        $idB = ResourceResolver::resolveUriId(100, 'post', '/about');

        $rows = $this->readResources(100, 'post');
        $this->assertCount(2, $rows, 'Per-request mode keeps language as part of identity');

        $languages = array_column($rows, 'language');
        sort($languages);
        $this->assertSame(['es', 'fr'], $languages);

        // And the URI rows should map distinctly so the views table can join correctly.
        $this->assertNotSame($idA, $idB);
    }

    public function test_per_request_mode_reuses_row_for_same_language(): void
    {
        $this->useAdapter('per-request', 'fr');

        $id1 = ResourceResolver::resolveUriId(100, 'post', '/about');
        $id2 = ResourceResolver::resolveUriId(100, 'post', '/about');

        $rows = $this->readResources(100, 'post');
        $this->assertCount(1, $rows);
        $this->assertSame($id1, $id2);
    }

    public function test_per_request_mode_with_null_language_falls_back_to_legacy_bucket(): void
    {
        // If the adapter can't determine a language for this hit (e.g. WeGlot on
        // an admin-ajax call that escapes its rewrites), we should still record
        // the hit — into the language=NULL row — instead of failing.
        $this->useAdapter('per-request', null);
        $uriId = ResourceResolver::resolveUriId(50, 'post', '/x');
        $this->assertGreaterThan(0, $uriId);

        $rows = $this->readResources(50, 'post');
        $this->assertCount(1, $rows);
        $this->assertNull($rows[0]->language);
    }
}

/**
 * Test-double adapter for resolver tests — controllable mode and language.
 */
class ResolverFakeAdapter extends AbstractAdapter
{
    /** @var string */
    private $mode;

    /** @var string|null */
    private $language;

    public function __construct(string $mode, ?string $language)
    {
        $this->mode     = $mode;
        $this->language = $language;
    }

    public function getSlug(): string
    {
        return 'resolver-fake';
    }

    public function getName(): string
    {
        return 'Resolver Fake';
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
        return $this->language;
    }
}
