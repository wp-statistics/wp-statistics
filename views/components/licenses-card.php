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
        <a class="wps-liccard__add" href="<?php echo esc_url($addLicenseUrl); ?>"><?php esc_html_e('Add another key', 'wp-statistics'); ?></a>
    </div>
    <ul class="wps-liccard__list">
        <?php foreach ($data['saved_licenses'] as $license) :
            $expired = $license['status'] === 'license_expired';
            $name    = $license['sku'] === 'premium' ? __('Premium', 'wp-statistics') : __('Add-on key', 'wp-statistics');

            // When the key runs out: plain text, amber within 30 days, red once it is over.
            $expiryText = '';
            $expiryTone = 'ok';
            $time       = !empty($license['valid_until']) ? strtotime($license['valid_until']) : false;
            if ($time !== false) {
                $days = (int) ceil(($time - time()) / DAY_IN_SECONDS);
                if ($expired || $days < 0) {
                    $expiryTone = 'expired';
                    /* translators: %s: date */
                    $expiryText = sprintf(__('Expired %s', 'wp-statistics'), wp_date(get_option('date_format'), $time));
                } elseif ($days <= 30) {
                    $expiryTone = 'soon';
                    /* translators: %d: number of days */
                    $expiryText = $days === 0 ? __('Expires today', 'wp-statistics') : sprintf(_n('Expires in %d day', 'Expires in %d days', $days, 'wp-statistics'), $days);
                } else {
                    /* translators: %s: date */
                    $expiryText = sprintf(__('Expires %s', 'wp-statistics'), wp_date(get_option('date_format'), $time));
                }
            }

            // Sites the key allows.
            $sitesText = '';
            if (isset($license['max_domains']) && $license['max_domains'] !== '') {
                $sites     = (int) $license['max_domains'];
                /* translators: %d: number of sites */
                $sitesText = $sites > 0 ? sprintf(_n('%d site', '%d sites', $sites, 'wp-statistics'), $sites) : __('Unlimited sites', 'wp-statistics');
            }
            ?>
            <li class="wps-liccard__row<?php echo $expired ? ' wps-liccard__row--expired' : ''; ?>">
                <span class="wps-liccard__icon" aria-hidden="true"></span>
                <div class="wps-liccard__info">
                    <div class="wps-liccard__name">
                        <strong><?php echo esc_html($name); ?></strong>
                        <code><?php echo esc_html('…' . substr($license['masked'], -4)); ?></code>
                        <span class="wps-liccard__status"><?php echo $expired ? esc_html__('Expired', 'wp-statistics') : esc_html__('Valid', 'wp-statistics'); ?></span>
                    </div>
                    <div class="wps-liccard__meta">
                        <?php
                        $parts = [];
                        if ($license['covers'] > 0) {
                            /* translators: %d: number of add-ons */
                            $parts[] = '<span>' . esc_html(sprintf(_n('Unlocks %d add-on', 'Unlocks %d add-ons', $license['covers'], 'wp-statistics'), $license['covers'])) . '</span>';
                        }
                        if ($sitesText !== '') {
                            $parts[] = '<span>' . esc_html($sitesText) . '</span>';
                        }
                        if ($expiryText !== '') {
                            $parts[] = '<span class="wps-liccard__expiry wps-liccard__expiry--' . esc_attr($expiryTone) . '">' . esc_html($expiryText) . '</span>';
                        }
                        echo implode('<span aria-hidden="true">·</span>', $parts); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- every part is escaped above
                        ?>
                        <?php if ($expiryTone !== 'ok') : ?>
                            <a class="wps-liccard__renew" href="<?php echo esc_url($renewUrl); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Renew', 'wp-statistics'); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
                <a class="wps-liccard__remove js-wps-license-remove" data-masked="<?php echo esc_attr($license['masked']); ?>" data-confirm="<?php esc_attr_e('Click again to remove', 'wp-statistics'); ?>" role="button" tabindex="0"><span><?php esc_html_e('Remove', 'wp-statistics'); ?></span></a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
