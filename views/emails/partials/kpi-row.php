<?php
/**
 * KPI metrics row for email reports.
 *
 * Renders 2-3 KPI metric cards in a row with values and comparison arrows.
 *
 * @var string $title         Optional section title.
 * @var array  $kpis          Array of KPIs, each with 'label', 'value', 'change_percent'.
 * @var string $primary_color Primary brand color.
 * @var string $muted_color   Muted text color.
 * @var string $positive_color Color for positive trends.
 * @var string $negative_color Color for negative trends.
 * @var bool   $show_comparison Whether to show comparison percentages.
 */

$primary_color  = $primary_color ?? '#1e40af';
$muted_color    = $muted_color ?? '#6b7280';
$positive_color = $positive_color ?? '#059669';
$negative_color = $negative_color ?? '#dc2626';
$title          = $title ?? '';
$show_comparison = $show_comparison ?? true;
$kpis           = $kpis ?? [];

if (empty($kpis)) {
    return;
}

$count = count($kpis);
$width = $count > 0 ? intval(100 / $count) : 33;
?>
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:24px;">
    <?php if (!empty($title)) : ?>
    <tr>
        <td style="padding:0 0 12px;font-size:14px;font-weight:600;color:#111827;"><?php echo esc_html($title); ?></td>
    </tr>
    <?php endif; ?>
    <tr>
        <?php foreach ($kpis as $index => $kpi) :
            $change = isset($kpi['change_percent']) ? floatval($kpi['change_percent']) : 0;
            $arrow  = $change > 0 ? '&#9650;' : ($change < 0 ? '&#9660;' : '');
            $color  = $change > 0 ? $positive_color : ($change < 0 ? $negative_color : $muted_color);
            $label  = isset($kpi['label']) ? (string) $kpi['label'] : '';
            $value  = isset($kpi['value']) ? (string) $kpi['value'] : '0';
        ?>
        <td width="<?php echo esc_attr($width); ?>%" valign="top" style="padding:0 <?php echo $index < $count - 1 ? '8' : '0'; ?>px 0 <?php echo $index > 0 ? '8' : '0'; ?>px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;">
                <tr>
                    <td style="padding:16px;text-align:center;">
                        <p style="margin:0 0 4px;font-size:12px;color:<?php echo esc_attr($muted_color); ?>;text-transform:none;"><?php echo esc_html($label); ?></p>
                        <p style="margin:0 0 4px;font-size:24px;font-weight:700;color:#111827;line-height:1.2;"><?php echo esc_html($value); ?></p>
                        <?php if ($show_comparison && $change != 0) : ?>
                        <p style="margin:0;font-size:12px;color:<?php echo esc_attr($color); ?>;">
                            <?php echo $arrow; ?> <?php echo esc_html(abs($change) . '%'); ?>
                        </p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </td>
        <?php endforeach; ?>
    </tr>
</table>
