<div class="notice notice-<?php echo esc_attr($notice['class'] ?? 'default'); ?> wp-statistics-notice">
    <h2 class="notice-title"><?php echo esc_html($notice['message']['title'] ?? 'Default Title'); ?></h2>
    <p><?php echo esc_html($notice['message']['content']); ?>
        <a href="<?php echo esc_url($notice['message']['links']['learn_more']['url']); ?>" target="_blank">
            <?php echo esc_html($notice['message']['links']['learn_more']['text']); ?>
        </a>.
    </p>
    <div class="notice-footer">
        <a href="<?php echo esc_url($notice['message']['links']['enable_tracking']['url']); ?>" target="_blank" class="<?php echo esc_attr($notice['message']['links']['enable_tracking']['class']); ?>">
            <?php echo esc_html($notice['message']['links']['enable_tracking']['text']); ?>
        </a>
        <a href="<?php echo esc_url($notice['message']['links']['dismiss']['url']); ?>" class="<?php echo esc_attr($notice['message']['links']['dismiss']['class']); ?>">
            <?php echo esc_html($notice['message']['links']['dismiss']['text']); ?>
        </a>
    </div>
</div>
