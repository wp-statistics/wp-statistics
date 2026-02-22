<?php
/**
 * KPI metrics row for email reports.
 *
 * Typography-first design: stacked label/value/change with column dividers.
 *
 * @var string $title          Optional section title.
 * @var array  $kpis           Array of KPIs, each with 'label', 'value', 'change_percent'.
 * @var string $primary_color  Primary brand color.
 * @var string $muted_color    Muted text color.
 * @var string $positive_color Color for positive trends.
 * @var string $negative_color Color for negative trends.
 * @var bool   $show_comparison Whether to show comparison percentages.
 */

$primary_color   = $primary_color ?? '#1e40af';
$muted_color     = $muted_color ?? '#6b7280';
$positive_color  = $positive_color ?? '#059669';
$negative_color  = $negative_color ?? '#dc2626';
$title           = $title ?? '';
$show_comparison = $show_comparison ?? true;
$kpis            = $kpis ?? [];
$is_rtl          = $is_rtl ?? false;

if (empty($kpis)) {
    return;
}

$count     = count($kpis);
$width     = $count > 0 ? intval(100 / $count) : 33;
$text_align = $is_rtl ? 'right' : 'left';

// Column divider: left border on non-first cells (RTL: right border)
$divider_side = $is_rtl ? 'right' : 'left';
?>
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:28px;">
    <?php if (!empty($title)) : ?>
    <tr>
        <td style="padding:0 0 14px;font-size:14px;font-weight:600;color:#111827;" class="email-text">
            <?php echo esc_html($title); ?>
        </td>
    </tr>
    <?php endif; ?>
    <tr>
        <?php foreach ($kpis as $index => $kpi) :
            $change = isset($kpi['change_percent']) ? floatval($kpi['change_percent']) : 0;
            $label  = isset($kpi['label']) ? (string) $kpi['label'] : '';
            $value  = isset($kpi['value']) ? (string) $kpi['value'] : '0';

            $change_color = $muted_color;
            $change_text  = '';
            if ($change > 0) {
                $change_color = $positive_color;
                $change_text  = '+' . number_format_i18n(abs($change), 1) . '%';
            } elseif ($change < 0) {
                $change_color = $negative_color;
                $change_text  = '-' . number_format_i18n(abs($change), 1) . '%';
            }

            // First cell: no border/padding. Subsequent cells: divider + padding.
            $divider_style = $index > 0
                ? "border-{$divider_side}:1px solid #e5e7eb;padding-{$divider_side}:16px;"
                : '';
        ?>
        <td width="<?php echo esc_attr($width); ?>%" valign="top" style="<?php echo esc_attr($divider_style); ?>text-align:<?php echo esc_attr($text_align); ?>;">
            <p style="margin:0 0 2px;font-size:11px;font-weight:500;color:<?php echo esc_attr($muted_color); ?>;" class="email-text-muted"><?php echo esc_html($label); ?></p>
            <p style="margin:0 0 4px;font-size:32px;font-weight:700;color:#111827;line-height:1.1;" class="email-text"><?php echo esc_html($value); ?></p>
            <?php if ($show_comparison && $change != 0) : ?>
            <p style="margin:0;font-size:12px;font-weight:600;color:<?php echo esc_attr($change_color); ?>;"><?php echo esc_html($change_text); ?></p>
            <?php endif; ?>
        </td>
        <?php endforeach; ?>
    </tr>
</table>
