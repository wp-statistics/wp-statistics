<?php
/**
 * CTA Block Template
 *
 * @var array $settings Block settings
 * @var array $data Block data
 * @var array $globalSettings Global template settings
 */

$text = $settings['text'] ?? __('View Full Report', 'wp-statistics');
$url = $settings['url'] ?? $data['dashboardUrl'] ?? '';
$alignment = $settings['alignment'] ?? 'center';
$primaryColor = $globalSettings['primaryColor'] ?? '#404BF2';

if (empty($url)) {
    return;
}
?>
<div style="margin: 32px 0; text-align: <?php echo esc_attr($alignment); ?>;">
    <!--[if mso]>
    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="<?php echo esc_url($url); ?>" style="height:44px;v-text-anchor:middle;width:200px;" arcsize="14%" stroke="f" fillcolor="<?php echo esc_attr($primaryColor); ?>">
        <w:anchorlock/>
        <center>
    <![endif]-->
    <a href="<?php echo esc_url($url); ?>" class="btn" style="display: inline-block; padding: 12px 32px; background-color: <?php echo esc_attr($primaryColor); ?>; color: #ffffff; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; font-size: 14px; font-weight: 600; text-decoration: none; border-radius: 6px;">
        <?php echo esc_html($text); ?>
    </a>
    <!--[if mso]>
        </center>
    </v:roundrect>
    <![endif]-->
</div>
