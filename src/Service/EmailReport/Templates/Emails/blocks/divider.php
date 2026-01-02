<?php
/**
 * Divider Block Template
 *
 * @var array $settings Block settings
 * @var array $data Block data
 * @var array $globalSettings Global template settings
 */

$style = $settings['style'] ?? 'solid';
$color = $settings['color'] ?? '#e4e4e7';
$spacing = $settings['spacing'] ?? 'normal';

$spacings = [
    'small' => '16px',
    'normal' => '24px',
    'large' => '32px',
];

$margin = $spacings[$spacing] ?? $spacings['normal'];
?>
<div style="margin: <?php echo esc_attr($margin); ?> 0;">
    <hr style="border: none; border-top: 1px <?php echo esc_attr($style); ?> <?php echo esc_attr($color); ?>; margin: 0;">
</div>
