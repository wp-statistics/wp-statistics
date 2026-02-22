<?php
/**
 * Device breakdown section for email reports.
 *
 * Compact progress-bar lists for device types, browsers, and operating systems.
 * Bar widths represent absolute share percentage (honest data).
 *
 * @var string $title             Section title.
 * @var array  $device_breakdown  ['types' => [], 'browsers' => [], 'operating_systems' => []].
 * @var string $primary_color     Primary brand color.
 * @var string $muted_color       Muted text color.
 */

$title            = $title ?? '';
$deviceBreakdown  = is_array($device_breakdown ?? null) ? $device_breakdown : [];
$primary_color    = $primary_color ?? '#1e40af';
$muted_color      = $muted_color ?? '#6b7280';
$is_rtl           = $is_rtl ?? false;

$types            = is_array($deviceBreakdown['types'] ?? null) ? $deviceBreakdown['types'] : [];
$browsers         = is_array($deviceBreakdown['browsers'] ?? null) ? $deviceBreakdown['browsers'] : [];
$operatingSystems = is_array($deviceBreakdown['operating_systems'] ?? null) ? $deviceBreakdown['operating_systems'] : [];

if (empty($types) && empty($browsers) && empty($operatingSystems)) {
    return;
}

$renderProgressList = static function (string $subtitle, array $rows, string $primaryColor, string $mutedColor): string {
    if (empty($rows)) {
        return '';
    }

    ob_start();
    ?>
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:16px;">
        <tr>
            <td style="padding:0 0 8px;font-size:12px;font-weight:600;color:<?php echo esc_attr($mutedColor); ?>;" class="email-text-muted"><?php echo esc_html($subtitle); ?></td>
        </tr>
        <?php foreach ($rows as $row) :
            $label = isset($row['label']) ? (string) $row['label'] : '';
            $share = isset($row['share_percent']) && is_numeric($row['share_percent']) ? floatval($row['share_percent']) : 0;
            $barWidth = max(intval($share), 1);
        ?>
        <tr>
            <td style="padding:6px 0;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tr>
                        <td style="font-size:13px;font-weight:500;color:#374151;padding-bottom:4px;" class="email-text"><?php echo esc_html($label); ?></td>
                        <td style="font-size:13px;font-weight:600;color:#111827;text-align:right;padding-bottom:4px;" class="email-text">
                            <?php echo esc_html(number_format_i18n($share, 1) . '%'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding:0;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f3f4f6;border-radius:4px;">
                                <tr>
                                    <td width="<?php echo esc_attr($barWidth); ?>%" style="background-color:<?php echo esc_attr($primaryColor); ?>;height:8px;border-radius:4px;font-size:1px;">&nbsp;</td>
                                    <td style="font-size:1px;">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php
    return (string) ob_get_clean();
};
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
            <?php
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $renderProgressList(__('Types', 'wp-statistics'), $types, $primary_color, $muted_color);
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $renderProgressList(__('Browsers', 'wp-statistics'), $browsers, $primary_color, $muted_color);
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $renderProgressList(__('Operating Systems', 'wp-statistics'), $operatingSystems, $primary_color, $muted_color);
            ?>
        </td>
    </tr>
</table>
