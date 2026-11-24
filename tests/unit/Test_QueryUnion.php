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

    /**
     * ORDER BY or LIMIT inside a sub query or a quoted value is not the part's own clause.
     */
    public function test_order_by_inside_sub_query_or_literal_does_not_wrap()
    {
        $sql = Query::union([
            "SELECT id FROM wp_statistics_pages WHERE id IN (SELECT id FROM wp_statistics_pages ORDER BY id LIMIT 5)",
            "SELECT id FROM wp_statistics_pages WHERE uri = '/order by limit/'",
        ])->getQuery();

        $this->assertStringNotContainsString('union_part_', $sql);
    }

    /**
     * Query parts are wrapped only when orderBy() or perPage() was called on them.
     */
    public function test_query_parts_wrap_by_flag()
    {
        $plain   = Query::select('id')->from('pages')->where('id', '!=', '0')->groupBy('id');
        $limited = Query::select('id')->from('pages')->where('id', '=', '0')->orderBy('id')->perPage(1, 5);

        $this->assertFalse($plain->hasOrderOrLimit());
        $this->assertTrue($limited->hasOrderOrLimit());

        $sql = Query::union([$plain, $limited])->orderBy('id')->getQuery();

        $this->assertStringNotContainsString('union_part_0', $sql);
        $this->assertStringContainsString('SELECT * FROM (SELECT id FROM', $sql);
        $this->assertStringContainsString(') AS union_part_1', $sql);
        $this->assertStringStartsWith('SELECT id FROM', $sql);
    }
}
