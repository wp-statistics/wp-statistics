<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title_text); ?>
            <?php if ($tooltip_text) : ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip_text); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
    </div>
</div>