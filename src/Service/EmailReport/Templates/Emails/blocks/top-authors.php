<?php
/**
 * Top Authors Block Template
 *
 * @var array $settings Block settings
 * @var array $data Block data
 * @var array $globalSettings Global template settings
 */

$authors = $data['authors'] ?? [];
$showViews = $settings['showViews'] ?? true;
$showAvatar = $settings['showAvatar'] ?? true;
$primaryColor = $globalSettings['primaryColor'] ?? '#404BF2';

if (empty($authors)) {
    return;
}
?>
<div style="margin-bottom: 32px;">
    <h2 style="font-size: 18px; font-weight: 600; color: #18181b; margin-bottom: 16px;">
        <?php esc_html_e('Top Authors', 'wp-statistics'); ?>
    </h2>
    <table role="presentation" class="list-table" width="100%" cellpadding="0" cellspacing="0" border="0">
        <?php foreach ($authors as $index => $author): ?>
            <tr class="list-item" style="border-bottom: 1px solid #e4e4e7;">
                <td class="list-rank" style="padding: 12px 0; width: 32px; font-size: 14px; font-weight: 600; color: #71717a;">
                    <?php echo esc_html($index + 1); ?>
                </td>
                <?php if ($showAvatar): ?>
                    <td style="padding: 12px 8px; width: 40px;">
                        <img src="<?php echo esc_url($author['avatar']); ?>" alt="" width="32" height="32" style="display: block; border-radius: 50%;">
                    </td>
                <?php endif; ?>
                <td class="list-title" style="padding: 12px 8px;">
                    <a href="<?php echo esc_url($author['url']); ?>" style="color: #18181b; text-decoration: none; font-size: 14px;">
                        <?php echo esc_html($author['name']); ?>
                    </a>
                </td>
                <td class="list-stat" style="padding: 12px 0; text-align: right; font-size: 14px; white-space: nowrap;">
                    <?php if ($showViews): ?>
                        <span style="font-weight: 600; color: #18181b;"><?php echo esc_html($author['viewsFormatted']); ?></span>
                        <span style="color: #71717a; font-size: 12px;"><?php esc_html_e('views', 'wp-statistics'); ?></span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
