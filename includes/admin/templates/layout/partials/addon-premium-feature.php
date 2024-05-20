<div class="wps-premium-feature">
    <div class="wps-premium-feature__head">
        <h1>
            <?php esc_html_e('This Feature is Part of the', 'wp-statistics'); ?> <span><?php esc_html_e($addon_title, 'wp-statistics'); ?></span>
        </h1>
        <?php if (!empty($addon_description)): ?>
            <p><?php esc_html_e($addon_description, 'wp-statistics'); ?></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($addon_features)): ?>
        <div class="wps-premium-feature__items <?php  echo $addon_title ; ?>">
            <?php foreach ($addon_features as $feature): ?>
                <div class="wps-premium-feature__item"><?php esc_html_e($feature, 'wp-statistics'); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($addon_info)): ?>
        <div class="wps-premium-feature__info">
            <?php esc_html_e($addon_info, 'wp-statistics'); ?>
            <?php if (!empty($addon_documentation_title) && !empty($addon_documentation_slug)): ?>
                <a href="<?php echo $addon_documentation_slug ?>" target="_blank" title="<?php esc_html_e($addon_documentation_title, 'wp-statistics'); ?>"><?php esc_html_e($addon_documentation_title, 'wp-statistics'); ?></a>.
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <a target="_blank" class="button button-primary" href="<?php echo $addon_slug ?>"><?php esc_html_e('Upgrade Now', 'wp-statistics') ?></a>
</div>