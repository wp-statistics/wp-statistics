<?php
/**
 * Ranked data table for email reports.
 *
 * Renders a table of ranked items (top pages, referrers, countries, etc.).
 *
 * @var string $title        Section title (e.g., "Top Pages").
 * @var string $column_label Label for the name column.
 * @var string $value_label  Label for the value column.
 * @var array  $rows         Array of rows, each with 'label', 'value', optional 'url', 'change_percent', 'share_percent'.
 * @var string $primary_color Primary brand color.
 * @var string $muted_color   Muted text color.
 * @var string $positive_color Color for positive trends.
 * @var string $negative_color Color for negative trends.
 * @var bool   $show_comparison Whether comparison columns should render.
 */

$title         = $title ?? '';
$column_label  = $column_label ?? '';
$value_label   = $value_label ?? '';
$rows          = $rows ?? [];
$primary_color = $primary_color ?? '#1e40af';
$muted_color   = $muted_color ?? '#6b7280';
$positive_color = $positive_color ?? '#059669';
$negative_color = $negative_color ?? '#dc2626';
$show_comparison = $show_comparison ?? true;

if (empty($rows)) {
    return;
}

$hasChangeColumn = $show_comparison && (bool) array_filter($rows, function ($row) {
    return is_array($row) && array_key_exists('change_percent', $row) && $row['change_percent'] !== null && $row['change_percent'] !== '';
});

$hasShareColumn = (bool) array_filter($rows, function ($row) {
    return is_array($row) && array_key_exists('share_percent', $row) && $row['share_percent'] !== null && $row['share_percent'] !== '';
});
?>
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:24px;">
    <?php if (!empty($title)) : ?>
    <tr>
        <td style="padding:0 0 12px;font-size:14px;font-weight:600;color:#111827;"><?php echo esc_html($title); ?></td>
    </tr>
    <?php endif; ?>
    <tr>
        <td>
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                <tr style="background-color:#f9fafb;">
                    <td style="padding:8px 12px;font-size:11px;font-weight:600;color:<?php echo esc_attr($muted_color); ?>;width:32px;">#</td>
                    <td style="padding:8px 12px;font-size:11px;font-weight:600;color:<?php echo esc_attr($muted_color); ?>;"><?php echo esc_html($column_label); ?></td>
                    <td style="padding:8px 12px;font-size:11px;font-weight:600;color:<?php echo esc_attr($muted_color); ?>;text-align:right;"><?php echo esc_html($value_label); ?></td>
                    <?php if ($hasChangeColumn) : ?>
                    <td style="padding:8px 12px;font-size:11px;font-weight:600;color:<?php echo esc_attr($muted_color); ?>;text-align:right;"><?php esc_html_e('Change', 'wp-statistics'); ?></td>
                    <?php endif; ?>
                    <?php if ($hasShareColumn) : ?>
                    <td style="padding:8px 12px;font-size:11px;font-weight:600;color:<?php echo esc_attr($muted_color); ?>;text-align:right;"><?php esc_html_e('Share', 'wp-statistics'); ?></td>
                    <?php endif; ?>
                </tr>
                <?php foreach ($rows as $index => $row) :
                    $bg = ($index % 2 === 0) ? '#ffffff' : '#f9fafb';
                    $border = ($index < count($rows) - 1) ? 'border-bottom:1px solid #f3f4f6;' : '';
                    $label = isset($row['label']) ? (string) $row['label'] : '';
                    $value = isset($row['value']) ? (string) $row['value'] : '0';

                    $change = isset($row['change_percent']) && is_numeric($row['change_percent']) ? floatval($row['change_percent']) : null;
                    $changeColor = $muted_color;
                    if ($change !== null) {
                        if ($change > 0) {
                            $changeColor = $positive_color;
                        } elseif ($change < 0) {
                            $changeColor = $negative_color;
                        }
                    }

                    $share = isset($row['share_percent']) && is_numeric($row['share_percent']) ? floatval($row['share_percent']) : null;
                ?>
                <tr style="background-color:<?php echo esc_attr($bg); ?>;">
                    <td style="padding:10px 12px;font-size:12px;color:<?php echo esc_attr($muted_color); ?>;<?php echo $border; ?>"><?php echo esc_html($index + 1); ?></td>
                    <td style="padding:10px 12px;font-size:13px;color:#374151;<?php echo $border; ?>">
                        <?php if (!empty($row['url'])) : ?>
                            <a href="<?php echo esc_url($row['url']); ?>" style="color:<?php echo esc_attr($primary_color); ?>;text-decoration:none;"><?php echo esc_html($label); ?></a>
                        <?php else : ?>
                            <?php echo esc_html($label); ?>
                        <?php endif; ?>
                    </td>
                    <td style="padding:10px 12px;font-size:13px;color:#111827;text-align:right;font-weight:500;<?php echo $border; ?>"><?php echo esc_html($value); ?></td>
                    <?php if ($hasChangeColumn) : ?>
                    <td style="padding:10px 12px;font-size:12px;color:<?php echo esc_attr($changeColor); ?>;text-align:right;font-weight:500;<?php echo $border; ?>">
                        <?php
                        if ($change === null) {
                            echo '&ndash;';
                        } else {
                            $formattedChange = ($change > 0 ? '+' : '') . number_format_i18n($change, 1) . '%';
                            echo esc_html($formattedChange);
                        }
                        ?>
                    </td>
                    <?php endif; ?>
                    <?php if ($hasShareColumn) : ?>
                    <td style="padding:10px 12px;font-size:12px;color:<?php echo esc_attr($muted_color); ?>;text-align:right;<?php echo $border; ?>">
                        <?php
                        if ($share === null) {
                            echo '&ndash;';
                        } else {
                            echo esc_html(number_format_i18n($share, 1) . '%');
                        }
                        ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </table>
        </td>
    </tr>
</table>
