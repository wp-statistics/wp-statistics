<div class="wps-premium-feature">
    <div class="wps-premium-feature__head">
        <h1>
            <?php esc_html_e('This Feature is Part of the', 'wp-statistics'); ?> <span><?php echo esc_html($addon_title); ?></span>
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
        <div class="wps-premium-feature__info">
            <?php echo esc_html($addon_info); ?>
            <?php if (!empty($addon_documentation_title) && !empty($addon_documentation_slug)): ?>
                <a href="<?php echo esc_url($addon_documentation_slug) ?>" target="_blank" title="<?php echo esc_html($addon_documentation_title); ?>"><?php echo esc_html($addon_documentation_title); ?></a>.
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <a target="_blank" class="button button-primary" href="<?php echo esc_url($addon_slug) ?>"><?php esc_html_e('Upgrade Now', 'wp-statistics') ?></a>
</div>