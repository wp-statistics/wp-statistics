<div class="wps-addon__download__item">
    <div class="wps-addon__download__item--info">
        <div class="wps-addon__download__item--info__img">
            <img src="<?php echo esc_url($icon) ?>" alt="<?php echo $title ?>">
        </div>
        <div class="wps-addon__download__item--info__text">
            <div class="wps-addon__download__item--info__title">
                <?php echo $title ?>
            </div>
            <p class="wps-addon__download__item--info__desc">
                <?php echo $description ?>
            </p>
        </div>
    </div>
    <div class="wps-addon__download__item--actions">
        <?php if (isset($status_text)) : ?>
            <span class="wps-postbox-addon__status wps-postbox-addon__status--<?php echo $status_class ?> "><?php echo $status_text ?></span>
        <?php endif; ?>

        <?php if (isset($active_link)) : ?>
            <a class="wps-postbox-addon__button" href="<?php echo esc_url($active_link) ?>" title="<?php echo esc_html__('Active', 'wp-statistics') ?>"><?php echo esc_html__('Active', 'wp-statistics') ?></a>
        <?php endif; ?>

        <?php if (isset($retry_link)) : ?>
            <a class="wps-postbox-addon__button" href="<?php echo esc_url($retry_link) ?>" title="<?php echo esc_html__('Retry', 'wp-statistics') ?>"><?php echo esc_html__('Retry', 'wp-statistics') ?></a>
        <?php endif; ?>

        <?php if (isset($setting_link) ||  isset($detail_link) || isset($change_log_link) || isset($documentation_link)) : ?>
            <div class="wps-addon--actions">
                <span class="wps-addon--actions--show-more js-addon-show-more"></span>
                <ul class="wps-addon--submenus">
                    <?php if (isset($setting_link)) : ?>
                        <li><a href="<?php echo esc_url($setting_link) ?>" class="wps-addon--submenu wps-addon--submenu__settings"><?php esc_html_e('Settings', 'wp-statistics'); ?></a></li>
                    <?php endif; ?>
                    <?php if (isset($detail_link)) : ?>
                        <li><a href="<?php echo esc_url($detail_link) ?>" class="wps-addon--submenu" target="_blank"><?php esc_html_e('Add-On Detail', 'wp-statistics'); ?></a></li>
                    <?php endif; ?>
                    <?php if (isset($change_log_link)) : ?>
                        <li><a href="<?php echo esc_url($change_log_link) ?>" class="wps-addon--submenu" target="_blank"><?php esc_html_e('Changelog', 'wp-statistics'); ?></a></li>
                    <?php endif; ?>
                    <?php if (isset($documentation_link)) : ?>
                        <li><a href="<?php echo esc_url($documentation_link) ?>" class="wps-addon--submenu" target="_blank"><?php esc_html_e('Documentation', 'wp-statistics'); ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>