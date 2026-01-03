<?php
/**
 * Metrics Block Template
 *
 * @var array $settings Block settings
 * @var array $data Block data
 * @var array $globalSettings Global template settings
 */

$metrics = $data['metrics'] ?? [];
$columns = min(count($metrics), $data['columns'] ?? 4);
$showComparison = $settings['showComparison'] ?? true;
$primaryColor = $globalSettings['primaryColor'] ?? '#404BF2';

if (empty($metrics)) {
    return;
}
?>
<table role="presentation" class="metrics-grid" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 32px;">
    <tr>
        <?php foreach ($metrics as $index => $metric): ?>
            <td class="metric-cell" style="padding: 20px; text-align: center; border: 1px solid #e4e4e7; width: <?php echo round(100 / $columns); ?>%;">
                <div class="metric-value" style="font-size: 28px; font-weight: 700; color: #18181b; margin-bottom: 4px;">
                    <?php echo esc_html($metric['formatted']); ?>
                </div>
                <div class="metric-label" style="font-size: 12px; color: #71717a; text-transform: uppercase; letter-spacing: 0.5px;">
                    <?php echo esc_html($metric['label']); ?>
                </div>
                <?php if ($showComparison && isset($metric['change'])): ?>
                    <?php
                    $changeClass = $metric['change']['direction'];
                    $changeColor = $changeClass === 'up' ? '#16a34a' : ($changeClass === 'down' ? '#dc2626' : '#71717a');
                    $arrow = $changeClass === 'up' ? '↑' : ($changeClass === 'down' ? '↓' : '→');
                    ?>
                    <div class="metric-change <?php echo esc_attr($changeClass); ?>" style="font-size: 12px; margin-top: 8px; color: <?php echo esc_attr($changeColor); ?>;">
                        <?php echo esc_html($arrow . ' ' . $metric['change']['formatted']); ?>
                    </div>
                <?php endif; ?>
            </td>
            <?php if (($index + 1) % $columns === 0 && $index + 1 < count($metrics)): ?>
                </tr><tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </tr>
</table>
