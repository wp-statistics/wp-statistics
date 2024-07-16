<div class="wps-card wps-card__sums <?php echo isset($total) ? 'wps-card__sums--two-row' : ''; ?> ">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title) ?>
            <?php if(isset($tooltip)): ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif?>
        </h2>
    </div>
    <div class="wps-card__summary">
        <div class="wps-card__summary--title">
            <span><?php echo $selected ?></span>
            <span><?php echo isset($selected_title) ? esc_html($selected_title) : esc_html__('Total', 'wp-statistics') ?></span>
        </div>
        <div class="wps-card__summary--avg">
            <?php if(isset($avg)):  ?>
                <span><?php echo $avg ?></span>
            <?php endif?>
            <?php if(isset($avg_title)): ?>
                <span title="<?php echo esc_attr($avg_title) ?>"><?php echo $avg_title ?></span>
            <?php endif?>
        </div>

        <?php if (isset($total)) : ?>
            <div class="wps-card__summary--total">
                <span><?php echo esc_html($total) ?></span>
                <span><?php esc_html_e('Total', 'wp-statistics') ?></span>
            </div>
        <?php endif ?>

        <?php if (isset($total_avg)) : ?>
            <div class="wps-card__summary--total-avg">
                <span><?php echo esc_html($total_avg) ?></span>
                <span title="<?php echo isset($total_avg_title) ? esc_attr($total_avg_title) : esc_attr_e('Total Avg. per Content', 'wp-statistics') ?>"><?php echo isset($total_avg_title) ? esc_html($total_avg_title) : esc_html_e('Total Avg. per Content', 'wp-statistics') ?></span>
            </div>
        <?php endif ?>


    </div>
</div>