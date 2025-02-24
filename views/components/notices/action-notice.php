<div class="notice notice-<?php echo esc_attr($notice['class'] ?? 'default'); ?> wp-statistics-notice">
    <h2 class="notice-title"><?php echo esc_html($notice['message']['title'] ?? 'Default Title'); ?></h2>
    <p><?php echo wp_kses_post($notice['message']['content']); ?>
        <a href="<?php echo esc_url($notice['message']['links']['learn_more']['url']); ?>" target="_blank">
            <?php echo esc_html($notice['message']['links']['learn_more']['text']); ?>
        </a>.
    </p>
    <div class="notice-footer">
        <a href="<?php echo esc_url($notice['message']['links']['enable_tracking']['url']); ?>" target="_blank"
           class="<?php echo esc_attr($notice['message']['links']['enable_tracking']['class']); ?>">
            <?php echo esc_html($notice['message']['links']['enable_tracking']['text']); ?>
        </a>
        <?php if ($notice['is_dismissible']) : ?>
            <?php if ($dismissUrl) : ?>
                <a href="<?php echo esc_url($dismissUrl); ?>" class="notice--dismiss">
                    <?php echo esc_html_e('Dismiss', 'wp-statistics'); ?>
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
