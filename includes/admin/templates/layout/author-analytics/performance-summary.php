<div class="wps-card wps-card__icon wps-card__icon--<?php echo $icon_class ?>">
    <div class="wps-card__title">
        <h2>
            <?php echo $title_text ?>
            <?php if($tooltip_text):?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip_text); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif?>
        </h2>
    </div>
    <div class="wps-card__summary">
        <div class="wps-card__summary--title">
            <span><?php echo $total ?></span>
            <span><?php echo esc_html__('Total', 'wp-statistics') ?></span>
        </div>
        <?php if($active):?>
            <div class="wps-card__summary--active">
                <span><?php echo $active ?></span>
                <span><?php echo esc_html__('Active', 'wp-statistics') ?></span>
            </div>
        <?php endif?>
        <div class="wps-card__summary--avg">
            <span><?php echo $avg ?></span>
            <span><?php echo $avg_title ?></span>
        </div>
     </div>
</div>