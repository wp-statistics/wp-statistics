<?php
$aboutWidgetContent = apply_filters('wp_statistics_about_widget_content', false);
if ($aboutWidgetContent) {
    echo wp_kses_post($aboutWidgetContent);
    return;
}
?>
    <table style="margin: -15px 0;padding: 0 0 15px;width: 100%;">
        <tr>
            <td>
                <a href="https://wp-statistics.com" target="_blank">
                    <img src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/logo-250.png'); ?>" alt="WP-Statistics" class="wps-about-logo" style="width: 60px;">
                </a>
            </td>
            <td>
                <div style="text-align: left; font-size: 12px; padding-left: 10px;">
                    <a href="https://wp-statistics.com/documentation/" target="_blank"><?php _e('Documentation', 'wp-statistics'); ?></a> |
                    <a href="https://wp-statistics.com/add-ons/" target="_blank"><?php _e('Add-Ons', 'wp-statistics'); ?></a> |
                    <a href="https://wordpress.org/support/plugin/wp-statistics/reviews/?rate=5#new-post" target="_blank"><?php _e('Rate & Review', 'wp-statistics'); ?></a>
                    <div class="wps-postbox-veronalabs">
                        <a href="https://veronalabs.com/?utm_source=wp_statistics&utm_medium=display&utm_campaign=wordpress" target="_blank" title="<?php _e('Power by VeronaLabs', 'wp-statistics'); ?>"><img src="<?php echo esc_url(WP_STATISTICS_URL); ?>assets/images/veronalabs.svg" alt="VeronaLabs" style="width: 80px;"/></a>
                    </div>
                </div>
            </td>
        </tr>
    </table>

<?php if (!is_plugin_active('wp-statistics-customization/wp-statistics-customization.php')) { ?>
    <div style="margin: 0 -15px;border-top: 1px solid #e7e7e7;padding: 15px 15px 0; font-size: 12px;">
        <?php echo sprintf(__('Disable or customize this widget by <a href="%1$s" target="_blank">Customization Add-On!</a>', 'wp-statistics'), 'https://wp-statistics.com/product/wp-statistics-customization?utm_source=wp_statistics&utm_medium=display&utm_campaign=wordpress'); ?>
    </div>
<?php } ?>