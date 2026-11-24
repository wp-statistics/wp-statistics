<?php

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\Leaderboard\Leaderboard;
use WP_Statistics\Service\Admin\Leaderboard\LeaderboardPresenter;

/**
 * One post picture everywhere (owner test 14.17): Top Contents, the Top Pages table and the single
 * content header use components/objects/post-thumb, so a post without a featured image gets the
 * same colour initials tile in every place (no grey image placeholder).
 */
class Test_PostThumb extends WP_UnitTestCase
{
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

    public function test_thumb_data_is_the_thumbnail_size_or_the_initials_tile()
    {
        $withImage = self::factory()->post->create(['post_title' => 'Quick Dinners']);
        $noImage   = self::factory()->post->create(['post_title' => 'Slow Evenings']);
        set_post_thumbnail($withImage, $this->imageAttachment($withImage));

        $this->assertStringEndsWith('/2026/11/cover-150x150.jpg', LeaderboardPresenter::thumb($withImage, 'Quick Dinners')['thumb']);

        $tile = LeaderboardPresenter::thumb($noImage);
        $this->assertSame('', $tile['thumb']);
        $this->assertSame('SE', $tile['initials'], 'Title read from the post when not given');
        $this->assertSame(Leaderboard::color($noImage), $tile['color']);
    }

    public function test_top_pages_table_uses_the_same_tile_as_top_contents()
    {
        $postId = self::factory()->post->create(['post_title' => 'Slow Evenings']);

        $html = View::load('components/tables/top-report', [
            'data'       => [(object) [
                'post_id'     => $postId,
                'title'       => 'Slow Evenings',
                'visitors'    => 3,
                'views'       => 5,
                'words'       => 100,
                'bounce_rate' => 0,
                'date'        => '2026-11-01 10:00:00',
            ]],
            'pagination' => '',
        ], true);

        $this->assertStringNotContainsString('<svg xmlns="http://www.w3.org/2000/svg" width="140" height="140"', $html, 'No grey image placeholder');
        $this->assertMatchesRegularExpression('#<span class="wps-post-thumb wps-pages-image wps-post-thumb--tile" style="background-color:' . preg_quote(Leaderboard::color($postId), '#') . ';color:\#fff !important;display:inline-flex !important" aria-hidden="true">SE</span>#', $html);

        // The Top Contents list draws the same tile for the same post.
        $items = LeaderboardPresenter::contentItems([(object) ['ID' => $postId, 'post_title' => 'Slow Evenings', 'views' => 5]], 'views', function () {
            return '';
        });
        $list = View::load('components/content-rank-list', [
            'id'    => 'test-rank',
            'title' => 'Top Contents',
            'lists' => [['key' => 'views', 'label' => 'Most viewed', 'items' => $items]],
        ], true);
        $this->assertStringContainsString('wps-post-thumb wps-rank-list__thumb wps-post-thumb--tile" style="background-color:' . Leaderboard::color($postId) . ';color:#fff !important;display:inline-flex !important" aria-hidden="true">SE</span>', $list);
    }

    public function test_top_pages_table_shows_the_thumbnail_size_image()
    {
        $postId = self::factory()->post->create(['post_title' => 'Quick Dinners']);
        set_post_thumbnail($postId, $this->imageAttachment($postId));

        $html = View::load('components/objects/post-thumb', ['post_id' => $postId, 'title' => 'Quick Dinners', 'size' => 27, 'class' => 'wps-pages-image'], true);

        $this->assertMatchesRegularExpression('#<img class="wps-post-thumb wps-pages-image" src="[^"]+cover-150x150\.jpg" alt="" width="27" height="27" loading="lazy" decoding="async">#', $html);
    }

    public function test_tile_text_and_colour_are_escaped()
    {
        $html = View::load('components/objects/post-thumb', ['thumb' => '', 'initials' => '<b>', 'color' => '"red', 'class' => 'x'], true);

        $this->assertStringContainsString('&lt;b&gt;', $html);
        $this->assertStringNotContainsString('"red', $html);
    }
}
