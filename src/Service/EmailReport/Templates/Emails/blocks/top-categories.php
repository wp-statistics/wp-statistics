<?php
/**
 * Top Categories Block Template
 *
 * @var array $settings Block settings
 * @var array $data Block data
 * @var array $globalSettings Global template settings
 */

$categories = $data['categories'] ?? [];
$showViews = $settings['showViews'] ?? true;
$primaryColor = $globalSettings['primaryColor'] ?? '#404BF2';

if (empty($categories)) {
    return;
}
?>
<div style="margin-bottom: 32px;">
    <h2 style="font-size: 18px; font-weight: 600; color: #18181b; margin-bottom: 16px;">
        <?php esc_html_e('Top Categories', 'wp-statistics'); ?>
    </h2>
    <table role="presentation" class="list-table" width="100%" cellpadding="0" cellspacing="0" border="0">
        <?php foreach ($categories as $index => $category): ?>
            <tr class="list-item" style="border-bottom: 1px solid #e4e4e7;">
                <td class="list-rank" style="padding: 12px 0; width: 32px; font-size: 14px; font-weight: 600; color: #71717a;">
                    <?php echo esc_html($index + 1); ?>
                </td>
                <td class="list-title" style="padding: 12px 8px;">
                    <a href="<?php echo esc_url($category['url']); ?>" style="color: #18181b; text-decoration: none; font-size: 14px;">
                        <?php echo esc_html($category['name']); ?>
                    </a>
                </td>
                <td class="list-stat" style="padding: 12px 0; text-align: right; font-size: 14px; white-space: nowrap;">
                    <?php if ($showViews): ?>
                        <span style="font-weight: 600; color: #18181b;"><?php echo esc_html($category['viewsFormatted']); ?></span>
                        <span style="color: #71717a; font-size: 12px;"><?php esc_html_e('views', 'wp-statistics'); ?></span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
