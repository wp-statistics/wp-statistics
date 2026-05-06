<?php

namespace WP_Statistics\Tests\Records;

use WP_UnitTestCase;
use WP_Statistics\Records\RecordFactory;

/**
 * Integration tests for ResourceRecord::upsertWithLanguageFill().
 *
 * The forward-fill rule: when a row already exists, the language column
 * is filled if currently empty, but a non-empty existing value is never
 * overwritten. This must be atomic so concurrent inserts don't duplicate.
 */
class Test_ResourceRecordUpsert extends WP_UnitTestCase
{
    /** @var string */
    private $table;

    public function setUp(): void
    {
        parent::setUp();
        global $wpdb;

        $this->table = $wpdb->prefix . 'statistics_resources';
        $wpdb->query("DROP TABLE IF EXISTS {$this->table}");
        $wpdb->query("CREATE TABLE {$this->table} (
            ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            resource_type varchar(64) NOT NULL,
            resource_id bigint(20) UNSIGNED NOT NULL,
            cached_title text,
            cached_terms text,
            cached_author_id bigint(20) UNSIGNED DEFAULT NULL,
            cached_date datetime,
            resource_meta text,
            language varchar(32) NOT NULL DEFAULT '',
            is_deleted tinyint(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (ID),
            UNIQUE KEY uk_resource_lang (resource_id, resource_type, language)
        )");
    }

    public function tearDown(): void
    {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$this->table}");
        parent::tearDown();
    }

    private function row(int $id): ?object
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table} WHERE ID = %d", $id));
    }

    private function rowCount(int $resourceId, string $resourceType): int
    {
        global $wpdb;
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE resource_id = %d AND resource_type = %s",
            $resourceId,
            $resourceType
        ));
    }

    public function test_first_call_inserts_new_row(): void
    {
        $id = RecordFactory::resource()->upsertWithLanguageFill(42, 'post', 'fr');

        $this->assertGreaterThan(0, $id);
        $this->assertSame('fr', $this->row($id)->language);
        $this->assertSame(1, $this->rowCount(42, 'post'));
    }

    public function test_repeat_call_with_same_identity_returns_same_id(): void
    {
        $id1 = RecordFactory::resource()->upsertWithLanguageFill(42, 'post', 'fr');
        $id2 = RecordFactory::resource()->upsertWithLanguageFill(42, 'post', 'fr');

        $this->assertSame($id1, $id2);
        $this->assertSame(1, $this->rowCount(42, 'post'));
    }

    public function test_forward_fill_replaces_empty_language(): void
    {
        // Pre-existing row with empty language (the no-multilang or pre-detection state)
        $id = RecordFactory::resource()->upsertWithLanguageFill(42, 'post', '');
        $this->assertSame('', $this->row($id)->language);

        // Same identity, now with detected language → should be filled
        $id2 = RecordFactory::resource()->upsertWithLanguageFill(42, 'post', 'fr');

        $this->assertSame($id, $id2, 'Same identity returns the same ID');
        $this->assertSame('fr', $this->row($id)->language, 'Empty language is filled');
        $this->assertSame(1, $this->rowCount(42, 'post'));
    }

    public function test_does_not_overwrite_existing_non_empty_language(): void
    {
        $id = RecordFactory::resource()->upsertWithLanguageFill(42, 'post', 'en');
        $this->assertSame('en', $this->row($id)->language);

        // Same identity, different language → must NOT overwrite
        $id2 = RecordFactory::resource()->upsertWithLanguageFill(42, 'post', 'en');

        $this->assertSame($id, $id2);
        $this->assertSame('en', $this->row($id)->language, 'Existing language is preserved');
    }

    public function test_different_languages_produce_different_rows(): void
    {
        // Per-request mode case: same post viewed in two languages → two rows
        $idEn = RecordFactory::resource()->upsertWithLanguageFill(42, 'post', 'en');
        $idFr = RecordFactory::resource()->upsertWithLanguageFill(42, 'post', 'fr');

        $this->assertNotSame($idEn, $idFr);
        $this->assertSame(2, $this->rowCount(42, 'post'));
    }

    public function test_empty_language_across_two_calls_is_one_row(): void
    {
        // No-multilang case: both calls pass '' → must be one row, not two
        $id1 = RecordFactory::resource()->upsertWithLanguageFill(42, 'post', '');
        $id2 = RecordFactory::resource()->upsertWithLanguageFill(42, 'post', '');

        $this->assertSame($id1, $id2);
        $this->assertSame(1, $this->rowCount(42, 'post'));
    }
}
