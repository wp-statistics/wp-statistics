<div class="notice notice-<?php echo esc_attr($notice['class']); ?> wp-statistics-notice<?php echo esc_attr($dismissible); ?>">
    <p><?php echo wp_kses_post($notice['message']); ?></p>
    <?php if ($notice['is_dismissible']) : ?>
        <?php if ($dismissUrl) : ?><a href="<?php echo esc_url($dismissUrl); ?>" class="notice-dismiss"><?php endif; ?>
        <span class="screen-reader-text"><?php _e('Dismiss this notice.'); ?></span>
        <?php if ($dismissUrl) : ?></a><?php endif; ?>
    <?php endif; ?>
</div>