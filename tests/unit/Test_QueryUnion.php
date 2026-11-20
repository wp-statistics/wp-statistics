<?php

namespace WP_Statistics\Tests\Utils;

use WP_Statistics\Utils\Query;
use WP_UnitTestCase;

class Test_QueryUnion extends WP_UnitTestCase
{
    /**
     * Plain parts are joined with UNION without parentheses (SQLite rejects
     * "(SELECT ...) UNION (SELECT ...)"), and the outer ORDER BY and LIMIT stay.
     */
    public function test_union_of_plain_parts_has_no_parentheses()
    {
        $sql = Query::union([
            'SELECT id, uri FROM wp_statistics_pages WHERE id != 0 GROUP BY id',
            'SELECT id, uri FROM wp_statistics_pages WHERE id = 0 GROUP BY uri',
        ])
            ->orderBy('id', 'DESC')
            ->perPage(2, 10)
            ->getQuery();

        $beforeUnion = substr($sql, 0, strpos($sql, ' UNION '));

        $this->assertStringNotContainsString('(SELECT', $beforeUnion);
        $this->assertStringNotContainsString('(SELECT', $sql);
        $this->assertStringNotContainsString('union_part_', $sql);
        $this->assertStringStartsWith('SELECT id, uri FROM wp_statistics_pages WHERE id != 0 GROUP BY id UNION SELECT', $sql);
        $this->assertMatchesRegularExpression('/ UNION SELECT id, uri FROM wp_statistics_pages WHERE id = 0 GROUP BY uri ORDER BY .*id.* DESC LIMIT 10 OFFSET 10$/', $sql);
    }

    /**
     * Building the same union twice must give the same SQL (parts are not rewritten in place).
     */
    public function test_union_is_the_same_when_built_twice()
    {
        $query = Query::union([
            'SELECT id FROM wp_statistics_pages WHERE id != 0',
            'SELECT id FROM wp_statistics_pages WHERE id = 0',
        ])->orderBy('id');

        $this->assertSame($query->getQuery(), $query->getQuery());
    }

    /**
     * A part with its own ORDER BY or LIMIT is wrapped as a derived table, as both engines require.
     */
    public function test_part_with_its_own_limit_is_wrapped()
    {
        $query = Query::union([
            'SELECT id FROM wp_statistics_pages WHERE id != 0',
            'SELECT id FROM wp_statistics_pages ORDER BY id LIMIT 5',
        ]);

        $sql = $query->getQuery();

        $this->assertSame(
            'SELECT id FROM wp_statistics_pages WHERE id != 0 UNION SELECT * FROM (SELECT id FROM wp_statistics_pages ORDER BY id LIMIT 5) AS union_part_1',
            $sql
        );
        $this->assertSame($sql, $query->getQuery());
    }
}
