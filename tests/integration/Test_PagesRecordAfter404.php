<?php

use WP_STATISTICS\DB;
use WP_STATISTICS\Pages;
use WP_STATISTICS\TimeZone;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;

/**
 * #1256: the first view of a URI today was a 404 (the post was a draft), so the (date, uri) row was
 * created as type 404, id 0. Once the post was published, its views were added to that 404 row and
 * the post showed no views for the rest of the day.
 */
class Test_PagesRecordAfter404 extends WP_UnitTestCase
{
    private $uri = '/sample-post-1256/';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        TableHandler::createTable('pages', Manager::getSchemaForTable('pages'));
    }

    public function setUp(): void
    {
        parent::setUp();
        global $wpdb;
        $wpdb->query('DELETE FROM `' . DB::table('pages') . '`');
        $_SERVER['REQUEST_URI'] = $this->uri;
    }

    private function hit($type, $id)
    {
        $profile = new class(['type' => $type, 'id' => $id, 'search_query' => '']) {
            private $page;
            public function __construct($page) { $this->page = $page; }
            public function getCurrentPageType() { return $this->page; }
        };

        return Pages::record($profile);
    }

    private function rows()
    {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            'SELECT `type`, `id`, `count` FROM `' . DB::table('pages') . '` WHERE `date` = %s AND `uri` = %s',
            TimeZone::getCurrentDate('Y-m-d'),
            $this->uri
        ), ARRAY_A);
    }

    public function test_views_after_a_404_go_to_the_post()
    {
        $postId = self::factory()->post->create();

        $this->hit('404', 0);
        $this->hit('post', $postId);
        $this->hit('post', $postId);
        $this->hit('post', $postId);

        $rows = $this->rows();
        $this->assertCount(1, $rows);
        $this->assertSame('post', $rows[0]['type']);
        $this->assertSame($postId, (int)$rows[0]['id']);
        $this->assertSame(4, (int)$rows[0]['count']);
    }

    public function test_a_404_on_a_post_row_does_not_turn_it_into_a_404()
    {
        $postId = self::factory()->post->create();

        $this->hit('post', $postId);
        $this->hit('404', 0);

        $rows = $this->rows();
        $this->assertCount(1, $rows);
        $this->assertSame('post', $rows[0]['type']);
        $this->assertSame($postId, (int)$rows[0]['id']);
        $this->assertSame(2, (int)$rows[0]['count']);
    }

    public function test_two_404_hits_stay_one_404_row()
    {
        $this->hit('404', 0);
        $this->hit('404', 0);

        $rows = $this->rows();
        $this->assertCount(1, $rows);
        $this->assertSame('404', $rows[0]['type']);
        $this->assertSame(2, (int)$rows[0]['count']);
    }
}
