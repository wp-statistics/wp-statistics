<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Service\Admin\ExportImport\Reports\ReportsExportDataTransformer;

class Test_ReportsExportColumns extends WP_UnitTestCase
{
    public function test_posts_export_has_published_date_column()
    {
        $postId = self::factory()->post->create(['post_title' => 'Example post']);

        $rows = ReportsExportDataTransformer::transformPostsData([(object)[
            'post_id'  => $postId,
            'title'    => 'Example post',
            'visitors' => 3,
            'views'    => 4,
            'words'    => 100,
            'date'     => '2026-11-20',
        ]]);

        $this->assertArrayHasKey('published_date', $rows[0]);
        $this->assertArrayNotHasKey('published_data', $rows[0]);
        $this->assertSame('2026-11-20', $rows[0]['published_date']);
    }
}
