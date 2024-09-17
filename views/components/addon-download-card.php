<div class="wps-addon__download__item <?php echo $disable ? 'wps-addon__download__item--disabled' : '' ?>">
    <div class="wps-addon__download__item--info">
        <div class="wps-addon__download__item--info__img">
            <img src="<?php echo esc_url($icon) ?>" alt="<?php echo $title ?>">
        </div>
        <div class="wps-addon__download__item--info__text">
            <div class="wps-addon__download__item--info__title">
                <?php echo $title ?>
                <?php if (isset($read_more_link)) : ?>
                    <a target="_blank" href="<?php echo $read_more_link ?>" class="wps-postbox-addon__read-more" title="<?php echo esc_html__('Learn More', 'wp-statistics') ?>">
                        <?php echo esc_html__('Learn More', 'wp-statistics') ?>
                    </a>
                <?php endif; ?>
                <?php if (isset($label_text)) : ?>
                    <span class="wps-postbox-addon__label wps-postbox-addon__label--<?php echo $label_class ?>"><?php echo $label_text ?></span>
                <?php endif; ?>
            </div>
            <p class="wps-addon__download__item--info__desc">
                <?php echo $description ?>
            </p>
        </div>
    </div>
    <div class="wps-addon__download__item--select">
        <?php if (isset($status_text)) : ?>
            <span class="wps-postbox-addon__status wps-postbox-addon__status--<?php echo $status_class ?> "><?php echo $status_text ?></span>
        <?php endif; ?>
        <?php if (!$disable) : ?>
        <input type="checkbox" class="js-wps-addon-check-box" name="addon-select">
        <?php endif; ?>
    </div>
</div>