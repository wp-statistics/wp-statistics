<?php
/**
 * Promo Block Template
 *
 * @var array $settings Block settings
 * @var array $data Block data
 * @var array $globalSettings Global template settings
 */

$showPromo = $settings['showPromo'] ?? true;

if (!$showPromo) {
    return;
}

$title = $data['title'] ?? '';
$description = $data['description'] ?? '';
$url = $data['url'] ?? '';
$buttonText = $data['buttonText'] ?? __('Learn More', 'wp-statistics');
$primaryColor = $globalSettings['primaryColor'] ?? '#404BF2';

if (empty($title) || empty($url)) {
    return;
}
?>
<div style="margin: 32px 0; padding: 24px; background-color: #f4f4f5; border-radius: 8px; text-align: center;">
    <h3 style="font-size: 16px; font-weight: 600; color: #18181b; margin-bottom: 8px;">
        <?php echo esc_html($title); ?>
    </h3>
    <p style="font-size: 14px; color: #52525b; margin-bottom: 16px; line-height: 1.5;">
        <?php echo esc_html($description); ?>
    </p>
    <a href="<?php echo esc_url($url); ?>" style="display: inline-block; padding: 10px 24px; background-color: transparent; color: <?php echo esc_attr($primaryColor); ?>; font-size: 14px; font-weight: 600; text-decoration: none; border: 2px solid <?php echo esc_attr($primaryColor); ?>; border-radius: 6px;">
        <?php echo esc_html($buttonText); ?>
    </a>
</div>
