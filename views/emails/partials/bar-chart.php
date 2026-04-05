<?php
/**
 * CSS-only horizontal bar chart for email reports.
 *
 * Uses table cells with background-color for Outlook compatibility.
 * Features background tracks for proportion context.
 *
 * @var string $title         Section title.
 * @var array  $chart_data    Array of items, each with 'label' and 'value'.
 * @var string $primary_color Primary bar color.
 * @var string $muted_color   Muted text color.
 */

$title         = $title ?? '';
$chart_data    = $chart_data ?? [];
$primary_color = $primary_color ?? '#1e40af';
$muted_color   = $muted_color ?? '#6b7280';
$is_rtl        = $is_rtl ?? false;

if (empty($chart_data)) {
    return;
}

$max_value = max(array_column($chart_data, 'value'));
if ($max_value <= 0) {
    $max_value = 1;
}

$text_align     = $is_rtl ? 'right' : 'left';
$text_align_opp = $is_rtl ? 'left' : 'right';
$padding_side   = $is_rtl ? 'padding:4px 0 4px 8px;' : 'padding:4px 8px 4px 0;';
$padding_value  = $is_rtl ? 'padding:4px 8px 4px 0;' : 'padding:4px 0 4px 8px;';
?>
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:28px;">
    <?php if (!empty($title)) : ?>
    <tr>
        <td colspan="3" style="padding:0 0 14px;font-size:14px;font-weight:600;color:#111827;" class="email-text">
            <?php echo esc_html($title); ?>
        </td>
    </tr>
    <?php endif; ?>
    <?php foreach ($chart_data as $item) :
        $pct = ($item['value'] / $max_value) * 100;
        $bar_width = max(intval($pct), 2);
    ?>
    <tr>
        <td width="50" style="<?php echo esc_attr($padding_side); ?>font-size:12px;color:<?php echo esc_attr($muted_color); ?>;white-space:nowrap;text-align:<?php echo esc_attr($text_align); ?>;" class="email-text-muted"><?php echo esc_html($item['label']); ?></td>
        <td style="padding:4px 0;">
            <!-- Background track with bar overlay -->
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f3f4f6;border-radius:3px;">
                <tr>
                    <td width="<?php echo esc_attr($bar_width); ?>%" style="background-color:<?php echo esc_attr($primary_color); ?>;height:16px;border-radius:3px;font-size:1px;">&nbsp;</td>
                    <td style="font-size:1px;">&nbsp;</td>
                </tr>
            </table>
        </td>
        <td width="50" style="<?php echo esc_attr($padding_value); ?>font-size:13px;color:#111827;text-align:<?php echo esc_attr($text_align_opp); ?>;font-weight:600;" class="email-text"><?php echo esc_html(number_format_i18n($item['value'])); ?></td>
    </tr>
    <?php endforeach; ?>
</table>
