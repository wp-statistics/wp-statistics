<div class="wps-notice wps-notice--warning">
    <div>
        <p class="wps-notice__title"><?php esc_html_e('Some Add-ons Are Missing', 'wp-statistics'); ?></p>
        <div class="wps-notice__description">
            <?php esc_html_e('You have a valid WP Statistics license, but you haven’t installed the following add-ons yet:', 'wp-statistics') ?>
            <ul>
                <?php foreach ($data['missing_add_ons'] as $addOn): ?>
                    <li>
                        <a href="<?php echo esc_url($addOn->getProductUrl()); ?>?utm_source=wp-statistics&utm_medium=link&utm_campaign=<?php echo rawurlencode($addOn->getUtmCampaign()); ?>" target="_blank">
                            <?php echo esc_html($addOn->getName()) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php esc_html_e('Install them now to take full advantage of your WP Statistics.', 'wp-statistics') ?>
        </div>
    </div>
</div>
