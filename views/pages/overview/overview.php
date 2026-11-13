<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;

// A short banner when an installed add-on has no valid license or is expired.
$licenseSummary = LicenseHelper::getLicenseSummary();
$licenseNotice  = '';

if ($licenseSummary['expired'] > 0) {
    /* translators: %d: number of add-ons */
    $licenseNotice = sprintf(_n('%d add-on has an expired license.', '%d add-ons have an expired license.', $licenseSummary['expired'], 'wp-statistics'), $licenseSummary['expired']);
} elseif ($licenseSummary['missing'] > 0) {
    /* translators: %d: number of add-ons */
    $licenseNotice = sprintf(_n('%d add-on has no license yet.', '%d add-ons have no license yet.', $licenseSummary['missing'], 'wp-statistics'), $licenseSummary['missing']);
}
?>
<?php if ($licenseNotice) : ?>
    <div class="wps-license-banner" role="status">
        <span class="wps-license-banner__icon" aria-hidden="true">🔑</span>
        <span class="wps-license-banner__text">
            <strong><?php echo esc_html($licenseNotice); ?></strong>
            <?php esc_html_e('Add your license key to keep getting updates and support.', 'wp-statistics'); ?>
        </span>
        <a class="wps-license-banner__action" href="<?php echo esc_url(admin_url('admin.php?page=wps_plugins_page&tab=add-license')); ?>"><?php esc_html_e('Add license', 'wp-statistics'); ?></a>
    </div>
<?php endif; ?>
<div class="metabox-holder" id="overview-widgets">
    <div class="postbox-container" id="wps-postbox-container-1">
        <?php do_meta_boxes(get_current_screen(), 'side', ''); ?>
    </div>

    <div class="postbox-container" id="wps-postbox-container-2">
        <?php do_meta_boxes(get_current_screen(), 'normal', ''); ?>
    </div>
</div>

<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>
<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>

<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');
        postboxes.add_postbox_toggles('<?php echo esc_js(get_current_screen()->id); ?>');
    });
</script>