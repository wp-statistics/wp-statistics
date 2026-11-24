<?php

use WP_Statistics\Service\Admin\Leaderboard\Leaderboard;
use WP_Statistics\Service\Admin\Leaderboard\LeaderboardPresenter;

/**
 * S4-B leaderboard helpers: per-post maths, optional columns, same-name tags, initials.
 */
class Test_Leaderboard extends WP_UnitTestCase
{
    public function test_per_post_divides_by_posts_published_in_the_range()
    {
        // Same as the Authors report: views / posts published in the range.
        $this->assertSame(30.9, Leaderboard::perPost(278, 9));
        $this->assertSame(26.3, Leaderboard::perPost(105, 4));
        $this->assertSame(31.2, Leaderboard::perPost(718, 23));
    }

    public function test_per_post_is_null_without_published_posts()
    {
        $this->assertNull(Leaderboard::perPost(10, 0));
        $this->assertNull(Leaderboard::perPost(0, 0));
    }

    public function test_build_rows_reads_published_from_the_ranked_row()
    {
        // Owner test 14.17: admin published 9 posts this year (278 views); Anastasia none (1,454 views).
        $ranked = [
            (object) ['id' => 1, 'name' => 'admin', 'views' => '278', 'posts_with_views' => '6', 'published' => '9'],
            (object) ['id' => 9, 'name' => 'Anastasia', 'views' => '1454', 'posts_with_views' => '4', 'published' => '0'],
        ];

        $rows = Leaderboard::buildRows($ranked, ['id' => 'id', 'name' => 'name'], [], []);

        $this->assertCount(2, $rows);
        $this->assertSame(9, $rows[0]['published']);
        $this->assertSame(30.9, $rows[0]['views_per_post']);
        $this->assertSame(0, $rows[1]['published'], 'Posts = published in the range, not posts with views');
        $this->assertNull($rows[1]['views_per_post']);
        $this->assertSame(4, $rows[1]['posts_with_views']);
    }

    public function test_build_rows_reads_side_counts_by_count_key()
    {
        // Older rows without a published field: the side map by count key still works.
        $ranked = [(object) ['term_id' => 50, 'term_taxonomy_id' => 70, 'term_name' => 'Blog', 'views' => 19, 'posts_with_views' => 2]];

        $rows = Leaderboard::buildRows($ranked, ['id' => 'term_id', 'name' => 'term_name', 'count_key' => 'term_taxonomy_id'], [70 => 3], [70 => 5]);

        $this->assertSame(50, $rows[0]['id']);
        $this->assertSame(3, $rows[0]['published']);
        $this->assertSame(5, $rows[0]['comments']);
        $this->assertSame(6.3, $rows[0]['views_per_post']);
    }

    public function test_columns_match_the_authors_report()
    {
        $rows = [
            ['published' => 0, 'comments' => 0],
            ['published' => 0, 'comments' => 0],
        ];

        $columns = Leaderboard::columns($rows, ['comments' => true]);
        $this->assertTrue($columns['views']);
        $this->assertTrue($columns['published'], 'Posts (published in the range) always shows, like the report');
        $this->assertTrue($columns['views_per_post']);
        $this->assertFalse($columns['posts_with_views']);
        $this->assertFalse($columns['comments'], 'Nobody commented: hidden');

        $rows[1]['comments'] = 2;
        $this->assertTrue(Leaderboard::columns($rows, ['comments' => true])['comments']);

        // Comments off for the post type: hidden even with values.
        $this->assertFalse(Leaderboard::columns($rows, ['comments' => false])['comments']);
    }

    public function test_leaderboard_view_draws_one_ranking_per_sortable_metric()
    {
        $row = function ($id, $name, $views, $published) {
            return ['id' => $id, 'name' => $name, 'views' => $views, 'posts_with_views' => 1, 'published' => $published, 'views_per_post' => Leaderboard::perPost($views, $published), 'comments' => 0, 'tag' => null];
        };

        $html = \WP_Statistics\Components\View::load('components/leaderboard', [
            'rankings' => [
                'views'          => [$row(9, 'Anastasia', 1454, 0), $row(8, 'Floyd', 1148, 0)],
                'published'      => [$row(1, 'admin', 278, 9), $row(9, 'Anastasia', 1454, 0)],
                'views_per_post' => [$row(1, 'admin', 278, 9)],
            ],
            'columns'  => Leaderboard::columns([]),
            'caption'  => 'Top Authors',
        ], true);

        // A plain metric => rows array is read as the high-to-low lists.
        $this->assertStringContainsString('<tbody data-ranking="views" data-direction="descending">', $html);
        $this->assertStringContainsString('<tbody data-ranking="published" data-direction="descending" hidden>', $html);
        $this->assertStringContainsString('<tbody data-ranking="views_per_post" data-direction="descending" hidden>', $html);
        $this->assertStringNotContainsString('data-direction="ascending"', $html);
        $this->assertStringContainsString('data-sort-key="published"', $html);
        $this->assertStringContainsString('Posts published in this range', $html);
        $this->assertStringNotContainsString('data-sort-key="posts_with_views"', $html);
        // admin is only in the Posts and Per post rankings, as rank 1.
        $this->assertMatchesRegularExpression('#data-ranking="published" data-direction="descending" hidden>\s*<tr data-rank="1"[^>]*data-published="9"#', $html);
    }

    public function test_leaderboard_view_draws_both_directions_and_a_sortable_comments_column()
    {
        $row = function ($id, $name, $views, $published, $comments = 0) {
            return ['id' => $id, 'name' => $name, 'views' => $views, 'posts_with_views' => 1, 'published' => $published, 'views_per_post' => Leaderboard::perPost($views, $published), 'comments' => $comments, 'tag' => null];
        };

        $top    = [$row(9, 'Anastasia', 1454, 2, 4), $row(8, 'Floyd', 1148, 1, 1)];
        $bottom = [$row(8, 'Floyd', 1148, 1, 1), $row(9, 'Anastasia', 1454, 2, 4)];

        $html = \WP_Statistics\Components\View::load('components/leaderboard', [
            'rankings' => [
                'descending' => ['views' => $top, 'published' => $top, 'views_per_post' => $top, 'comments' => $top],
                'ascending'  => ['views' => $bottom, 'published' => $bottom, 'views_per_post' => $bottom, 'comments' => $bottom],
            ],
            'columns'  => Leaderboard::columns($top, ['comments' => true]),
            'caption'  => 'Top Authors',
        ], true);

        $this->assertStringContainsString('<tbody data-ranking="views" data-direction="descending">', $html);
        $this->assertStringContainsString('<tbody data-ranking="views" data-direction="ascending" hidden>', $html);
        $this->assertStringContainsString('<tbody data-ranking="comments" data-direction="ascending" hidden>', $html);
        $this->assertSame(8, substr_count($html, '<tbody '), 'four metrics, two directions');
        $this->assertStringContainsString('data-sort-key="comments"', $html, 'Comments has its own ranking, so it is sortable');
        $this->assertMatchesRegularExpression('#data-ranking="views" data-direction="ascending" hidden>\s*<tr data-rank="1"[^>]*data-views="1148"#', $html);
    }

    public function test_presenter_passes_both_directions_to_the_view()
    {
        $rows   = [['id' => 3, 'name' => 'Cat', 'views' => 1, 'published' => 0, 'views_per_post' => null, 'comments' => 0, 'tag' => null]];
        $result = \WP_Statistics\Service\Admin\Leaderboard\LeaderboardPresenter::termRankings([
            'rows'         => $rows,
            'rankings'     => ['views' => $rows],
            'rankings_asc' => ['views' => $rows],
        ]);

        $this->assertSame(['descending', 'ascending'], array_keys($result));
        $this->assertNotEmpty($result['ascending']['views'][0]['url']);

        // Older results: high to low only.
        $this->assertSame(['descending'], array_keys(\WP_Statistics\Service\Admin\Leaderboard\LeaderboardPresenter::termRankings(['rows' => $rows])));
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

    /**
     * An image attachment with a made-up file and a 150x150 "thumbnail" size in its metadata.
     */
    private function imageAttachment($postId)
    {
        $attachmentId = self::factory()->attachment->create_object([
            'file'           => '2026/11/cover.jpg',
            'post_parent'    => $postId,
            'post_mime_type' => 'image/jpeg',
            'post_title'     => 'Cover',
        ]);

        wp_update_attachment_metadata($attachmentId, [
            'width'  => 800,
            'height' => 600,
            'file'   => '2026/11/cover.jpg',
            'sizes'  => [
                'thumbnail' => ['file' => 'cover-150x150.jpg', 'width' => 150, 'height' => 150, 'mime-type' => 'image/jpeg'],
            ],
        ]);

        return $attachmentId;
    }

    public function test_content_items_use_the_wordpress_thumbnail_size_and_fall_back_to_initials()
    {
        $withImage = self::factory()->post->create(['post_title' => 'Quick Dinners']);
        $noImage   = self::factory()->post->create(['post_title' => 'Slow Evenings']);
        set_post_thumbnail($withImage, $this->imageAttachment($withImage));

        $posts = [
            (object) ['ID' => $withImage, 'post_title' => 'Quick Dinners', 'views' => 12],
            (object) ['ID' => $noImage, 'post_title' => 'Slow Evenings', 'views' => 3],
        ];
        $items = LeaderboardPresenter::contentItems($posts, 'views', function () {
            return 'https://example.test/report';
        });

        $this->assertStringEndsWith('/2026/11/cover-150x150.jpg', $items[0]['thumb'], 'WordPress\'s own "thumbnail" size, no new size');
        $this->assertSame('', $items[1]['thumb']);
        $this->assertSame('SE', $items[1]['initials']);

        $html = \WP_Statistics\Components\View::load('components/content-rank-list', [
            'id'    => 'test-rank',
            'title' => 'Top Posts',
            'lists' => [['key' => 'views', 'label' => 'Most viewed', 'items' => $items]],
        ], true);

        $this->assertMatchesRegularExpression('#<img class="wps-post-thumb wps-rank-list__thumb" src="[^"]+cover-150x150\.jpg" alt="" width="40" height="40" loading="lazy" decoding="async">#', $html);
        $this->assertSame(1, substr_count($html, '<img'), 'only the post with a featured image gets one');
        $this->assertStringContainsString('wps-post-thumb--tile', $html);
        $this->assertStringContainsString('>SE</span>', $html, 'the other post keeps its initials tile');
    }

    public function test_content_items_fall_back_when_the_featured_image_is_gone()
    {
        $postId       = self::factory()->post->create(['post_title' => 'Gone Image']);
        $attachmentId = $this->imageAttachment($postId);
        set_post_thumbnail($postId, $attachmentId);
        wp_delete_post($attachmentId, true);

        $items = LeaderboardPresenter::contentItems([(object) ['ID' => $postId, 'post_title' => 'Gone Image', 'views' => 1]], 'views', function () {
            return '';
        });

        $this->assertSame('', $items[0]['thumb']);
        $this->assertSame('GI', $items[0]['initials']);
    }
}
