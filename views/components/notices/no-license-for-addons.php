<div class="wps-notice wps-notice--danger">
    <div>
        <p class="wps-notice__title"><?php esc_html_e('No License for Installed Add-ons', 'wp-statistics'); ?></p>
        <div class="wps-notice__description">
            <?php esc_html_e('You’ve installed the following add-ons, but we couldn’t find valid licenses for them:', 'wp-statistics') ?>
            <ul>
                <?php foreach ($data['unlicensed_installed_add_ons'] as $addOn): ?>
                    <li>
                        <a href="<?php echo esc_url($addOn->getProductUrl()); ?>?utm_source=wp-statistics&utm_medium=link&utm_campaign=install-addon" target="_blank">
                            <?php echo esc_html($addOn->getName()) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php
            echo wp_kses_post(sprintf(
                __('Without valid licenses, these add-ons won’t receive critical updates or new features. <br> Please add a valid license to ensure ongoing compatibility and support. <br> Have questions? <a href="%s" target="_blank">Contact Support</a>', 'wp-statistics'),
                esc_url("https://wp-statistics.com/contact-us/?utm_source=wp-statistics&utm_medium=link&utm_campaign=install-addon")
            ));
            ?>
        </div>
    </div>
</div>
