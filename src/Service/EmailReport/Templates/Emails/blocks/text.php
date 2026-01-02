<?php
/**
 * Text Block Template
 *
 * @var array $settings Block settings
 * @var array $data Block data
 * @var array $globalSettings Global template settings
 */

$content = $settings['content'] ?? '';
$alignment = $settings['alignment'] ?? 'left';
$fontSize = $settings['fontSize'] ?? 'normal';

if (empty($content)) {
    return;
}

$fontSizes = [
    'small' => '12px',
    'normal' => '14px',
    'large' => '16px',
];

$size = $fontSizes[$fontSize] ?? $fontSizes['normal'];
?>
<div style="margin-bottom: 24px; text-align: <?php echo esc_attr($alignment); ?>;">
    <p style="font-size: <?php echo esc_attr($size); ?>; color: #52525b; line-height: 1.6;">
        <?php echo wp_kses_post(nl2br($content)); ?>
    </p>
</div>
