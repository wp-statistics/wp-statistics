<?php
/**
 * Device breakdown section for email reports.
 *
 * Renders compact subtables for device types, browsers, and operating systems.
 *
 * @var string $title             Section title.
 * @var array  $device_breakdown  ['types' => [], 'browsers' => [], 'operating_systems' => []].
 * @var string $muted_color       Muted text color.
 */

$title            = $title ?? '';
$deviceBreakdown  = is_array($device_breakdown ?? null) ? $device_breakdown : [];
$muted_color      = $muted_color ?? '#6b7280';

$types            = is_array($deviceBreakdown['types'] ?? null) ? $deviceBreakdown['types'] : [];
$browsers         = is_array($deviceBreakdown['browsers'] ?? null) ? $deviceBreakdown['browsers'] : [];
$operatingSystems = is_array($deviceBreakdown['operating_systems'] ?? null) ? $deviceBreakdown['operating_systems'] : [];

if (empty($types) && empty($browsers) && empty($operatingSystems)) {
    return;
}

$renderSubtable = static function (string $subtitle, array $rows, string $mutedColor): string {
    if (empty($rows)) {
        return '';
    }

    $hasShareColumn = (bool) array_filter($rows, static function ($row) {
        return is_array($row) && array_key_exists('share_percent', $row) && $row['share_percent'] !== null && $row['share_percent'] !== '';
    });

    ob_start();
    ?>
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:12px;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
        <tr style="background-color:#f9fafb;">
            <td colspan="<?php echo esc_attr($hasShareColumn ? '4' : '3'); ?>" style="padding:8px 12px;font-size:12px;font-weight:600;color:#111827;"><?php echo esc_html($subtitle); ?></td>
        </tr>
        <tr style="background-color:#f9fafb;">
            <td style="padding:6px 12px;font-size:11px;font-weight:600;color:<?php echo esc_attr($mutedColor); ?>;width:32px;">#</td>
            <td style="padding:6px 12px;font-size:11px;font-weight:600;color:<?php echo esc_attr($mutedColor); ?>;"><?php esc_html_e('Label', 'wp-statistics'); ?></td>
            <td style="padding:6px 12px;font-size:11px;font-weight:600;color:<?php echo esc_attr($mutedColor); ?>;text-align:right;"><?php esc_html_e('Visitors', 'wp-statistics'); ?></td>
            <?php if ($hasShareColumn) : ?>
            <td style="padding:6px 12px;font-size:11px;font-weight:600;color:<?php echo esc_attr($mutedColor); ?>;text-align:right;"><?php esc_html_e('Share', 'wp-statistics'); ?></td>
            <?php endif; ?>
        </tr>
        <?php foreach ($rows as $index => $row) :
            $bg = ($index % 2 === 0) ? '#ffffff' : '#f9fafb';
            $border = ($index < count($rows) - 1) ? 'border-bottom:1px solid #f3f4f6;' : '';
            $share = isset($row['share_percent']) && is_numeric($row['share_percent']) ? floatval($row['share_percent']) : null;
        ?>
        <tr style="background-color:<?php echo esc_attr($bg); ?>;">
            <td style="padding:8px 12px;font-size:12px;color:<?php echo esc_attr($mutedColor); ?>;<?php echo $border; ?>"><?php echo esc_html($index + 1); ?></td>
            <td style="padding:8px 12px;font-size:12px;color:#374151;<?php echo $border; ?>"><?php echo esc_html($row['label'] ?? ''); ?></td>
            <td style="padding:8px 12px;font-size:12px;color:#111827;text-align:right;font-weight:500;<?php echo $border; ?>"><?php echo esc_html($row['value'] ?? '0'); ?></td>
            <?php if ($hasShareColumn) : ?>
            <td style="padding:8px 12px;font-size:12px;color:<?php echo esc_attr($mutedColor); ?>;text-align:right;<?php echo $border; ?>">
                <?php echo $share === null ? '&ndash;' : esc_html(number_format_i18n($share, 1) . '%'); ?>
            </td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php
    return (string) ob_get_clean();
};
?>
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:24px;">
    <?php if (!empty($title)) : ?>
    <tr>
        <td style="padding:0 0 12px;font-size:14px;font-weight:600;color:#111827;"><?php echo esc_html($title); ?></td>
    </tr>
    <?php endif; ?>
    <tr>
        <td>
            <?php
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $renderSubtable(__('Types', 'wp-statistics'), $types, $muted_color);
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $renderSubtable(__('Browsers', 'wp-statistics'), $browsers, $muted_color);
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $renderSubtable(__('Operating Systems', 'wp-statistics'), $operatingSystems, $muted_color);
            ?>
        </td>
    </tr>
</table>
