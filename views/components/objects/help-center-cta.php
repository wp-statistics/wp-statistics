<div class="wps-help__cta">
    <div>
        <h2 class="wps-help__cta-title">
            <?php echo esc_html($title) ?>
        </h2>
        <p class="wps-help__cta-description">
            <?php echo esc_html($description); ?>
        </p>
    </div>
    <?php if (isset($cta_link)): ?>
        <a href="<?php echo esc_url($cta_link); ?>" target="_blank" class="button button-primary wps-help__cta-button">
            <?php echo esc_html($cta_title); ?>
        </a>
    <?php endif; ?>
</div>