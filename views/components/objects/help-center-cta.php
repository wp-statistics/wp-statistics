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
        <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . esc_attr($cta_link) . '/?utm_source=wp-statistics&utm_medium=link&utm_campaign=help'); ?>" target="_blank" class="button button-primary wps-help__cta-button">
            <?php echo esc_html($cta_title); ?>
        </a>
    <?php endif; ?>
</div>