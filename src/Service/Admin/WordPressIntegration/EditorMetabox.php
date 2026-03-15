<?php

namespace WP_Statistics\Service\Admin\WordPressIntegration;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Utils\Format;
use WP_Statistics\Utils\User;

/**
 * Editor Statistics Metabox.
 *
 * Adds a stats summary metabox to the post editor (classic and Gutenberg).
 * Controlled by the `disable_editor` setting (inverted: true = disabled).
 *
 * @since 15.0.0
 */
class EditorMetabox
{
    public function __construct()
    {
        // When disable_editor is true, the feature is OFF (inverted setting)
        if (Option::getValue('disable_editor')) {
            return;
        }

        add_action('add_meta_boxes', [$this, 'register']);
    }

    private function shouldShow(): bool
    {
        return (bool) User::hasAccess('read');
    }

    public function register(): void
    {
        if (!$this->shouldShow()) {
            return;
        }

        $postTypes = array_keys(get_post_types(['public' => true]));

        foreach ($postTypes as $postType) {
            add_meta_box(
                'wp_statistics_editor_metabox',
                esc_html__('WP Statistics', 'wp-statistics'),
                [$this, 'render'],
                $postType,
                'side',
                'default'
            );
        }
    }

    /**
     * Render the metabox content showing views and visitors for the current post.
     */
    public function render(\WP_Post $post): void
    {
        $postId = $post->ID;

        if (empty($postId) || $post->post_status === 'auto-draft') {
            printf(
                '<p style="color:#666;margin:0;">%s</p>',
                esc_html__('Publish this content to see its statistics.', 'wp-statistics')
            );
            return;
        }

        $data = $this->fetchData($postId);

        $dashboardUrl = admin_url('admin.php?page=wp-statistics#/content/' . $postId);
        ?>
        <div class="wps-editor-metabox" style="margin:-6px -12px -12px;">
            <table style="width:100%;border-collapse:collapse;">
                <tr>
                    <td style="padding:10px 12px;border-bottom:1px solid #f0f0f0;width:50%;">
                        <div style="color:#666;font-size:11px;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">
                            <?php esc_html_e('Views', 'wp-statistics'); ?>
                        </div>
                        <div style="font-size:18px;font-weight:600;color:#1e1e1e;">
                            <?php echo esc_html(Format::compactNumber($data['views'])); ?>
                        </div>
                    </td>
                    <td style="padding:10px 12px;border-bottom:1px solid #f0f0f0;border-left:1px solid #f0f0f0;">
                        <div style="color:#666;font-size:11px;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">
                            <?php esc_html_e('Visitors', 'wp-statistics'); ?>
                        </div>
                        <div style="font-size:18px;font-weight:600;color:#1e1e1e;">
                            <?php echo esc_html(Format::compactNumber($data['visitors'])); ?>
                        </div>
                    </td>
                </tr>
            </table>
            <div style="padding:8px 12px;text-align:center;">
                <a href="<?php echo esc_url($dashboardUrl); ?>" style="color:#2271b1;text-decoration:none;font-size:12px;">
                    <?php esc_html_e('View detailed analytics', 'wp-statistics'); ?> &rarr;
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Fetch views and visitors for a post.
     *
     * @param int $postId The post ID.
     * @return array{views: int, visitors: int}
     */
    private function fetchData(int $postId): array
    {
        try {
            $handler = new AnalyticsQueryHandler(true);

            $batch = $handler->handleBatch([
                [
                    'id'      => 'views',
                    'sources' => ['views'],
                    'filters' => ['page_id' => ['is' => $postId]],
                    'format'  => 'flat',
                ],
                [
                    'id'      => 'visitors',
                    'sources' => ['visitors'],
                    'filters' => ['page_id' => ['is' => $postId]],
                    'format'  => 'flat',
                ],
            ]);

            $items = $batch['items'] ?? [];

            return [
                'views'    => (int) ($items['views']['items'][0]['views'] ?? 0),
                'visitors' => (int) ($items['visitors']['items'][0]['visitors'] ?? 0),
            ];
        } catch (\Exception $e) {
            return ['views' => 0, 'visitors' => 0];
        }
    }
}
