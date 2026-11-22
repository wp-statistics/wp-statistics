<?php

use WP_Statistics\Service\Admin\Leaderboard\Leaderboard;
use WP_Statistics\Service\Admin\Leaderboard\LeaderboardPresenter;

/**
 * S4-B leaderboard helpers: per-post maths, optional columns, same-name tags, initials.
 */
class Test_Leaderboard extends WP_UnitTestCase
{
    public function test_per_post_divides_by_posts_that_got_views()
    {
        // Real data from the design notes: Anastasia 196 views over 4 posts with views.
        $this->assertSame(49.0, Leaderboard::perPost(196, 4));
        $this->assertSame(26.3, Leaderboard::perPost(105, 4));
        $this->assertSame(31.2, Leaderboard::perPost(718, 23));
    }

    public function test_per_post_is_null_without_posts()
    {
        $this->assertNull(Leaderboard::perPost(10, 0));
        $this->assertNull(Leaderboard::perPost(0, 0));
    }

    public function test_build_rows_is_never_empty_when_nothing_was_published()
    {
        // Nothing published in the range: the old widget showed "No data" here.
        $ranked = [
            (object) ['id' => 9, 'name' => 'Anastasia', 'views' => '196', 'posts_with_views' => '4'],
            (object) ['id' => 8, 'name' => 'Floyd', 'views' => '156', 'posts_with_views' => '4'],
        ];

        $rows = Leaderboard::buildRows($ranked, ['id' => 'id', 'name' => 'name'], [], []);

        $this->assertCount(2, $rows);
        $this->assertSame(49.0, $rows[0]['views_per_post']);
        $this->assertSame(39.0, $rows[1]['views_per_post']);
        $this->assertSame(0, $rows[0]['published']);
    }

    public function test_build_rows_reads_side_counts_by_count_key()
    {
        $ranked = [(object) ['term_id' => 50, 'term_taxonomy_id' => 70, 'term_name' => 'Blog', 'views' => 19, 'posts_with_views' => 2]];

        $rows = Leaderboard::buildRows($ranked, ['id' => 'term_id', 'name' => 'term_name', 'count_key' => 'term_taxonomy_id'], [70 => 3], [70 => 5]);

        $this->assertSame(50, $rows[0]['id']);
        $this->assertSame(3, $rows[0]['published']);
        $this->assertSame(5, $rows[0]['comments']);
        $this->assertSame(9.5, $rows[0]['views_per_post']);
    }

    public function test_optional_columns_hide_when_all_zero()
    {
        $rows = [
            ['published' => 0, 'comments' => 0],
            ['published' => 0, 'comments' => 0],
        ];

        $columns = Leaderboard::columns($rows, ['published' => true, 'comments' => true]);
        $this->assertTrue($columns['views']);
        $this->assertTrue($columns['posts_with_views']);
        $this->assertTrue($columns['views_per_post']);
        $this->assertFalse($columns['published']);
        $this->assertFalse($columns['comments']);

        $rows[1]['comments'] = 2;
        $rows[0]['published'] = 1;
        $columns = Leaderboard::columns($rows, ['published' => true, 'comments' => true]);
        $this->assertTrue($columns['published']);
        $this->assertTrue($columns['comments']);

        // Comments off for the post type: hidden even with values.
        $columns = Leaderboard::columns($rows, ['published' => false, 'comments' => false]);
        $this->assertFalse($columns['published']);
        $this->assertFalse($columns['comments']);
    }

    public function test_same_name_terms_get_language_tag_first()
    {
        $rows = [
            ['name' => 'Blog', 'ctx' => ['lang' => 'DE', 'parent' => '', 'slug' => 'blog-de']],
            ['name' => 'blog ', 'ctx' => ['lang' => 'EN', 'parent' => '', 'slug' => 'blog']],
            ['name' => 'News', 'ctx' => ['lang' => 'EN', 'parent' => '', 'slug' => 'news']],
        ];

        $calls  = 0;
        $result = Leaderboard::disambiguate($rows, function ($row) use (&$calls) {
            $calls++;
            return $row['ctx'];
        });

        $this->assertSame(['type' => 'lang', 'text' => 'DE'], $result[0]['tag']);
        $this->assertSame(['type' => 'lang', 'text' => 'EN'], $result[1]['tag']);
        $this->assertArrayNotHasKey('tag', $result[2], 'A unique name gets no tag');
        $this->assertSame(2, $calls, 'Context is only looked up for colliding names');
    }

    public function test_same_name_falls_back_to_parent_then_slug()
    {
        // Same language for both: the parent tells them apart.
        $rows = [
            ['name' => 'Blog', 'ctx' => ['lang' => 'EN', 'parent' => 'News', 'slug' => 'blog-2']],
            ['name' => 'Blog', 'ctx' => ['lang' => 'EN', 'parent' => 'Company', 'slug' => 'blog']],
        ];
        $result = Leaderboard::disambiguate($rows, function ($row) {
            return $row['ctx'];
        });
        $this->assertSame('parent', $result[0]['tag']['type']);
        $this->assertSame('News', $result[0]['tag']['text']);

        // No multilingual plugin, both top level: the slug (the live site's two "Blog" rows).
        $rows = [
            ['name' => 'Blog', 'ctx' => ['slug' => 'blog-de']],
            ['name' => 'Blog', 'ctx' => ['slug' => 'blog']],
        ];
        $result = Leaderboard::disambiguate($rows, function ($row) {
            return $row['ctx'];
        });
        $this->assertSame(['type' => 'slug', 'text' => 'blog-de'], $result[0]['tag']);
        $this->assertSame(['type' => 'slug', 'text' => 'blog'], $result[1]['tag']);
    }

    public function test_same_name_with_one_language_missing_skips_language()
    {
        $rows = [
            ['name' => 'Blog', 'ctx' => ['lang' => 'DE', 'slug' => 'blog-de']],
            ['name' => 'Blog', 'ctx' => ['lang' => '', 'slug' => 'blog']],
        ];
        $result = Leaderboard::disambiguate($rows, function ($row) {
            return $row['ctx'];
        });
        $this->assertSame('slug', $result[0]['tag']['type']);
        $this->assertSame('slug', $result[1]['tag']['type']);
    }

    public function test_initials_and_colour()
    {
        $this->assertSame('A', Leaderboard::initials('anastasia homenick'));
        $this->assertSame('QD', Leaderboard::initials('Qui debitis tempore', 2));
        $this->assertSame('D', Leaderboard::initials('"Dolor" sit'));
        $this->assertSame('م', Leaderboard::initials('مصطفی'));
        $this->assertSame('#', Leaderboard::initials('  '));

        $this->assertSame(Leaderboard::color(9), Leaderboard::color(9), 'Stable per seed');
        $this->assertContains(Leaderboard::color('x'), Leaderboard::PALETTE);
    }

    public function test_average_format()
    {
        $this->assertSame('49', Leaderboard::formatAverage(49.0));
        $this->assertSame('26.3', Leaderboard::formatAverage(26.25));
        $this->assertSame('0', Leaderboard::formatAverage(null));
    }

    public function test_published_note_grammar()
    {
        $this->assertSame('0 posts published in this range', LeaderboardPresenter::publishedNote(0, 'post'));
        $this->assertSame('1 post published in this range', LeaderboardPresenter::publishedNote(1, ['post']));
        $this->assertSame('1 page published in this range', LeaderboardPresenter::publishedNote(1, 'page'));
        $this->assertSame('3 items published in this range', LeaderboardPresenter::publishedNote(3, ['post', 'page']));
    }

    public function test_metric_text_uses_plural_forms()
    {
        $this->assertSame('1 view', LeaderboardPresenter::metricText((object) ['views' => 1], 'views'));
        $this->assertSame('165 views', LeaderboardPresenter::metricText((object) ['views' => 165], 'views'));
        $this->assertSame('1 comment', LeaderboardPresenter::metricText((object) ['comments' => 1], 'comments'));
        $this->assertSame('1.23K views', LeaderboardPresenter::metricText((object) ['views' => 1234], 'views'));
    }
}
