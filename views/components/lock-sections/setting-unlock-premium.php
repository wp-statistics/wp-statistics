<div class="wps-premium-feature__head">
    <h1>
        <?php esc_html_e('Unlock Premium Features with', 'wp-statistics')?>
        <span><?php echo esc_html($addon_title); ?></span>
    </h1>
    <?php if (!empty($addon_description)): ?>
        <p><?php echo esc_html($addon_description); ?></p>
    <?php endif; ?>
</div>
<?php if (!empty($addon_features)): ?>
    <div class="wps-premium-feature__items <?php echo esc_html($addon_title); ?>">
        <?php foreach ($addon_features as $feature): ?>
            <div class="wps-premium-feature__item"><?php echo esc_html($feature); ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($addon_info)): ?>
    <div class="wps-premium-feature__addon_info">
        <?php if (isset($addon_info)) {
            echo esc_html($addon_info);
            }
        ?>
        <?php if (!empty($addon_documentation_title) && !empty($addon_documentation_slug)): ?>
            <a href="<?php echo esc_url($addon_documentation_slug) ?>" target="_blank" title="<?php echo esc_html($addon_documentation_title); ?>"><?php echo esc_html($addon_documentation_title); ?></a>.
        <?php endif; ?>
    </div>
<?php endif; ?>
<div class="wps-premium-feature__info">
    <?php echo esc_html_e('To unlock every premium feature in WP Statistics, upgrade to Premium.', 'wp-statistics'); ?>
</div>
<div class="wps-premium-feature__buttons">
    <a class="button button-primary" target="_blank" href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/pricing/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings') ?>"><?php esc_html_e('Unlock Everything with Premium', 'wp-statistics') ?></a>
    <a class="wps-show-premium-modal button  js-wps-openPremiumModal" data-target="<?php echo esc_html($addon_modal_target) ?>" data-name="<?php echo esc_html($addon_title) ?>"><?php esc_html_e('Learn More', 'wp-statistics') ?></a>
</div>
