<?php

use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Models\TaxonomyModel;
use WP_Statistics\Service\Admin\Posts\PostsManager;
use WP_Statistics\Service\Database\Schema\Manager;
use WP_STATISTICS\DB;
use WP_STATISTICS\Pages;

class Test_Pages extends WP_UnitTestCase
{
    private $term;
    private $pagesTable;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        TableHandler::createTable('pages', Manager::getSchemaForTable('pages'));
    }

    public function setUp(): void
    {
        parent::setUp();

        global $wpdb;

        $this->pagesTable = DB::table('pages');
        $wpdb->query("DELETE FROM `{$this->pagesTable}`");

        $this->term = $this->createTermWithDifferentTaxonomyId();
    }

    public function tearDown(): void
    {
        global $wpdb;

        $wpdb->query("DELETE FROM `{$this->pagesTable}`");

        parent::tearDown();
    }

    public function test_get_page_info_uses_term_id_in_category_analytics_url()
    {
        $pageInfo = Pages::get_page_info($this->term->term_id, 'category');
        $query     = $this->getUrlQuery($pageInfo['report']);

        $this->assertSame((string)$this->term->term_id, $query['term_id']);
    }

    public function test_get_top_uses_term_id_in_category_analytics_url()
    {
        global $wpdb;

        $date = current_time('Y-m-d');
        $wpdb->insert(
            $this->pagesTable,
            [
                'uri'   => get_term_link($this->term->term_id),
                'type'  => 'category',
                'date'  => $date,
                'count' => 1,
                'id'    => $this->term->term_id,
            ]
        );

        $pages = Pages::getTop([
            'from' => $date,
            'to'   => $date,
            'type' => 'category',
        ]);
        $query = $this->getUrlQuery($pages[0]['hits_page']);

        $this->assertSame((string)$this->term->term_id, $query['term_id']);
    }

    public function test_taxonomy_data_uses_term_id_for_archive_views()
    {
        global $wpdb;

        $date   = current_time('Y-m-d');
        $postId = self::factory()->post->create(['post_status' => 'publish']);
        wp_set_post_terms($postId, [$this->term->term_id], 'category');

        $wpdb->insert(
            $this->pagesTable,
            [
                'uri'   => get_term_link($this->term->term_id),
                'type'  => 'category',
                'date'  => $date,
                'count' => 7,
                'id'    => $this->term->term_id,
            ]
        );

        $taxonomies = (new TaxonomyModel())->getTaxonomiesData([
            'post_type'         => ['post'],
            'taxonomy'          => ['category'],
            'resource_type'     => ['category'],
            'date'              => ['from' => $date, 'to' => $date],
            'count_total_posts' => true,
            'per_page'          => -1,
        ]);
        $category = array_values(array_filter($taxonomies['category'], function ($item) {
            return (int)$item['term_id'] === (int)$this->term->term_id;
        }));

        $this->assertSame(7, (int)$category[0]['views']);
    }

    public function test_delete_term_hits_uses_term_id_and_preserves_taxonomy_id_collision()
    {
        global $wpdb;

        $date = current_time('Y-m-d');
        foreach ([$this->term->term_id, $this->term->term_taxonomy_id] as $id) {
            $wpdb->insert(
                $this->pagesTable,
                [
                    'uri'   => 'https://example.org/term-' . $id,
                    'type'  => 'category',
                    'date'  => $date,
                    'count' => 1,
                    'id'    => $id,
                ]
            );
        }

        PostsManager::deleteTermHits($this->term->term_id, $this->term->term_taxonomy_id, 'category');

        $termRows = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `{$this->pagesTable}` WHERE `id` = %d", $this->term->term_id));
        $collisionRows = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `{$this->pagesTable}` WHERE `id` = %d", $this->term->term_taxonomy_id));

        $this->assertSame('0', $termRows);
        $this->assertSame('1', $collisionRows);
    }

    private function createTermWithDifferentTaxonomyId()
    {
        global $wpdb;

        $termId = self::factory()->term->create([
            'taxonomy' => 'category',
            'name'     => 'Category analytics link test ' . wp_generate_uuid4(),
        ]);
        $term = get_term($termId, 'category');

        if ($term->term_id === $term->term_taxonomy_id) {
            $wpdb->insert(
                $wpdb->term_taxonomy,
                [
                    'term_id'     => $term->term_id,
                    'taxonomy'    => 'post_tag',
                    'description' => '',
                    'parent'      => 0,
                    'count'       => 0,
                ]
            );
            $wpdb->delete($wpdb->term_taxonomy, ['term_taxonomy_id' => $wpdb->insert_id]);

            $termId = self::factory()->term->create([
                'taxonomy' => 'category',
                'name'     => 'Category analytics link regression ' . wp_generate_uuid4(),
            ]);
            $term = get_term($termId, 'category');
        }

        $this->assertNotSame($term->term_id, $term->term_taxonomy_id);

        return $term;
    }

    private function getUrlQuery($url)
    {
        parse_str(wp_parse_url($url, PHP_URL_QUERY), $query);

        return $query;
    }
}
