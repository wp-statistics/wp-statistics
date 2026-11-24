<?php

use WP_Statistics\Components\DateTime;
use WP_Statistics\Service\Admin\Leaderboard\LeaderboardDataProvider;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;
use WP_STATISTICS\DB;

/**
 * Top Authors / Top Categories leaderboards: the same numbers as the Authors and Categories
 * reports (Posts = published in the range, Per post = views / Posts), one server ranking per
 * sortable metric, and same-name terms get a tag.
 */
class Test_LeaderboardData extends WP_UnitTestCase
{
    private static $tables = ['pages'];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        foreach (self::$tables as $table) {
            TableHandler::createTable($table, Manager::getSchemaForTable($table));
        }
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->emptyTables();
    }

    public function tearDown(): void
    {
        $this->emptyTables();
        parent::tearDown();
    }

    private function emptyTables()
    {
        global $wpdb;

        foreach (self::$tables as $table) {
            $wpdb->query('DELETE FROM `' . DB::table($table) . '`');
        }
    }

    private function range()
    {
        return ['from' => DateTime::get('-7 days'), 'to' => DateTime::get()];
    }

    private function addViews($postId, $count, $date = null)
    {
        global $wpdb;

        $wpdb->insert(DB::table('pages'), [
            'uri'   => '/?p=' . $postId,
            'type'  => 'post',
            'date'  => $date ?: DateTime::get(),
            'count' => $count,
            'id'    => $postId,
        ]);
    }

    private function oldPost($author, $args = [])
    {
        // Published long before the range: the old widget divided by 0 posts here.
        return self::factory()->post->create(array_merge([
            'post_author' => $author,
            'post_date'   => '2020-01-15 10:00:00',
            'post_status' => 'publish',
        ], $args));
    }

    public function test_authors_numbers_match_the_authors_report()
    {
        $anna  = self::factory()->user->create(['role' => 'author', 'display_name' => 'Anna']);
        $bruno = self::factory()->user->create(['role' => 'author', 'display_name' => 'Bruno']);

        $a1 = $this->oldPost($anna);
        $a2 = $this->oldPost($anna);
        $this->oldPost($anna); // no views
        $b1 = $this->oldPost($bruno);
        $b2 = self::factory()->post->create(['post_author' => $bruno, 'post_date' => DateTime::get('-2 days') . ' 10:00:00']);

        $this->addViews($a1, 150);
        $this->addViews($a2, 46);
        $this->addViews($b1, 30);
        $this->addViews($b1, 400, DateTime::get('-60 days')); // outside the range
        $this->addViews($b2, 10);

        $data = LeaderboardDataProvider::authors(['date' => $this->range(), 'post_type' => 'post']);
        $rows = $data['rows'];

        $this->assertCount(2, $rows);
        $this->assertSame($anna, $rows[0]['id']);
        $this->assertSame(196, $rows[0]['views']);
        $this->assertSame(0, $rows[0]['published'], 'Nothing was published in the range: Posts 0, like the report');
        $this->assertNull($rows[0]['views_per_post']);

        $this->assertSame($bruno, $rows[1]['id']);
        $this->assertSame(40, $rows[1]['views']);
        $this->assertSame(1, $rows[1]['published']);
        $this->assertSame(40.0, $rows[1]['views_per_post'], 'views / posts published in the range');

        $this->assertTrue($data['columns']['published']);
        $this->assertFalse($data['columns']['posts_with_views']);
        $this->assertSame(1, $data['published_total']);
        $this->assertFalse($data['columns']['comments'], 'No comments in the range: column hidden');
    }

    public function test_authors_are_ranked_on_the_server_per_metric()
    {
        // Owner test 14.17: five authors with more views and no new posts push admin out of the
        // views top 5, though admin published the most this year. Sorting by Posts must show admin.
        $range  = $this->range();
        $admin  = self::factory()->user->create(['role' => 'administrator', 'display_name' => 'admin']);
        $others = [];

        for ($i = 0; $i < 5; $i++) {
            $others[$i] = self::factory()->user->create(['role' => 'author', 'display_name' => 'Author ' . $i]);
            $this->addViews($this->oldPost($others[$i]), 500 + $i);
        }

        for ($i = 0; $i < 3; $i++) {
            $post = self::factory()->post->create(['post_author' => $admin, 'post_date' => DateTime::get('-1 days') . ' 10:00:00']);
            $this->addViews($post, 20);
        }

        $data = LeaderboardDataProvider::authors(['date' => $range, 'post_type' => 'post', 'per_page' => 5]);

        $viewIds = array_column($data['rankings']['views'], 'id');
        $this->assertCount(5, $viewIds);
        $this->assertNotContains($admin, $viewIds, 'admin has the fewest views');
        $this->assertSame($data['rows'], $data['rankings']['views']);

        $byPosts = $data['rankings']['published'];
        $this->assertSame($admin, $byPosts[0]['id'], 'Posts ranking is re-ranked on the server, not the views rows re-ordered');
        $this->assertSame(3, $byPosts[0]['published']);
        $this->assertSame(60, $byPosts[0]['views']);
        $this->assertSame(20.0, $byPosts[0]['views_per_post']);
        $this->assertCount(5, $byPosts);

        $byAverage = $data['rankings']['views_per_post'];
        $this->assertSame($admin, $byAverage[0]['id']);
        $this->assertCount(5, $byAverage, 'Authors with no posts in the range come after, by views');
        $this->assertSame($others[4], $byAverage[1]['id']);
    }

    public function test_authors_with_a_post_but_no_views_still_rank_by_posts()
    {
        $quiet = self::factory()->user->create(['role' => 'author', 'display_name' => 'Quiet']);
        self::factory()->post->create(['post_author' => $quiet, 'post_date' => DateTime::get('-1 days') . ' 10:00:00']);

        $data = LeaderboardDataProvider::authors(['date' => $this->range(), 'post_type' => 'post']);

        $this->assertSame($quiet, $data['rankings']['published'][0]['id']);
        $this->assertSame(0, $data['rankings']['published'][0]['views']);
        $this->assertSame(0.0, $data['rankings']['published'][0]['views_per_post']);
    }

    public function test_authors_are_ranked_low_to_high_among_active_authors()
    {
        // Second click on a header: the bottom of the ranking, but only authors with views or a
        // published post in the range, so authors who did nothing never fill the list.
        $idle   = self::factory()->user->create(['role' => 'author', 'display_name' => 'Idle']);
        $authors = [];

        $this->oldPost($idle); // published long ago, no views in the range

        foreach ([300, 40, 120, 5, 80, 200] as $i => $views) {
            $authors[$views] = self::factory()->user->create(['role' => 'author', 'display_name' => 'Author ' . $i]);
            $this->addViews($this->oldPost($authors[$views]), $views);
        }

        // One fresh post each for two authors: Per post and Posts get real values.
        $this->addViews(self::factory()->post->create(['post_author' => $authors[40], 'post_date' => DateTime::get('-1 days') . ' 10:00:00']), 2);
        $this->addViews(self::factory()->post->create(['post_author' => $authors[300], 'post_date' => DateTime::get('-1 days') . ' 10:00:00']), 1);

        $data = LeaderboardDataProvider::authors(['date' => $this->range(), 'post_type' => 'post', 'per_page' => 5]);

        $this->assertArrayHasKey('rankings_asc', $data);
        $this->assertSame(['views', 'published', 'views_per_post', 'comments'], array_keys($data['rankings_asc']));

        $ascViews = array_column($data['rankings_asc']['views'], 'views');
        $this->assertSame([5, 42, 80, 120, 200], $ascViews, 'lowest views first');
        $this->assertNotContains($idle, array_column($data['rankings_asc']['views'], 'id'), 'no views and nothing published: not ranked');

        $descViews = array_column($data['rankings']['views'], 'views');
        $this->assertSame([301, 200, 120, 80, 42], $descViews);

        // Posts low to high: authors with 0 posts published in the range first, ties by fewest views.
        $ascPublished = $data['rankings_asc']['published'];
        $this->assertSame(0, $ascPublished[0]['published']);
        $this->assertSame($authors[5], $ascPublished[0]['id']);

        // Per post low to high: no posts in the range counts as 0 (what the table shows), then real averages.
        $ascAverage = $data['rankings_asc']['views_per_post'];
        $this->assertNull($ascAverage[0]['views_per_post']);
        $this->assertCount(5, $ascAverage);
        $this->assertSame([301.0, 42.0], array_values(array_filter(array_column($data['rankings']['views_per_post'], 'views_per_post'))), 'high to low, real averages first');
    }

    public function test_authors_rank_by_comments_both_ways()
    {
        $chatty = self::factory()->user->create(['role' => 'author', 'display_name' => 'Chatty']);
        $quiet  = self::factory()->user->create(['role' => 'author', 'display_name' => 'Quiet']);

        $post = $this->oldPost($chatty);
        $this->addViews($post, 5);
        $this->addViews($this->oldPost($quiet), 50);

        for ($i = 0; $i < 3; $i++) {
            self::factory()->comment->create(['comment_post_ID' => $post, 'comment_approved' => '1', 'comment_type' => 'comment', 'comment_date' => DateTime::get() . ' 09:00:00']);
        }

        $data = LeaderboardDataProvider::authors(['date' => $this->range(), 'post_type' => 'post']);

        $this->assertSame([$chatty, $quiet], array_column($data['rankings']['comments'], 'id'));
        $this->assertSame([3, 0], array_column($data['rankings']['comments'], 'comments'));
        $this->assertSame([$quiet, $chatty], array_column($data['rankings_asc']['comments'], 'id'));
        $this->assertTrue($data['columns']['comments']);
    }

    public function test_authors_published_and_comments_columns()
    {
        $anna = self::factory()->user->create(['role' => 'author', 'display_name' => 'Anna']);
        $new  = self::factory()->post->create(['post_author' => $anna, 'post_date' => DateTime::get('-2 days') . ' 10:00:00']);
        $this->addViews($new, 12);

        self::factory()->comment->create(['comment_post_ID' => $new, 'comment_approved' => '1', 'comment_type' => 'comment', 'comment_date' => DateTime::get() . ' 09:00:00']);
        self::factory()->comment->create(['comment_post_ID' => $new, 'comment_approved' => '0', 'comment_type' => 'comment', 'comment_date' => DateTime::get() . ' 09:00:00']);

        $data = LeaderboardDataProvider::authors(['date' => $this->range(), 'post_type' => 'post']);

        $this->assertSame(1, $data['rows'][0]['published']);
        $this->assertSame(1, $data['rows'][0]['comments'], 'Only approved comments count');
        $this->assertTrue($data['columns']['published']);
        $this->assertTrue($data['columns']['comments']);
        $this->assertSame(1, $data['published_total']);
    }

    public function test_terms_with_the_same_name_get_their_slug()
    {
        $author = self::factory()->user->create(['role' => 'author']);
        $blog   = self::factory()->category->create(['name' => 'Blog', 'slug' => 'blog']);
        $blogDe = self::factory()->category->create(['name' => 'Blog', 'slug' => 'blog-de']);

        $p1 = $this->oldPost($author, ['post_category' => [$blogDe]]);
        $p2 = $this->oldPost($author, ['post_category' => [$blogDe]]);
        $p3 = $this->oldPost($author, ['post_category' => [$blog]]);

        $this->addViews($p1, 30);
        $this->addViews($p2, 20);
        $this->addViews($p3, 19);

        $data = LeaderboardDataProvider::terms(['date' => $this->range(), 'taxonomy' => 'category', 'post_type' => 'post']);
        $rows = $data['rows'];

        $this->assertCount(2, $rows);
        $this->assertSame($blogDe, $rows[0]['id']);
        $this->assertSame(50, $rows[0]['views']);
        $this->assertSame(2, $rows[0]['posts_with_views']);
        $this->assertSame(0, $rows[0]['published'], 'Old posts: nothing published in the range');
        $this->assertNull($rows[0]['views_per_post']);
        $this->assertSame(['type' => 'slug', 'text' => 'blog-de'], $rows[0]['tag']);
        $this->assertSame(['type' => 'slug', 'text' => 'blog'], $rows[1]['tag']);
    }

    public function test_terms_are_ranked_on_the_server_per_metric()
    {
        $author = self::factory()->user->create(['role' => 'author']);
        $big    = self::factory()->category->create(['name' => 'Big']);
        $fresh  = self::factory()->category->create(['name' => 'Fresh']);

        $this->addViews($this->oldPost($author, ['post_category' => [$big]]), 300);
        $p = self::factory()->post->create(['post_author' => $author, 'post_category' => [$fresh], 'post_date' => DateTime::get('-1 days') . ' 10:00:00']);
        $this->addViews($p, 12);

        $data = LeaderboardDataProvider::terms(['date' => $this->range(), 'taxonomy' => 'category', 'post_type' => 'post', 'per_page' => 1]);

        $this->assertSame($big, $data['rankings']['views'][0]['id']);
        $this->assertSame($fresh, $data['rankings']['published'][0]['id']);
        $this->assertSame(1, $data['rankings']['published'][0]['published']);
        $this->assertSame(12.0, $data['rankings']['views_per_post'][0]['views_per_post']);
    }

    public function test_terms_are_ranked_low_to_high_among_active_terms()
    {
        $author = self::factory()->user->create(['role' => 'author']);
        $big    = self::factory()->category->create(['name' => 'Big']);
        $small  = self::factory()->category->create(['name' => 'Small']);
        $unused = self::factory()->category->create(['name' => 'Unused']);

        $this->addViews($this->oldPost($author, ['post_category' => [$big]]), 300);
        $this->addViews($this->oldPost($author, ['post_category' => [$small]]), 7);
        $this->oldPost($author, ['post_category' => [$unused]]);

        $data = LeaderboardDataProvider::terms(['date' => $this->range(), 'taxonomy' => 'category', 'post_type' => 'post']);

        $this->assertSame([$small, $big], array_column($data['rankings_asc']['views'], 'id'));
        $this->assertSame([$big, $small], array_column($data['rankings']['views'], 'id'));
        $this->assertNotContains($unused, array_column($data['rankings_asc']['published'], 'id'), 'unused terms stay out of the low-to-high list');
        $this->assertSame($small, $data['rankings_asc']['published'][0]['id'], 'ties fall back to views, low to high');
    }

    public function test_terms_with_the_same_name_prefer_language_then_parent()
    {
        $news   = self::factory()->category->create(['name' => 'News']);
        $about  = self::factory()->category->create(['name' => 'About']);
        $blogA  = self::factory()->category->create(['name' => 'Blog', 'parent' => $news]);
        $blogB  = self::factory()->category->create(['name' => 'Blog', 'parent' => $about]);
        $author = self::factory()->user->create(['role' => 'author']);

        $this->addViews($this->oldPost($author, ['post_category' => [$blogA]]), 5);
        $this->addViews($this->oldPost($author, ['post_category' => [$blogB]]), 3);

        $data = LeaderboardDataProvider::terms(['date' => $this->range(), 'taxonomy' => 'category', 'post_type' => 'post']);
        $this->assertSame(['type' => 'parent', 'text' => 'News'], $data['rows'][0]['tag']);
        $this->assertSame(['type' => 'parent', 'text' => 'About'], $data['rows'][1]['tag']);

        // A multilingual plugin's language wins over the parent.
        $filter = function ($code, $termId) use ($blogA) {
            return $termId === $blogA ? 'de' : 'en';
        };
        add_filter('wp_statistics_leaderboard_term_language', $filter, 10, 2);
        $data = LeaderboardDataProvider::terms(['date' => $this->range(), 'taxonomy' => 'category', 'post_type' => 'post']);
        remove_filter('wp_statistics_leaderboard_term_language', $filter, 10);

        $this->assertSame(['type' => 'lang', 'text' => 'DE'], $data['rows'][0]['tag']);
        $this->assertSame(['type' => 'lang', 'text' => 'EN'], $data['rows'][1]['tag']);
    }

    public function test_empty_id_lists_count_nothing()
    {
        $this->assertSame([], (new \WP_Statistics\Models\AuthorsModel())->countPublishedByAuthors([], ['date' => $this->range()]));
        $this->assertSame([], (new \WP_Statistics\Models\TaxonomyModel())->countPublishedByTerms([], ['date' => $this->range()]));
        $this->assertSame([], (new \WP_Statistics\Models\TaxonomyModel())->countCommentsByTerms([], ['date' => $this->range()]));
    }
}
