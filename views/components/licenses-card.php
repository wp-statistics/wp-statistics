<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/** @var array $data */
if (empty($data['saved_licenses'])) {
    return;
}

$addLicenseUrl = admin_url('admin.php?page=wps_plugins_page&tab=add-license');
$renewUrl      = 'https://wp-statistics.com/my-account/subscriptions/?utm_source=wp-statistics&utm_medium=link&utm_campaign=licenses-card';
?>
<section class="wps-liccard" aria-labelledby="wps-liccard-title">
    <div class="wps-liccard__head">
        <h2 class="wps-liccard__title" id="wps-liccard-title"><?php esc_html_e('Your licenses', 'wp-statistics'); ?></h2>
        <a class="wps-liccard__add" href="<?php echo esc_url($addLicenseUrl); ?>"><?php esc_html_e('Add a new license key', 'wp-statistics'); ?></a>
    </div>
    <ul class="wps-liccard__list">
        <?php foreach ($data['saved_licenses'] as $license) :
            $expired = $license['status'] === 'license_expired';
            $name    = $license['sku'] === 'premium' ? __('Premium', 'wp-statistics') : __('Add-on key', 'wp-statistics');

            // One short line: when the key runs out. Amber within 30 days, red once it is over.
            $line = '';
            $tone = $expired ? 'expired' : 'ok';
            $time = !empty($license['valid_until']) ? strtotime($license['valid_until']) : false;
            if ($time !== false) {
                $days = (int) ceil(($time - time()) / DAY_IN_SECONDS);
                if ((int) gmdate('Y', $time) >= 2090) {
                    // The store writes 2099-01-01 for a key that never expires.
                    $line = __('Lifetime', 'wp-statistics');
                } elseif ($expired || $days < 0) {
                    $tone = 'expired';
                    /* translators: %s: date */
                    $line = sprintf(__('Expired %s', 'wp-statistics'), wp_date(get_option('date_format'), $time));
                } elseif ($days <= 30) {
                    $tone = 'soon';
                    /* translators: %d: number of days */
                    $line = $days === 0 ? __('Expires today', 'wp-statistics') : sprintf(_n('Expires in %d day', 'Expires in %d days', $days, 'wp-statistics'), $days);
                } else {
                    /* translators: %s: date */
                    $line = sprintf(__('Valid until %s', 'wp-statistics'), wp_date(get_option('date_format'), $time));
                }
            } elseif ($expired) {
                $line = __('Expired', 'wp-statistics');
            }

            // Small facts go in one faint bit of text next to the name, only when they say something.
            $facts = [];
            if ($license['covers'] > 0) {
                /* translators: %d: number of add-ons */
                $facts[] = sprintf(_n('%d add-on', '%d add-ons', $license['covers'], 'wp-statistics'), $license['covers']);
            }
            if (isset($license['max_domains']) && (int) $license['max_domains'] > 1) {
                /* translators: %d: number of sites */
                $facts[] = sprintf(__('%d sites', 'wp-statistics'), (int) $license['max_domains']);
            }
            ?>
            <li class="wps-liccard__row wps-liccard__row--<?php echo esc_attr($tone); ?>">
                <span class="wps-liccard__icon" aria-hidden="true"></span>
                <div class="wps-liccard__info">
                    <div class="wps-liccard__name">
                        <strong><?php echo esc_html($name); ?></strong>
                        <code><?php echo esc_html('…' . substr($license['masked'], -4)); ?></code>
                        <?php if ($facts) : ?>
                            <span class="wps-liccard__facts"><?php echo esc_html(implode(' · ', $facts)); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($line !== '') : ?>
                        <div class="wps-liccard__meta wps-liccard__meta--<?php echo esc_attr($tone); ?>"><?php echo esc_html($line); ?></div>
                    <?php endif; ?>
                </div>
                <?php if ($tone !== 'ok') : ?>
                    <a class="wps-liccard__renew" href="<?php echo esc_url($renewUrl); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Renew', 'wp-statistics'); ?></a>
                <?php endif; ?>
                <a class="wps-liccard__remove js-wps-license-remove" data-masked="<?php echo esc_attr($license['masked']); ?>" data-confirm="<?php esc_attr_e('Click again to remove', 'wp-statistics'); ?>" role="button" tabindex="0" title="<?php esc_attr_e('Remove license', 'wp-statistics'); ?>"><span class="wps-liccard__remove-label"><?php esc_html_e('Remove license', 'wp-statistics'); ?></span></a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
