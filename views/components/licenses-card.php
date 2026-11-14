<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/** @var array $data */
if (empty($data['saved_licenses'])) {
    return;
}

$addLicenseUrl = admin_url('admin.php?page=wps_plugins_page&tab=add-license');
?>
<section class="wps-liccard" aria-labelledby="wps-liccard-title">
    <div class="wps-liccard__head">
        <h2 class="wps-liccard__title" id="wps-liccard-title"><?php esc_html_e('Your licenses', 'wp-statistics'); ?></h2>
        <a class="wps-liccard__add" href="<?php echo esc_url($addLicenseUrl); ?>"><?php esc_html_e('Add another key', 'wp-statistics'); ?></a>
    </div>
    <ul class="wps-liccard__list">
        <?php foreach ($data['saved_licenses'] as $license) :
            $expired = $license['status'] === 'license_expired';
            $name    = $license['sku'] === 'premium' ? __('Premium', 'wp-statistics') : __('Add-on key', 'wp-statistics');
            ?>
            <li class="wps-liccard__row<?php echo $expired ? ' wps-liccard__row--expired' : ''; ?>">
                <span class="wps-liccard__icon" aria-hidden="true"></span>
                <div class="wps-liccard__info">
                    <div class="wps-liccard__name">
                        <strong><?php echo esc_html($name); ?></strong>
                        <code><?php echo esc_html('…' . substr($license['masked'], -4)); ?></code>
                        <span class="wps-liccard__status"><?php echo $expired ? esc_html__('Expired', 'wp-statistics') : esc_html__('Valid', 'wp-statistics'); ?></span>
                    </div>
                    <?php if ($license['covers'] > 0) : ?>
                        <div class="wps-liccard__meta">
                            <?php
                            /* translators: %d: number of add-ons */
                            echo esc_html(sprintf(_n('Unlocks %d add-on', 'Unlocks %d add-ons', $license['covers'], 'wp-statistics'), $license['covers']));
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
                <a class="wps-liccard__remove js-wps-license-remove" data-masked="<?php echo esc_attr($license['masked']); ?>" data-confirm="<?php esc_attr_e('Click again to remove', 'wp-statistics'); ?>" role="button" tabindex="0"><span><?php esc_html_e('Remove', 'wp-statistics'); ?></span></a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
