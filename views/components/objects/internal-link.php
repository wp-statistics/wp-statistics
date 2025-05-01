<a class="wps-ellipsis-parent wps-internal-link" href="<?php echo esc_url($url); ?>">
    <span title="<?php echo esc_html($title) ?>" class="wps-ellipsis-text"><?php echo esc_html($title) ?></span>
    <?php if (!empty($tooltip)): ?>
        <span class="wps-tooltip" title="<?php echo esc_attr($tooltip) ?>"><i class="wps-tooltip-icon info"></i></span>
    <?php endif; ?>
</a>