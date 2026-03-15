<?php
/**
 * Ranked data table for email reports.
 *
 * Clean typography-first design: plain rank numbers, no badges or pills.
 *
 * @var string $title          Section title (e.g., "Top Pages").
 * @var string $column_label   Label for the name column.
 * @var string $value_label    Label for the value column.
 * @var array  $rows           Array of rows, each with 'label', 'value', optional 'url', 'change_percent', 'share_percent'.
 * @var string $primary_color  Primary brand color.
 * @var string $muted_color    Muted text color.
 * @var string $positive_color Color for positive trends.
 * @var string $negative_color Color for negative trends.
 * @var bool   $show_comparison Whether comparison columns should render.
 */

$title          = $title ?? '';
$column_label   = $column_label ?? '';
$value_label    = $value_label ?? '';
$rows           = $rows ?? [];
$primary_color  = $primary_color ?? '#1e40af';
$muted_color    = $muted_color ?? '#6b7280';
$positive_color = $positive_color ?? '#059669';
$negative_color = $negative_color ?? '#dc2626';
$show_comparison = $show_comparison ?? true;
$is_rtl          = $is_rtl ?? false;

if (empty($rows)) {
    return;
}

$text_align = $is_rtl ? 'right' : 'left';

$hasChangeColumn = $show_comparison && (bool) array_filter($rows, function ($row) {
    return is_array($row) && array_key_exists('change_percent', $row) && $row['change_percent'] !== null && $row['change_percent'] !== '';
});

$hasShareColumn = (bool) array_filter($rows, function ($row) {
    return is_array($row) && array_key_exists('share_percent', $row) && $row['share_percent'] !== null && $row['share_percent'] !== '';
});
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
        <td>
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                <!-- Header row -->
                <tr>
                    <td style="padding:8px 8px 10px;font-size:11px;font-weight:600;color:<?php echo esc_attr($muted_color); ?>;border-bottom:1px solid #e5e7eb;width:24px;" class="email-text-muted email-border">#</td>
                    <td style="padding:8px 8px 10px;font-size:11px;font-weight:600;color:<?php echo esc_attr($muted_color); ?>;border-bottom:1px solid #e5e7eb;" class="email-text-muted email-border"><?php echo esc_html($column_label); ?></td>
                    <td style="padding:8px 8px 10px;font-size:11px;font-weight:600;color:<?php echo esc_attr($muted_color); ?>;border-bottom:1px solid #e5e7eb;text-align:right;" class="email-text-muted email-border"><?php echo esc_html($value_label); ?></td>
                    <?php if ($hasChangeColumn) : ?>
                    <td style="padding:8px 8px 10px;font-size:11px;font-weight:600;color:<?php echo esc_attr($muted_color); ?>;border-bottom:1px solid #e5e7eb;text-align:right;" class="email-text-muted email-border"><?php esc_html_e('Change', 'wp-statistics'); ?></td>
                    <?php endif; ?>
                    <?php if ($hasShareColumn) : ?>
                    <td style="padding:8px 8px 10px;font-size:11px;font-weight:600;color:<?php echo esc_attr($muted_color); ?>;border-bottom:1px solid #e5e7eb;text-align:right;" class="email-text-muted email-border"><?php esc_html_e('Share', 'wp-statistics'); ?></td>
                    <?php endif; ?>
                </tr>
                <?php foreach ($rows as $index => $row) :
                    $border = ($index < count($rows) - 1) ? 'border-bottom:1px solid #f3f4f6;' : '';
                    $label  = isset($row['label']) ? (string) $row['label'] : '';
                    $value  = isset($row['value']) ? (string) $row['value'] : '0';

                    $change      = isset($row['change_percent']) && is_numeric($row['change_percent']) ? floatval($row['change_percent']) : null;
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
                <tr>
                    <!-- Rank number -->
                    <td style="padding:10px 8px;font-size:12px;font-weight:600;color:<?php echo esc_attr($muted_color); ?>;width:24px;<?php echo esc_attr($border); ?>" class="email-text-muted"><?php echo esc_html($index + 1); ?></td>
                    <td style="padding:10px 8px;font-size:13px;color:#374151;<?php echo esc_attr($border); ?>" class="email-text">
                        <?php if (!empty($row['url'])) : ?>
                            <a href="<?php echo esc_url($row['url']); ?>" style="color:<?php echo esc_attr($primary_color); ?>;text-decoration:none;font-weight:500;"><?php echo esc_html($label); ?></a>
                        <?php else : ?>
                            <?php echo esc_html($label); ?>
                        <?php endif; ?>
                    </td>
                    <td style="padding:10px 8px;font-size:13px;color:#111827;text-align:right;font-weight:600;<?php echo esc_attr($border); ?>" class="email-text"><?php echo esc_html($value); ?></td>
                    <?php if ($hasChangeColumn) : ?>
                    <td style="padding:10px 8px;text-align:right;<?php echo esc_attr($border); ?>">
                        <?php if ($change === null) : ?>
                            <span style="color:<?php echo esc_attr($muted_color); ?>;font-size:12px;"><?php echo esc_html('–'); ?></span>
                        <?php else : ?>
                            <span style="color:<?php echo esc_attr($changeColor); ?>;font-size:12px;font-weight:600;">
                                <?php echo esc_html(($change > 0 ? '+' : '') . number_format_i18n($change, 1) . '%'); ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                    <?php if ($hasShareColumn) : ?>
                    <td style="padding:10px 8px;font-size:12px;color:<?php echo esc_attr($muted_color); ?>;text-align:right;<?php echo esc_attr($border); ?>" class="email-text-muted">
                        <?php echo $share === null ? esc_html('–') : esc_html(number_format_i18n($share, 1) . '%'); ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </table>
        </td>
    </tr>
</table>
