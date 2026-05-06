<?php

namespace WP_Statistics\Tests\Records;

use WP_UnitTestCase;
use WP_Statistics\Records\RecordFactory;

class Test_ResourceUriRecordUpsert extends WP_UnitTestCase
{
    /** @var string */
    private $table;

    public function setUp(): void
    {
        parent::setUp();
        global $wpdb;

        $this->table = $wpdb->prefix . 'statistics_resource_uris';
        $wpdb->query("DROP TABLE IF EXISTS {$this->table}");
        $wpdb->query("CREATE TABLE {$this->table} (
            ID bigint(20) NOT NULL AUTO_INCREMENT,
            resource_id bigint(20) UNSIGNED NOT NULL,
            uri varchar(255) NOT NULL,
            PRIMARY KEY (ID),
            UNIQUE KEY uk_resource_uri (resource_id, uri)
        )");
    }

    public function tearDown(): void
    {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$this->table}");
        parent::tearDown();
    }

    private function rowCount(int $resourceId, string $uri): int
    {
        global $wpdb;
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE resource_id = %d AND uri = %s",
            $resourceId,
            $uri
        ));
    }

    public function test_first_call_inserts_new_row(): void
    {
        $id = RecordFactory::resourceUri()->findOrCreate(7, '/about');

        $this->assertGreaterThan(0, $id);
        $this->assertSame(1, $this->rowCount(7, '/about'));
    }

    public function test_repeat_call_returns_same_id(): void
    {
        $id1 = RecordFactory::resourceUri()->findOrCreate(7, '/about');
        $id2 = RecordFactory::resourceUri()->findOrCreate(7, '/about');

        $this->assertSame($id1, $id2);
        $this->assertSame(1, $this->rowCount(7, '/about'));
    }

    public function test_different_uri_creates_different_row(): void
    {
        $id1 = RecordFactory::resourceUri()->findOrCreate(7, '/about');
        $id2 = RecordFactory::resourceUri()->findOrCreate(7, '/contact');

        $this->assertNotSame($id1, $id2);
        $this->assertSame(2, (int) $GLOBALS['wpdb']->get_var("SELECT COUNT(*) FROM {$this->table}"));
    }
}
