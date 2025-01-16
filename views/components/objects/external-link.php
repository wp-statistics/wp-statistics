<a class="wps-link-arrow wps-link-arrow--external" title="<?php echo esc_html($title) ?>" target="_blank" href="<?php echo esc_url($url); ?>">
    <span><?php echo esc_html($title) ?></span>
    <?php if (isset($tooltip)): ?>
        <span class="wps-tooltip" title="<?php echo esc_attr($tooltip) ?>"><i class="wps-tooltip-icon info"></i></span>
    <?php endif; ?>
</a>
