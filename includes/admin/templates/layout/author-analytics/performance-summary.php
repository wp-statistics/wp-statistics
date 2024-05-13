<div class="wps-card wps-card__icon wps-card__icon--<?php echo esc_attr($icon_class) ?>">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title_text); ?>
            <?php if (isset($tooltip_text)) : ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip_text); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif?>
        </h2>
    </div>

    <div class="wps-card__summary">
        <div class="wps-card__summary--title">
            <span><?php echo esc_html($total) ?></span>
            <span><?php esc_html_e('Total', 'wp-statistics') ?></span>
        </div>

        <?php if (isset($active)) : ?>
            <div class="wps-card__summary--active">
                <span><?php echo esc_html($active) ?></span>
                <span><?php esc_html_e('Active', 'wp-statistics') ?></span>
            </div>
        <?php endif ?>

        <div class="wps-card__summary--avg">
            <?php if (isset($avg)) : ?>
                <span><?php echo esc_html($avg) ?></span>
            <?php endif ?>

            <?php if (isset($avg_title)) : ?>
                <span><?php echo esc_html($avg_title) ?></span>
            <?php endif ?>
        </div>
     </div>
</div>