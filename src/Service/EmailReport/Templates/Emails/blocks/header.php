<?php
/**
 * Header Block Template
 *
 * @var array $settings Block settings
 * @var array $data Block data
 * @var array $globalSettings Global template settings
 */

$primaryColor = $globalSettings['primaryColor'] ?? '#404BF2';
?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 32px;">
    <tr>
        <td align="center">
            <?php if (!empty($settings['showLogo']) && !empty($data['logoUrl'])): ?>
                <img src="<?php echo esc_url($data['logoUrl']); ?>" alt="<?php echo esc_attr($data['siteName']); ?>" style="max-width: 150px; height: auto; margin-bottom: 16px;">
            <?php endif; ?>

            <?php if (!empty($settings['showSiteTitle'])): ?>
                <h1 style="font-size: 24px; font-weight: 700; color: #18181b; margin-bottom: 8px;">
                    <?php echo esc_html($data['siteName']); ?>
                </h1>
            <?php endif; ?>

            <p style="font-size: 16px; color: <?php echo esc_attr($primaryColor); ?>; font-weight: 600; margin-bottom: 8px;">
                <?php echo esc_html($data['periodLabel']); ?>
            </p>

            <?php if (!empty($settings['showDateRange'])): ?>
                <p style="font-size: 14px; color: #71717a;">
                    <?php echo esc_html($data['dateRangeText']); ?>
                </p>
            <?php endif; ?>
        </td>
    </tr>
</table>
