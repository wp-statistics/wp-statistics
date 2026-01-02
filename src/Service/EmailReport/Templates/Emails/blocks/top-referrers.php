<?php
/**
 * Top Referrers Block Template
 *
 * @var array $settings Block settings
 * @var array $data Block data
 * @var array $globalSettings Global template settings
 */

$referrers = $data['referrers'] ?? [];
$showVisitors = $settings['showVisitors'] ?? true;
$primaryColor = $globalSettings['primaryColor'] ?? '#404BF2';

if (empty($referrers)) {
    return;
}
?>
<div style="margin-bottom: 32px;">
    <h2 style="font-size: 18px; font-weight: 600; color: #18181b; margin-bottom: 16px;">
        <?php esc_html_e('Top Referrers', 'wp-statistics'); ?>
    </h2>
    <table role="presentation" class="list-table" width="100%" cellpadding="0" cellspacing="0" border="0">
        <?php foreach ($referrers as $index => $referrer): ?>
            <tr class="list-item" style="border-bottom: 1px solid #e4e4e7;">
                <td class="list-rank" style="padding: 12px 0; width: 32px; font-size: 14px; font-weight: 600; color: #71717a;">
                    <?php echo esc_html($index + 1); ?>
                </td>
                <td style="padding: 12px 8px; width: 24px;">
                    <img src="<?php echo esc_url($referrer['favicon']); ?>" alt="" width="16" height="16" style="display: block;">
                </td>
                <td class="list-title" style="padding: 12px 8px;">
                    <span style="color: #18181b; font-size: 14px;">
                        <?php echo esc_html($referrer['domain']); ?>
                    </span>
                </td>
                <td class="list-stat" style="padding: 12px 0; text-align: right; font-size: 14px; white-space: nowrap;">
                    <?php if ($showVisitors): ?>
                        <span style="font-weight: 600; color: #18181b;"><?php echo esc_html($referrer['visitorsFormatted']); ?></span>
                        <span style="color: #71717a; font-size: 12px;"><?php esc_html_e('visitors', 'wp-statistics'); ?></span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
