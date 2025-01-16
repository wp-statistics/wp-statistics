<div class="wps-notification-sidebar__card">
    <div class="wps-notification-sidebar__card-icon">
        <img src="<?php echo esc_url($icon) ?>">
    </div>
    <div class="wps-notification-sidebar__card-body">
        <div class="wps-notification-sidebar__card-title">
            <div><?php echo esc_html($title) ?></div>
            <div class="wps-notification-sidebar__card-date"><?php echo esc_html($date) ?></div>
        </div>
        <div class="wps-notification-sidebar__card-content">
            <?php echo $content ?>
        </div>
        <div class="wps-notification-sidebar__card-actions">
            <?php if (!empty($actions)) : ?>
                <?php foreach ($actions as $action) : ?>
                    <a href="<?php echo esc_url($action['href']); ?>" class="<?php echo esc_attr($action['class']); ?>">
                        <?php echo esc_html($action['title']); ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
            <a href="" class="wps-notification-sidebar__dismiss">
                <?php echo esc_html_e('Dismiss', 'wp-statistics'); ?>
            </a>
        </div>
    </div>
</div>