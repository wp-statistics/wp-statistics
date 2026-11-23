<?php

use WP_Statistics\Components\DateTime;
use WP_Statistics\Service\Admin\Leaderboard\LeaderboardDataProvider;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;
use WP_STATISTICS\DB;

/**
 * Top Authors / Top Categories leaderboards: views per post divides by the posts that got
 * views in the range (not the posts published in it), and same-name terms get a tag.
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

    public function test_authors_views_per_post_uses_posts_with_views()
    {
        $anna  = self::factory()->user->create(['role' => 'author', 'display_name' => 'Anna']);
        $bruno = self::factory()->user->create(['role' => 'author', 'display_name' => 'Bruno']);

        $a1 = $this->oldPost($anna);
        $a2 = $this->oldPost($anna);
        $this->oldPost($anna); // no views: not in the divisor
        $b1 = $this->oldPost($bruno);

        $this->addViews($a1, 150);
        $this->addViews($a2, 46);
        $this->addViews($b1, 30);
        $this->addViews($b1, 400, DateTime::get('-60 days')); // outside the range

        $data = LeaderboardDataProvider::authors(['date' => $this->range(), 'post_type' => 'post']);
        $rows = $data['rows'];

        $this->assertCount(2, $rows);
        $this->assertSame($anna, $rows[0]['id']);
        $this->assertSame(196, $rows[0]['views']);
        $this->assertSame(2, $rows[0]['posts_with_views']);
        $this->assertSame(98.0, $rows[0]['views_per_post']);
        $this->assertSame(0, $rows[0]['published'], 'Nothing was published in the range');

        $this->assertSame(30, $rows[1]['views']);
        $this->assertSame(30.0, $rows[1]['views_per_post']);

        // Nothing published in the range: the column hides, the note still says 0.
        $this->assertFalse($data['columns']['published']);
        $this->assertSame(0, $data['published_total']);
        $this->assertFalse($data['columns']['comments'], 'No comments in the range: column hidden');
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
        $this->assertSame(25.0, $rows[0]['views_per_post']);
        $this->assertSame(['type' => 'slug', 'text' => 'blog-de'], $rows[0]['tag']);
        $this->assertSame(['type' => 'slug', 'text' => 'blog'], $rows[1]['tag']);
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
