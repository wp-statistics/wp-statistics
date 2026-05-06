<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;
use WP_Statistics\Utils\Query;

/**
 * Handles database interactions for the `resources` table.
 *
 * This class relies on BaseRecord for core database operations.
 *
 * @since 15.0.0
 */
class ResourceRecord extends BaseRecord
{
    /**
     * The current table name.
     *
     * @var string
     */
    protected $tableName = 'resources';

    /**
     * Marks the record as deleted (soft delete) by setting an is_deleted flag.
     *
     * @param array $args Optional additional fields to update.
     * @return void
     */
    public function markAsDeleted($args = [])
    {
        if (empty($this->record->ID)) {
            return;
        }

        $defaults = [
            'is_deleted' => 1,
        ];

        $args = $this->parseArgs($args, $defaults);

        Query::update($this->tableName)
            ->set($args)
            ->where('ID', '=', $this->record->ID)
            ->execute();
    }

    /**
     * Atomic upsert by the (resource_id, resource_type, language) identity tuple.
     *
     * Insert-or-find by identity, with forward-fill: when a row already exists
     * and its language column is empty, the new language is written; an existing
     * non-empty language is preserved (never overwritten). Both the find and the
     * conditional update happen in a single statement, so concurrent callers
     * cannot create duplicate rows.
     *
     * Forward-fill logic (only applies when $language is non-empty):
     *   - If a row for (resource_id, resource_type) exists with language = '', update
     *     its language column to $language and return that row's ID.
     *   - Otherwise, INSERT … ON DUPLICATE KEY UPDATE the (resource_id, resource_type,
     *     language) unique key, using LAST_INSERT_ID(ID) to expose the existing ID
     *     in the duplicate case.
     *
     * Requires the table to carry `UNIQUE KEY (resource_id, resource_type, language)`
     * and `language NOT NULL DEFAULT ''`.
     *
     * @param int    $resourceId   Logical resource ID (post ID, term ID, or 0 for home/archive).
     * @param string $resourceType Resource type ('post', 'page', 'home', 'search', term taxonomy, …).
     * @param string $language     Detected language code, or '' when no multilang adapter detected one.
     * @return int The resource row ID, or 0 on DB error.
     */
    public function upsertWithLanguageFill(int $resourceId, string $resourceType, string $language): int
    {
        global $wpdb;

        // Forward-fill step: when a real language is detected, promote any existing
        // empty-language row for this (resource_id, resource_type) pair.  The UPDATE
        // uses LAST_INSERT_ID() so the filled row's ID becomes available as insert_id.
        if ($language !== '') {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $filled = $wpdb->query($wpdb->prepare(
                "UPDATE `{$this->fullTableName}` "
                . "SET `language` = %s, `ID` = LAST_INSERT_ID(`ID`) "
                . "WHERE `resource_id` = %d AND `resource_type` = %s AND `language` = ''",
                $language,
                $resourceId,
                $resourceType
            ));

            if ($filled === false) {
                \WP_Statistics()->log('upsertWithLanguageFill UPDATE failed: ' . $wpdb->last_error);
                return 0;
            }

            if ($filled > 0) {
                // A row was forward-filled; LAST_INSERT_ID() now holds its ID.
                return (int) $wpdb->insert_id;
            }
        }

        // Standard atomic upsert: insert or find by exact (resource_id, resource_type, language).
        $sql = "INSERT INTO `{$this->fullTableName}` (`resource_id`, `resource_type`, `language`) "
             . "VALUES (%d, %s, %s) "
             . "ON DUPLICATE KEY UPDATE "
             . "`ID` = LAST_INSERT_ID(`ID`), "
             . "`language` = IF(`language` = '', VALUES(`language`), `language`)";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $result = $wpdb->query($wpdb->prepare($sql, $resourceId, $resourceType, $language));

        if ($result === false) {
            \WP_Statistics()->log('upsertWithLanguageFill failed: ' . $wpdb->last_error);
            return 0;
        }

        return (int) $wpdb->insert_id;
    }
}
