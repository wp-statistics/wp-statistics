<div class="notice notice-<?php echo esc_attr($notice['class'] ?? 'default'); ?> wp-statistics-notice">
    <h2 class="notice-title">
        <?php echo esc_html($notice['message']['title'] ?? 'Default Title'); ?>
        <?php if ($notice['is_dismissible']) : ?>
            <?php if ($dismissUrl) : ?>
                <a href="<?php echo esc_url($dismissUrl); ?>" class="notice--dismiss"></a>
            <?php endif; ?>
        <?php endif; ?>
    </h2>
    <p><?php echo wp_kses_post($notice['message']['content']); ?>
        <a href="<?php echo esc_url($notice['message']['links']['learn_more']['url']); ?>" target="_blank"><?php echo esc_html($notice['message']['links']['learn_more']['text']); ?></a>.
    </p>
    <div class="notice-footer">
        <a href="<?php echo esc_url($notice['message']['links']['primary_button']['url']); ?>" data-option="<?php echo esc_attr($notice['message']['links']['primary_button']['attributes']['data-option']); ?>" data-value="<?php echo esc_attr($notice['message']['links']['primary_button']['attributes']['data-value']); ?>" target="_blank"
           class="<?php echo esc_attr($notice['message']['links']['primary_button']['class']); ?>">
            <?php echo esc_html($notice['message']['links']['primary_button']['text']); ?>
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
